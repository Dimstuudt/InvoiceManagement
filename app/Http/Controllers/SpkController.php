<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Spk;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Smalot\PdfParser\Parser as PdfParser;

class SpkController extends Controller
{

    public function index()
    {
        $clients = Client::with('category:id,name')
            ->withCount('spks')
            ->withSum('spks', 'contract_value')
            ->orderBy('company_name')
            ->get(['id', 'client_category_id', 'company_name', 'city', 'client_status', 'is_active', 'created_at']);

        return Inertia::render('Spk/Index', [
            'clients' => $clients,
        ]);
    }

    public function clientSpks(Client $client)
    {
        $client->load([
            'spks' => fn($q) => $q->with([
                'projectCategory:id,name,code',
                'user:id,name',
                'invoices' => fn($q) => $q->select('id', 'spk_id', 'parent_invoice_id', 'invoice_number', 'issue_date', 'payment_status', 'document_status')
                                          ->orderByDesc('issue_date'),
            ])->orderByDesc('created_at'),
        ]);

        return Inertia::render('Spk/Client', [
            'client'            => $client,
            'projectCategories' => \App\Models\ProjectCategory::all(['id', 'name', 'code']),
            'clientCategories'  => \App\Models\ClientCategory::all(['id', 'name']),
            'documentIssuers'   => \App\Models\DocumentIssuer::all(['id', 'name']),
            'bankAccounts'      => \App\Models\BankAccount::all(['id', 'name', 'bank_name', 'account_number']),
        ]);
    }

    public function numberPreview(Request $request)
    {
        $request->validate([
            'project_category_id' => 'required|exists:project_categories,id',
            'date'                => 'required|date',
        ]);

        $category = \App\Models\ProjectCategory::findOrFail($request->project_category_id);
        $date     = Carbon::parse($request->date);
        $number   = Spk::generateNumber($category->code, $date);
        $seq      = (int) explode('/', $number)[0];

        return response()->json(['number' => $number, 'seq' => $seq]);
    }

    public function parse(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:10240',
        ]);

        $apiKey = config('services.groq.key');
        if (!$apiKey) {
            return back()->with('error', 'GROQ_API_KEY belum dikonfigurasi.');
        }

        // Ekstrak teks dari PDF
        try {
            $parser  = new PdfParser();
            $pdf     = $parser->parseFile($request->file('file')->getRealPath());
            $pdfText = $pdf->getText();
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca PDF: ' . $e->getMessage());
        }

        if (blank($pdfText)) {
            return back()->with('error', 'PDF tidak mengandung teks yang bisa dibaca (mungkin hasil scan/gambar).');
        }

        // Ambil referensi dari database untuk dicocokkan AI
        $categories = \App\Models\ProjectCategory::all(['id', 'name', 'code']);
        $issuers    = \App\Models\DocumentIssuer::all(['id', 'name']);
        $banks      = \App\Models\BankAccount::all(['id', 'name', 'bank_name']);

        $categoriesRef = $categories->map(fn($c) => "  ID {$c->id}: {$c->name} (kode: {$c->code})")->join("\n");
        $issuersRef    = $issuers->map(fn($d)    => "  ID {$d->id}: {$d->name}")->join("\n");
        $banksRef      = $banks->map(fn($b)      => "  ID {$b->id}: {$b->bank_name} – {$b->name}")->join("\n");

        $prompt = <<<PROMPT
Kamu adalah asisten yang mengekstrak data dari dokumen SPK (Surat Perjanjian Kerja) Indonesia dan mencocokkannya dengan data referensi sistem.

Kembalikan HANYA JSON valid berikut, tanpa penjelasan, tanpa markdown:

