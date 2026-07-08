<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SpkController extends Controller
{
    private static array $dummyPool = [
        [
            'client_name'         => 'PT Maju Bersama Digital',
            'client_address'      => 'Jl. Sudirman No. 45, Gedung Graha Utama Lt. 8',
            'client_city'         => 'Jakarta Selatan',
            'pic_name'            => 'Budi Santoso',
            'pic_email'           => 'budi.santoso@majubersama.co.id',
            'pic_phone'           => '0812-3456-7890',
            'service_name'        => 'Jasa Pengembangan & Pemeliharaan Sistem Informasi',
            'app_name'            => 'SIM-MBD (Sistem Informasi Manajemen)',
            'contract_value'      => 36000000,
            'contract_value_text' => 'Tiga Puluh Enam Juta Rupiah',
            'payment_term_months' => 12,
            'start_date'          => '2026-08-01',
            'end_date'            => '2027-07-31',
            'duration_months'     => 12,
            'spk_number'          => 'SPK/MBD/IT/VII/2026/042',
            'notes'               => 'Pembayaran bulanan. SLA respon 1×24 jam untuk bug kritis. Termasuk hosting, backup, dan update fitur minor.',
        ],
        [
            'client_name'         => 'CV Karya Nusantara',
            'client_address'      => 'Jl. Diponegoro No. 12A',
            'client_city'         => 'Surabaya',
            'pic_name'            => 'Dewi Rahayu',
            'pic_email'           => 'dewi@karyanusantara.id',
            'pic_phone'           => '0813-9988-7766',
            'service_name'        => 'Pengembangan Website & Toko Online',
            'app_name'            => 'KN-Shop Platform',
            'contract_value'      => 18500000,
            'contract_value_text' => 'Delapan Belas Juta Lima Ratus Ribu Rupiah',
            'payment_term_months' => 6,
            'start_date'          => '2026-09-01',
            'end_date'            => '2027-02-28',
            'duration_months'     => 6,
            'spk_number'          => 'SPK/KN/WEB/IX/2026/011',
            'notes'               => 'Termasuk desain UI/UX, integrasi payment gateway, dan pelatihan admin.',
        ],
        [
            'client_name'         => 'PT Teknologi Andalan Indonesia',
            'client_address'      => 'Jl. Asia Afrika No. 8, Tower Braga Lt. 12',
            'client_city'         => 'Bandung',
            'pic_name'            => 'Reza Firmansyah',
            'pic_email'           => 'reza.f@teknoandalan.com',
            'pic_phone'           => '0811-2233-4455',
            'service_name'        => 'Lisensi & Implementasi SaaS ERP',
            'app_name'            => 'TAI-ERP Cloud Edition',
            'contract_value'      => 72000000,
            'contract_value_text' => 'Tujuh Puluh Dua Juta Rupiah',
            'payment_term_months' => 12,
            'start_date'          => '2026-10-01',
            'end_date'            => '2027-09-30',
            'duration_months'     => 12,
            'spk_number'          => 'SPK/TAI/ERP/X/2026/005',
            'notes'               => 'Biaya per tahun. Termasuk onboarding, migrasi data lama, dan support 24/7.',
        ],
        [
            'client_name'         => 'UD Solusi Prima Teknologi',
            'client_address'      => 'Ruko Mutiara Indah No. 7B, Jl. Raya Darmo',
            'client_city'         => 'Surabaya',
            'pic_name'            => 'Hendra Kusuma',
            'pic_email'           => 'hendra@solusiprimatek.com',
            'pic_phone'           => '0856-7788-9900',
            'service_name'        => 'Pemeliharaan & Support Sistem Kasir',
            'app_name'            => 'PrimaPOS v3',
            'contract_value'      => 9600000,
            'contract_value_text' => 'Sembilan Juta Enam Ratus Ribu Rupiah',
            'payment_term_months' => 12,
            'start_date'          => '2026-08-15',
            'end_date'            => '2027-08-14',
            'duration_months'     => 12,
            'spk_number'          => 'SPK/SPT/POS/VIII/2026/019',
            'notes'               => 'Pembayaran per bulan Rp800.000. Termasuk kunjungan teknis 1× per bulan.',
        ],
        [
            'client_name'         => 'PT Citra Digital Mandiri',
            'client_address'      => 'Jl. Gatot Subroto Kav. 22, Menara CDM Lt. 5',
            'client_city'         => 'Jakarta Pusat',
            'pic_name'            => 'Siti Aminah',
            'pic_email'           => 'siti.aminah@cdigitalmandiri.id',
            'pic_phone'           => '0821-5566-7788',
            'service_name'        => 'Pengembangan Aplikasi Mobile Android & iOS',
            'app_name'            => 'CDM Connect App',
            'contract_value'      => 55000000,
            'contract_value_text' => 'Lima Puluh Lima Juta Rupiah',
            'payment_term_months' => null,
            'start_date'          => '2026-09-15',
            'end_date'            => '2027-03-15',
            'duration_months'     => 6,
            'spk_number'          => 'SPK/CDM/MOB/IX/2026/033',
            'notes'               => 'Pembayaran termin: 30% DP, 40% UAT, 30% go-live. Garansi bug 3 bulan setelah selesai.',
        ],
        [
            'client_name'         => 'CV Berkah Informatika',
            'client_address'      => 'Jl. Kaliurang Km. 9 No. 15',
            'client_city'         => 'Yogyakarta',
            'pic_name'            => 'Ahmad Fauzi',
            'pic_email'           => 'fauzi@berkahinformatika.net',
            'pic_phone'           => '0877-3344-5566',
            'service_name'        => 'Konsultasi IT & Audit Sistem Keamanan',
            'app_name'            => null,
            'contract_value'      => 14000000,
            'contract_value_text' => 'Empat Belas Juta Rupiah',
            'payment_term_months' => null,
            'start_date'          => '2026-10-01',
            'end_date'            => '2026-12-31',
            'duration_months'     => 3,
            'spk_number'          => 'SPK/BI/KON/X/2026/007',
            'notes'               => 'Mencakup audit keamanan jaringan, penetration testing, dan laporan rekomendasi.',
        ],
    ];

    public function index()
    {
        return Inertia::render('Spk/Index', [
            'projectCategories' => \App\Models\ProjectCategory::all(['id', 'name', 'code']),
            'documentIssuers'   => \App\Models\DocumentIssuer::all(['id', 'name']),
            'bankAccounts'      => \App\Models\BankAccount::all(['id', 'name', 'bank_name', 'account_number']),
        ]);
    }

    public function parse(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:10240',
        ]);

        // Pilih salah satu dari 6 dataset berdasarkan nama file
        $idx  = abs(crc32($request->file('file')->getClientOriginalName())) % 6;
        $data = self::$dummyPool[$idx];

        sleep(2); // simulasi AI processing

        return back()->with('spk', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_name'        => 'required|string|max:255',
            'client_address'     => 'nullable|string',
            'client_city'        => 'nullable|string|max:100',
            'pic_name'           => 'nullable|string|max:255',
            'pic_email'          => 'nullable|email|max:255',
            'service_name'       => 'nullable|string|max:255',
            'contract_value'     => 'nullable|numeric|min:0',
            'spk_number'         => 'nullable|string|max:100',
            'notes'              => 'nullable|string',
            'issue_date'         => 'required|date',
            'due_date'           => 'required|date|after_or_equal:issue_date',
            'invoice_type'       => 'required|in:monthly,yearly',
            'interval_months'    => 'nullable|integer|min:1|max:36',
            'project_category_id'=> 'required|exists:project_categories,id',
            'document_issuer_id' => 'required|exists:document_issuers,id',
            'bank_account_id'    => 'nullable|exists:bank_accounts,id',
        ]);

        DB::transaction(function () use ($request) {
            // 1. Cari atau buat client
            $client = Client::firstOrCreate(
                ['company_name' => $request->client_name],
                [
                    'address'   => $request->client_address,
                    'city'      => $request->client_city,
                    'pic'       => $request->pic_name,
                    'is_active' => true,
                ]
            );

            if ($request->pic_email) {
                $client->emails()->firstOrCreate(['email' => $request->pic_email]);
            }

            // 2. Generate nomor invoice
            $issueDate     = Carbon::parse($request->issue_date);
            $category      = \App\Models\ProjectCategory::findOrFail($request->project_category_id);
            $invoiceNumber = Invoice::generateNumber($category->code, $issueDate, $request->invoice_type);

            // 3. Buat invoice
            $invoice = Invoice::create([
                'user_id'             => Auth::id(),
                'client_id'           => $client->id,
                'project_category_id' => $request->project_category_id,
                'document_issuer_id'  => $request->document_issuer_id,
                'bank_account_id'     => $request->bank_account_id,
                'invoice_number'      => $invoiceNumber,
                'spk_number'          => $request->spk_number,
                'attention'           => $request->pic_name,
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

            // 4. Buat item invoice dari nilai kontrak
            if ($request->contract_value) {
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'description' => $request->service_name ?? 'Jasa sesuai SPK',
                    'amount'      => $request->contract_value,
                    'discount'    => 0,
                ]);
            }

            $this->redirectInvoiceId = $invoice->id;
        });

        return redirect()->route('invoices.show', $this->redirectInvoiceId)
            ->with('success', 'Invoice berhasil dibuat dari SPK.');
    }

    private int $redirectInvoiceId;
}
