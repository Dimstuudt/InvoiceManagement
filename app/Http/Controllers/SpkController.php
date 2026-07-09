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

    public function create()
    {
        return Inertia::render('Spk/Create', [
            'provider'          => 'groq',
            'projectCategories' => \App\Models\ProjectCategory::all(['id', 'name', 'code']),
            'clientCategories'  => \App\Models\ClientCategory::all(['id', 'name']),
            'documentIssuers'   => \App\Models\DocumentIssuer::all(['id', 'name']),
            'bankAccounts'      => \App\Models\BankAccount::all(['id', 'name', 'bank_name', 'account_number']),
        ]);
    }

    public function createLocal()
    {
        return Inertia::render('Spk/CreateLocal', [
            'projectCategories' => \App\Models\ProjectCategory::all(['id', 'name', 'code']),
            'clientCategories'  => \App\Models\ClientCategory::all(['id', 'name']),
            'documentIssuers'   => \App\Models\DocumentIssuer::all(['id', 'name']),
            'bankAccounts'      => \App\Models\BankAccount::all(['id', 'name', 'bank_name', 'account_number']),
        ]);
    }

    public function createOllama()
    {
        return Inertia::render('Spk/Create', [
            'provider'          => 'ollama',
            'projectCategories' => \App\Models\ProjectCategory::all(['id', 'name', 'code']),
            'clientCategories'  => \App\Models\ClientCategory::all(['id', 'name']),
            'documentIssuers'   => \App\Models\DocumentIssuer::all(['id', 'name']),
            'bankAccounts'      => \App\Models\BankAccount::all(['id', 'name', 'bank_name', 'account_number']),
        ]);
    }

    public function parseLocal(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:pdf|max:10240']);

        try {
            $parser  = new PdfParser();
            $pdf     = $parser->parseFile($request->file('file')->getRealPath());
            $text    = $pdf->getText();
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca PDF: ' . $e->getMessage());
        }

        if (blank($text)) {
            return back()->with('error', 'PDF tidak mengandung teks yang bisa dibaca (mungkin hasil scan/gambar).');
        }

        $detected = [];
        $missed   = [];
        $data     = [];

        // Helper: tandai detected/missed
        $mark = function ($field, $value) use (&$detected, &$missed, &$data) {
            if ($value !== null && $value !== '') {
                $data[$field]  = $value;
                $detected[]    = $field;
            } else {
                $data[$field]  = null;
                $missed[]      = $field;
            }
        };

        // Helper: parse tanggal Indonesia ke YYYY-MM-DD
        $parseIdDate = function (string $raw): ?string {
            $bulan = [
                'januari'=>1,'februari'=>2,'maret'=>3,'april'=>4,'mei'=>5,'juni'=>6,
                'juli'=>7,'agustus'=>8,'september'=>9,'oktober'=>10,'november'=>11,'desember'=>12,
            ];
            if (preg_match('/(\d{1,2})\s+(\w+)\s+(\d{4})/i', $raw, $m)) {
                $mon = $bulan[strtolower($m[2])] ?? null;
                if ($mon) return sprintf('%04d-%02d-%02d', $m[3], $mon, $m[1]);
            }
            // fallback: dd/mm/yyyy atau dd-mm-yyyy
            if (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/', $raw, $m)) {
                return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
            }
            return null;
        };

        $textLower = strtolower($text);

        // Helper: bersihkan string hasil regex
        $clean = fn($s) => trim(preg_replace('/\s+/', ' ', $s));

        // Helper: parse nilai Rp ke integer
        $parseRp = function (string $raw): ?int {
            // "30.000.000,-" atau "30,000,000" → 30000000
            $s = preg_replace('/[.,]\s*$/', '', $raw);  // hapus trailing ,- atau .
            // Jika ada koma sebagai desimal (misal "30,000,000"), ambil sebelum koma terakhir jika > 3 digit setelahnya
            // Strategi: hapus semua titik dan koma jika hasilnya masih angka
            $stripped = preg_replace('/[.,]/', '', $s);
            if (is_numeric($stripped) && strlen($stripped) >= 4) return (int) $stripped;
            // fallback: hapus non-digit
            $digits = preg_replace('/\D/', '', $s);
            return $digits ? (int) $digits : null;
        };

        // 1. Nomor SPK
        preg_match('/Nomor\s*:\s*([^\n\r]+)/i', $text, $m);
        $mark('spk_number', isset($m[1]) ? $clean($m[1]) : null);

        // 2. Tanggal — coba berbagai format:
        //    a. "(dd-mm-yyyy)" — format Koperasi CESA
        //    b. "dd Bulan YYYY" — format formal
        //    c. "terhitung sejak tanggal dd Bulan YYYY"
        $startDate = null;
        // Format (dd-mm-yyyy) dalam tanda kurung
        if (preg_match('/\((\d{1,2}[-\/]\d{1,2}[-\/]\d{4})\)/', $text, $m)) {
            $startDate = $parseIdDate($m[1]);
        }
        // Format teks: "tanggal X Bulan Y Tahun Z" atau "terhitung sejak tanggal X"
        if (!$startDate) {
            preg_match('/(?:sejak|mulai|terhitung|ditandatangani)\s+(?:pada\s+)?(?:hari\s+\w+\s+)?[Tt]anggal\s+(\d{1,2}\s+\w+[\s\n]+\d{4})/is', $text, $m);
            if (isset($m[1])) $startDate = $parseIdDate(preg_replace('/\s+/', ' ', $m[1]));
        }
        // Format teks paling atas: "hari ini, Selasa Tanggal X Bulan Y Tahun Z"
        if (!$startDate) {
            preg_match('/Tanggal\s+\w+\s+Bulan\s+\w+\s+Tahun\s+\w+\s+\((\d{1,2}-\d{1,2}-\d{4})\)/i', $text, $m);
            if (isset($m[1])) $startDate = $parseIdDate($m[1]);
        }
        $mark('start_date', $startDate);

        // 3. Tanggal selesai
        $endDate = null;
        preg_match('/(?:berakhir|sampai dengan|s\.d\.)\s+(?:pada\s+)?tanggal\s+(\d{1,2}\s+\w+[\s\n]+\d{4})/is', $text, $m);
        if (isset($m[1])) $endDate = $parseIdDate(preg_replace('/\s+/', ' ', $m[1]));
        // Cari "hingga November 2026" atau "(hingga Bulan YYYY)" — format Koperasi CESA
        if (!$endDate) {
            preg_match('/hingga\s+(\w+\s+\d{4})/i', $text, $m);
            if (isset($m[1])) $endDate = $parseIdDate('1 ' . $m[1]);
        }
        $mark('end_date', $endDate);

        // 4. Durasi dari tanggal
        if ($data['start_date'] && $data['end_date']) {
            $dur = Carbon::parse($data['start_date'])->diffInMonths(Carbon::parse($data['end_date']));
            $mark('duration_months', $dur > 0 ? $dur : null);
        } else {
            $mark('duration_months', null);
        }

        // 5. Identifikasi para pihak
        // Coba format "Antara [X] Dengan [Y] tentang" (header dokumen)
        $party1Name = $party2Name = '';
        if (preg_match('/Antara\s+(.+?)\s+Dengan\s+(.+?)\s+tentang/is', $text, $m)) {
            $party1Name = $clean($m[1]);
            $party2Name = $clean($m[2]);
        }
        // Fallback: extract dari blok bernomor
        if (!$party1Name) {
            preg_match('/1\.\s+.+?((?:PT|CV|Koperasi|Yayasan|UD|PD)\.?\s+[A-Z][A-Z\s]+?)(?:,|\s+yang|\s+berkedudukan)/is', $text, $m);
            $party1Name = isset($m[1]) ? $clean($m[1]) : '';
        }
        if (!$party2Name) {
            preg_match('/2\.\s+.+?((?:PT|CV|Koperasi|Yayasan|UD|PD)\.?\s+[A-Z][A-Z\s]+?)(?:,|\s+yang|\s+berkedudukan)/is', $text, $m);
            $party2Name = isset($m[1]) ? $clean($m[1]) : '';
        }

        // Tentukan mana klien vs issuer: cocokkan ke document_issuers DB
        $issuers = \App\Models\DocumentIssuer::all(['id', 'name']);
        $matchedIssuer = null;
        $clientPartyName = $party1Name; // default: party 1 = klien
        foreach ($issuers as $issuer) {
            $issuerWords = array_filter(explode(' ', strtolower($issuer->name)), fn($w) => strlen($w) > 3);
            foreach ($issuerWords as $word) {
                if (str_contains(strtolower($party1Name), $word)) {
                    // Party 1 = issuer kita → klien = party 2
                    $matchedIssuer   = $issuer->id;
                    $clientPartyName = $party2Name;
                    break 2;
                }
                if (str_contains(strtolower($party2Name), $word)) {
                    // Party 2 = issuer kita → klien = party 1
                    $matchedIssuer   = $issuer->id;
                    $clientPartyName = $party1Name;
                    break 2;
                }
            }
        }
        $mark('document_issuer_id', $matchedIssuer);

        // 6. Nama perusahaan klien
        $mark('company_name', $clientPartyName ?: null);

        // 7. Kota klien
        $clientBlock = (strtolower($clientPartyName) === strtolower($party1Name)) ? '1' : '2';

        // Helper extract Kab/Kota dari raw address string
        $extractCity = function (string $addr) use ($clean): ?string {
            if (preg_match('/(?:Kab\.?|Kabupaten)\s+([A-Za-z ]+?)(?:,|$)/i', $addr, $cm)) {
                return 'Kab. ' . $clean($cm[1]);
            }
            if (preg_match('/Kota\s+([A-Za-z ]+?)(?:,|$)/i', $addr, $cm)) {
                return 'Kota ' . $clean($cm[1]);
            }
            // Cari segmen sebelum nama provinsi
            if (preg_match('/([A-Za-z ]+?)\s*,\s*(?:Jawa|Sumatera|Kalimantan|Sulawesi|Bali|DKI|DI)\b/i', $addr, $cm)) {
                return $clean($cm[1]);
            }
            return null;
        };

        $cityVal = null;
        // Strategi 1: cari nama perusahaan klien → "berkedudukan di ..."
        if ($clientPartyName) {
            $escaped = preg_quote($clientPartyName, '/');
            preg_match('/' . $escaped . '[^.]{0,300}berkedudukan\s+di\s+([^.;]+?)(?:\.|yang\s+selanjutnya|selanjutnya)/is', $text, $m);
            if (isset($m[1])) $cityVal = $extractCity($clean($m[1]));
        }
        // Strategi 2: cari "PIHAK [PERTAMA/KEDUA]" → "berkedudukan di ..."
        if (!$cityVal) {
            $correspondBlock2 = ($clientBlock === '1') ? 'PERTAMA' : 'KEDUA';
            preg_match('/PIHAK\s+' . $correspondBlock2 . '[^.]{0,500}berkedudukan\s+di\s+([^.;]+?)(?:\.|yang\s+selanjutnya|selanjutnya)/is', $text, $m);
            if (isset($m[1])) $cityVal = $extractCity($clean($m[1]));
        }
        // Strategi 3: fallback blok bernomor (format lama)
        if (!$cityVal) {
            preg_match('/' . $clientBlock . '\.\s+.+?berkedudukan\s+di\s+([^.;]+?)(?:\.|selanjutnya)/is', $text, $m);
            if (isset($m[1])) $cityVal = $extractCity($clean($m[1]));
        }
        $mark('city', $cityVal);

        // 8. Nama PIC klien
        $picName = null;
        $correspondBlock = ($clientBlock === '1') ? 'PERTAMA' : 'KEDUA';
        if (preg_match('/KORESPONDENSI.+?' . $correspondBlock . '\s*\n\s*-\s*Nama\s*:\s*([^\n]+)/is', $text, $m)) {
            $picName = $clean($m[1]);
        }
        // Fallback: nama pertama di blok bernomor (sebelum tanda baca/gelar)
        if (!$picName) {
            preg_match('/' . $clientBlock . '\.\s+([A-Z][A-Z\s]+?)(?:,\s*S\.|,\s*dalam|\s+dalam)/i', $text, $m);
            $picName = isset($m[1]) ? $clean($m[1]) : null;
        }
        $mark('pic_name', $picName);

        // 9. Nama layanan
        // Coba dari "tentang\n[LAYANAN]" di header dokumen
        $serviceName = null;
        if (preg_match('/tentang\s+([A-Z][A-Z\s]+?)(?:\n\n|\nPada|\nAntar)/is', $text, $m)) {
            $serviceName = $clean($m[1]);
        }
        // Fallback: "pekerjaan berupa X"
        if (!$serviceName) {
            preg_match('/(?:pekerjaan berupa|kegiatan)\s+(.+?)(?:\s+bagi\s+PIHAK|\s+mencakup|\s+meliputi|\s+dengan\s+rincian)/is', $text, $m);
            $serviceName = isset($m[1]) ? $clean($m[1]) : null;
        }
        // Fallback: judul setelah "SURAT PERJANJIAN" baris pertama yang all-caps
        if (!$serviceName) {
            preg_match('/SURAT PERJANJIAN[^\n]*\n(?:[^\n]*\n){0,3}([A-Z][A-Z\s]{10,})/i', $text, $m);
            $serviceName = isset($m[1]) ? $clean($m[1]) : null;
        }
        $mark('service_name', $serviceName);

        // 10. Nilai kontrak — ambil nilai terbesar dari pasal pembayaran
        // Cari semua kemunculan Rp di pasal pembayaran
        $contractValue = null;
        preg_match('/(?:CARA|RINCIAN|HARGA|PEMBAYARAN).+?Rp\.?\s*([\d.,]+)/is', $text, $m);
        if (isset($m[1])) {
            $contractValue = $parseRp($m[1]);
        }
        // Fallback: Rp pertama yang nilainya > 1 juta
        if (!$contractValue) {
            preg_match_all('/Rp\.?\s*([\d.,]+)/i', $text, $allM);
            foreach (($allM[1] ?? []) as $raw) {
                $val = $parseRp($raw);
                if ($val && $val > 1000000) { $contractValue = $val; break; }
            }
        }
        $mark('contract_value', $contractValue);

        // 11. Catatan — ambil dari pasal garansi/ketentuan jika ada
        preg_match('/(?:KETENTUAN KHUSUS|GARANSI DAN JAMINAN)\s*\n+((?:.+\n?){1,5})/i', $text, $m);
        $mark('notes', isset($m[1]) ? $clean($m[1]) : null);

        // 12. Bank matching — cari nama bank di teks, cocokkan ke DB
        $banks = \App\Models\BankAccount::all(['id', 'name', 'bank_name']);
        $matchedBank = null;
        foreach ($banks as $bank) {
            foreach ([$bank->bank_name, $bank->name] as $keyword) {
                if ($keyword && stripos($text, $keyword) !== false) {
                    $matchedBank = $bank->id;
                    break 2;
                }
            }
        }
        $mark('bank_account_id', $matchedBank);

        // 13. Project category matching
        $categories = \App\Models\ProjectCategory::all(['id', 'name', 'code']);
        $categoryKeywords = [
            'hosting'      => ['hosting', 'server', 'cloud', 'vps', 'domain', 'sewa server'],
            'lisensi'      => ['lisensi', 'license', 'lifetime', 'smartcoop', 'aplikasi koperasi'],
            'maintenance'  => ['pemeliharaan', 'maintenance', 'support', 'perawatan', 'purna jual'],
            'pengembangan' => ['pengembangan', 'development', 'implementasi sistem', 'website', 'software'],
        ];
        $matchedCat = null;
        foreach ($categories as $cat) {
            if (stripos($text, $cat->name) !== false || stripos($text, $cat->code) !== false) {
                $matchedCat = $cat->id; break;
            }
            $catLower = strtolower($cat->name . ' ' . $cat->code);
            foreach ($categoryKeywords as $group => $keywords) {
                if (str_contains($catLower, $group)) {
                    foreach ($keywords as $kw) {
                        if (str_contains($textLower, $kw)) { $matchedCat = $cat->id; break 3; }
                    }
                }
            }
        }
        $mark('project_category_id', $matchedCat);

        // 14. Invoice type — dari keyword + durasi
        $invoiceType = null;
        if (preg_match('/setiap\s+(?:pada\s+)?(?:setiap\s+)?[Tt]ahun|per\s+[Tt]ahun|tahunan/i', $text)) {
            $invoiceType = 'yearly';
        } elseif (preg_match('/per\s+[Bb]ulan|setiap\s+[Bb]ulan|bulanan/i', $text)) {
            $invoiceType = 'monthly';
        } elseif ($data['duration_months']) {
            $invoiceType = $data['duration_months'] >= 12 ? 'yearly' : 'monthly';
        }
        $mark('invoice_type', $invoiceType);

        $data['interval_months'] = match($invoiceType) {
            'yearly'  => min(($data['duration_months'] ?? 12), 36),
            'monthly' => (($data['duration_months'] ?? 0) > 0 && $data['duration_months'] <= 12) ? $data['duration_months'] : null,
            default   => null,
        };

        $data['_detected'] = $detected;
        $data['_missed']   = $missed;

        // Kumpulkan teks yang ter-detect untuk highlighting di PDF viewer
        $highlightFields = ['spk_number', 'company_name', 'service_name', 'pic_name', 'city'];
        $highlights = [];
        foreach ($highlightFields as $f) {
            if (!empty($data[$f]) && is_string($data[$f]) && strlen(trim($data[$f])) > 4) {
                $highlights[] = trim($data[$f]);
            }
        }
        // Tambah nama pihak lainnya agar coverage lebih luas
        if ($party1Name && strlen($party1Name) > 4) $highlights[] = $party1Name;
        if ($party2Name && strlen($party2Name) > 4) $highlights[] = $party2Name;
        $data['_highlights'] = array_unique($highlights);

        return back()->with('spk_local', $data);
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

        // Request bisa override env (groq/ollama)
        $provider = $request->input('provider', config('services.ai_provider', 'groq'));

        if ($provider === 'ollama') {
            $apiUrl    = rtrim(config('services.ollama.host'), '/') . '/v1/chat/completions';
            $aiModel   = config('services.ollama.model', 'qwen2.5:7b');
            $headers   = ['Content-Type' => 'application/json'];
            $timeout   = 180; // Ollama di CPU bisa 2-3 menit
            $errLabel  = 'Ollama';
        } else {
            $apiKey = config('services.groq.key');
            if (!$apiKey) {
                return back()->with('error', 'GROQ_API_KEY belum dikonfigurasi.');
            }
            $apiUrl   = 'https://api.groq.com/openai/v1/chat/completions';
            $aiModel  = 'llama-3.3-70b-versatile';
            $headers  = ['Authorization' => 'Bearer ' . $apiKey, 'Content-Type' => 'application/json'];
            $timeout  = 60;
            $errLabel = 'Groq';
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

        $withClient = $request->boolean('with_client', false);

        // Ambil referensi dari database untuk dicocokkan AI
        $categories     = \App\Models\ProjectCategory::all(['id', 'name', 'code']);
        $issuers        = \App\Models\DocumentIssuer::all(['id', 'name']);
        $banks          = \App\Models\BankAccount::all(['id', 'name', 'bank_name']);
        $clientCats     = $withClient ? \App\Models\ClientCategory::all(['id', 'name']) : collect();

        $categoriesRef    = $categories->map(fn($c)  => "  ID {$c->id}: {$c->name} (kode: {$c->code})")->join("\n");
        $issuersRef       = $issuers->map(fn($d)     => "  ID {$d->id}: {$d->name}")->join("\n");
        $banksRef         = $banks->map(fn($b)       => "  ID {$b->id}: {$b->bank_name} – {$b->name}")->join("\n");
        $clientCatsRef    = $clientCats->map(fn($cc) => "  ID {$cc->id}: {$cc->name}")->join("\n");

        $clientJsonFields = $withClient ? <<<JSON

  "company_name": "nama perusahaan/instansi pihak pemberi kerja (klien) atau null",
  "city": "kota domisili klien atau null",
  "client_category_id": ID kategori klien yang paling cocok dengan jenis usaha/instansi atau null,
  "client_status": "active_client" jika SPK sudah berlaku/ditandatangani, "prospect" jika masih penawaran,
JSON : '';

        $clientRules = $withClient ? <<<RULES

- Untuk client_category_id, cocokkan jenis usaha/instansi klien dengan daftar berikut:
{$clientCatsRef}
- client_status: "active_client" jika ada nomor SPK/sudah ditandatangani, "prospect" jika belum pasti
RULES : '';

        $prompt = <<<PROMPT
Kamu adalah asisten yang mengekstrak data dari dokumen SPK (Surat Perjanjian Kerja) Indonesia dan mencocokkannya dengan data referensi sistem.

Kembalikan HANYA JSON valid berikut, tanpa penjelasan, tanpa markdown:

{{$clientJsonFields}
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
- Gunakan null untuk field yang tidak bisa dipastikan{$clientRules}

Teks dokumen SPK:
PROMPT;

        try {
            $response = Http::withHeaders($headers)
                ->timeout($timeout)
                ->post($apiUrl, [
                    'model'           => $aiModel,
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
            return back()->with('error', "Koneksi ke {$errLabel} gagal: " . $e->getMessage());
        }

        if ($response->failed()) {
            $msg = $response->json('error.message', 'Unknown error');
            return back()->with('error', "{$errLabel} error: {$msg}");
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

        if ($withClient) {
            $validClientCatIds = $clientCats->pluck('id')->all();
            if (!\in_array($data['client_category_id'] ?? null, $validClientCatIds)) {
                $data['client_category_id'] = null;
            }
            if (!\in_array($data['client_status'] ?? null, ['active_client', 'prospect'])) {
                $data['client_status'] = 'active_client';
            }
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

    public function storeWithClient(Request $request)
    {
        $request->validate([
            'company_name'       => 'required|string|max:255',
            'city'               => 'nullable|string|max:100',
            'pic_name'           => 'nullable|string|max:255',
            'client_category_id' => 'required|exists:client_categories,id',
            'client_status'      => 'required|in:active_client,prospect',
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
            // Cari klien berdasarkan nama, buat jika belum ada
            $client = Client::firstOrCreate(
                ['company_name' => $request->company_name],
                [
                    'client_category_id' => $request->client_category_id,
                    'city'               => $request->city      ?? '-',
                    'address'            => '-',
                    'director'           => '-',
                    'pic'                => $request->pic_name  ?? '-',
                    'client_status'      => $request->client_status,
                    'is_active'          => true,
                ]
            );

            $filePath = $request->hasFile('file')
                ? $request->file('file')->store('spk', 'public')
                : null;

            $category  = \App\Models\ProjectCategory::findOrFail($request->project_category_id);
            $issueDate = Carbon::parse($request->issue_date);
            $spkDate   = $request->start_date ? Carbon::parse($request->start_date) : $issueDate;
            $spkNumber = $request->spk_number ?: Spk::generateNumber($category->code, $spkDate);

            $spk = Spk::create([
                'client_id'          => $client->id,
                'user_id'            => Auth::id(),
                'project_category_id'=> $request->project_category_id,
                'spk_number'         => $spkNumber,
                'service_name'       => $request->service_name,
                'contract_value'     => $request->contract_value,
                'pic_name'           => $request->pic_name ?? $client->pic,
                'start_date'         => $request->start_date,
                'end_date'           => $request->end_date,
                'duration_months'    => $request->duration_months,
                'file_path'          => $filePath,
                'notes'              => $request->notes,
            ]);

            $invoiceNumber = Invoice::generateNumber($category->code, $issueDate, $request->invoice_type);

            $invoice = Invoice::create([
                'user_id'             => Auth::id(),
                'client_id'           => $client->id,
                'spk_id'              => $spk->id,
                'project_category_id' => $request->project_category_id,
                'document_issuer_id'  => $request->document_issuer_id,
                'bank_account_id'     => $request->bank_account_id,
                'invoice_number'      => $invoiceNumber,
                'spk_number'          => $spkNumber,
                'attention'           => $request->pic_name ?? $client->pic,
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
            ->with('success', 'Klien, SPK, dan invoice berhasil dibuat.');
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