{
  "spk_number": "nomor SPK lengkap atau null",
  "service_name": "nama layanan/jasa dari judul/objek pekerjaan atau null",
  "contract_value": nilai kontrak rupiah sebagai integer tanpa titik/koma atau null,
  "start_date": "YYYY-MM-DD atau null",
  "end_date": "YYYY-MM-DD atau null",
  "duration_months": durasi dalam bulan sebagai integer atau null,
  "pic_name": "nama PIC/contact person pihak klien atau null",
  "notes": "catatan syarat pembayaran, SLA, atau ketentuan khusus atau null",
  "project_category_id": ID kategori yang paling cocok dengan layanan atau null,
  "document_issuer_id": ID penerbit yang paling relevan atau null,
  "bank_account_id": ID rekening yang paling sesuai atau null,
  "invoice_type": "yearly" jika kontrak tahunan, "monthly" jika bulanan, atau null,
  "interval_months": interval tagihan dalam bulan (12 jika tahunan, 1-12 jika bulanan) atau null
}

Aturan:
- contract_value: integer murni, contoh: 36000000
- Hitung duration_months dari selisih tanggal jika tidak tertulis
- Untuk project_category_id, cocokkan nama/kode layanan di SPK dengan daftar berikut:
{$categoriesRef}
- Untuk document_issuer_id, pilih yang paling relevan dari:
{$issuersRef}
- Untuk bank_account_id, pilih yang paling relevan dari:
{$banksRef}
- Untuk invoice_type: "yearly" jika layanan tahunan/hosting/lisensi, "monthly" jika maintenance/bulanan
- Gunakan null untuk field yang tidak bisa dipastikan

Teks dokumen SPK:
PROMPT;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])->timeout(60)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model'           => 'llama-3.3-70b-versatile',
                'temperature'     => 0,
                'response_format' => ['type' => 'json_object'],
                'messages'        => [
                    [
                        'role'    => 'user',
                        'content' => $prompt . "\n\n" . mb_substr($pdfText, 0, 12000),
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            return back()->with('error', 'Koneksi ke Groq gagal: ' . $e->getMessage());
        }

        if ($response->failed()) {
            $msg = $response->json('error.message', 'Unknown error');
            return back()->with('error', "Groq API error: {$msg}");
        }

        $raw  = $response->json('choices.0.message.content', '');
        $raw  = preg_replace('/^```(?:json)?\s*/m', '', $raw);
        $raw  = preg_replace('/\s*```$/m', '', $raw);
        $data = json_decode(trim($raw), true);

        if (!\is_array($data)) {
            return back()->with('error', 'Claude tidak mengembalikan data yang valid. Coba lagi.');
        }

        // Sanitasi & validasi ID referensi agar tidak bisa inject ID sembarangan
        $validCategoryIds = $categories->pluck('id')->all();
        $validIssuerIds   = $issuers->pluck('id')->all();
        $validBankIds     = $banks->pluck('id')->all();

        if (isset($data['contract_value'])) {
            $data['contract_value'] = (int) preg_replace('/\D/', '', (string) $data['contract_value']) ?: null;
        }
        if (isset($data['duration_months'])) {
            $data['duration_months'] = (int) $data['duration_months'] ?: null;
        }
        if (isset($data['interval_months'])) {
            $data['interval_months'] = (int) $data['interval_months'] ?: null;
        }
        if (!\in_array($data['project_category_id'] ?? null, $validCategoryIds)) {
            $data['project_category_id'] = null;
        }
        if (!\in_array($data['document_issuer_id'] ?? null, $validIssuerIds)) {
            $data['document_issuer_id'] = null;
        }
        if (!\in_array($data['bank_account_id'] ?? null, $validBankIds)) {
            $data['bank_account_id'] = null;
        }
        if (!\in_array($data['invoice_type'] ?? null, ['monthly', 'yearly'])) {
            $data['invoice_type'] = null;
        }

        return back()->with('spk', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id'          => 'required|exists:clients,id',
            'service_name'       => 'required|string|max:255',
            'contract_value'     => 'nullable|numeric|min:0',
            'spk_number'         => 'nullable|string|max:100',
            'start_date'         => 'nullable|date',
            'end_date'           => 'nullable|date',
            'duration_months'    => 'nullable|integer|min:1',
            'notes'              => 'nullable|string',
            'file'               => 'nullable|file|mimes:pdf|max:10240',
            'issue_date'         => 'required|date',
            'due_date'           => 'required|date|after_or_equal:issue_date',
            'invoice_type'       => 'required|in:monthly,yearly',
            'interval_months'    => 'nullable|integer|min:1|max:36',
            'project_category_id'=> 'required|exists:project_categories,id',
            'document_issuer_id' => 'required|exists:document_issuers,id',
            'bank_account_id'    => 'nullable|exists:bank_accounts,id',
        ]);

        $invoiceId = null;

        DB::transaction(function () use ($request, &$invoiceId) {
            $client   = Client::findOrFail($request->client_id);
            $filePath = $request->hasFile('file')
                ? $request->file('file')->store('spk', 'public')
                : null;

            // Ambil kategori dulu (dipakai untuk generate SPK & invoice number)
            $category  = \App\Models\ProjectCategory::findOrFail($request->project_category_id);
            $issueDate = Carbon::parse($request->issue_date);

            // Generate nomor SPK jika tidak diisi manual
            $spkDate   = $request->start_date ? Carbon::parse($request->start_date) : $issueDate;
            $spkNumber = $request->spk_number ?: Spk::generateNumber($category->code, $spkDate);

            // Buat record SPK
            $spk = Spk::create([
                'client_id'          => $client->id,
                'user_id'            => Auth::id(),
                'project_category_id'=> $request->project_category_id,
                'spk_number'         => $spkNumber,
                'service_name'       => $request->service_name,
                'contract_value'     => $request->contract_value,
                'pic_name'           => $client->pic,
                'start_date'         => $request->start_date,
                'end_date'           => $request->end_date,
                'duration_months'    => $request->duration_months,
                'file_path'          => $filePath,
                'notes'              => $request->notes,
            ]);

            // Generate nomor invoice
            $invoiceNumber = Invoice::generateNumber($category->code, $issueDate, $request->invoice_type);

            // Buat invoice — attention diambil dari PIC client
            $invoice = Invoice::create([
                'user_id'             => Auth::id(),
                'client_id'           => $client->id,
                'spk_id'              => $spk->id,
                'project_category_id' => $request->project_category_id,
                'document_issuer_id'  => $request->document_issuer_id,
                'bank_account_id'     => $request->bank_account_id,
                'invoice_number'      => $invoiceNumber,
                'spk_number'          => $spkNumber,
                'attention'           => $client->pic,
                'notes'               => $request->notes,
                'issue_date'          => $issueDate->toDateString(),
                'due_date'            => Carbon::parse($request->due_date)->toDateString(),
                'interval_months'     => $request->interval_months,
                'invoice_type'        => $request->invoice_type,
                'payment_status'      => 'unpaid',
                'document_status'     => 'draft',
                'send_status'         => 'unsent',
                'is_demo'             => false,
            ]);

            if ($request->contract_value) {
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'description' => $request->service_name ?? 'Jasa sesuai SPK',
                    'amount'      => $request->contract_value,
                    'discount'    => 0,
                ]);
            }

            $invoiceId = $invoice->id;
        });

        return redirect()->route('invoices.show', $invoiceId)
            ->with('success', 'SPK dan invoice berhasil dibuat.');
    }

    public function destroy(Spk $spk)
    {
        DB::transaction(function () use ($spk) {
            // Hapus semua invoice yang terkait (beserta item-nya via cascade FK)
            $spk->invoices()->delete();

            if ($spk->file_path) {
                Storage::disk('public')->delete($spk->file_path);
            }

            $spk->delete();
        });

        return back()->with('success', 'SPK dan invoice terkait berhasil dihapus.');
    }
}
