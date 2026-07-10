<?php

namespace App\Console\Commands;

use App\Models\CronRun;
use App\Models\Invoice;
use App\Models\EmailTemplateGroup;
use App\Services\PdfService;
use App\Support\ActivityLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use finfo;

class InvoiceAutoSend extends Command
{
    protected $signature   = 'invoice:auto-send {--triggered-by=schedule : Source of trigger (schedule|http|manual)}';
    protected $description = 'Kirim invoice berdasarkan jadwal send1–send5 (verified + unpaid + issue_date tiba)';

    // Interval hari sejak issue_date untuk setiap tahap pengiriman
    private const SEND_SCHEDULE = [
        'send1' => 0,   // hari H (issue_date)
        'send2' => 14,  // +14 hari
        'send3' => 21,  // +7 hari dari send2
        'send4' => 28,  // +7 hari dari send3
        'send5' => 35,  // +7 hari dari send4 — pesan nonaktif
    ];

    public function handle(PdfService $pdfService): int
    {
        $start   = microtime(true);
        $today   = Carbon::today();
        $details = [];
        $sent    = 0;
        $failed  = 0;

        // Ambil semua invoice yang perlu dicek:
        // verified + unpaid + tidak frozen/carried + issue_date sudah lewat atau hari ini
        $candidates = Invoice::with([
                'client.emails', 'projectCategory', 'documentIssuer',
                'bankAccount', 'signature', 'items', 'user',
                'emailTemplateGroup', 'carriedFrom.items',
            ])
            ->where('document_status', 'verified')
            ->where('payment_status', 'unpaid')
            ->where('is_demo', false)
            ->whereDate('issue_date', '<=', $today)
            ->get();

        foreach ($candidates as $invoice) {
            $nextStage = $this->getNextSendStage($invoice, $today);

            if ($nextStage === null) {
                // Belum waktunya dikirim lagi
                continue;
            }

            $emails = $invoice->client->emails->pluck('email')->toArray();

            if (empty($emails)) {
                $details[] = [
                    'invoice_id'     => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'client'         => $invoice->client->company_name ?? '-',
                    'stage'          => $nextStage,
                    'status'         => 'skipped',
                    'error'          => 'Client tidak punya email terdaftar',
                ];
                $failed++;
                continue;
            }

            $group = $invoice->emailTemplateGroup
                ?? EmailTemplateGroup::where('is_default', true)->first()
                ?? EmailTemplateGroup::orderBy('id')->first();

            $stageTpl = $group ? $group->getStage($nextStage) : ['subject' => '', 'body' => ''];
            $subject  = $this->renderTemplate($stageTpl['subject'], $invoice, $nextStage);
            $body     = $this->renderTemplate($stageTpl['body'],    $invoice, $nextStage);

            $subject = $subject ?: "Invoice {$invoice->invoice_number}";
            $body    = $body    ?: "Terlampir invoice {$invoice->invoice_number}.";

            try {
                $html = view('invoices.pdf', [
                    'invoice' => $invoice,
                    'imgB64'  => fn($url) => $this->urlToBase64($url),
                ])->render();

                $pdfBase64 = $pdfService->generate($html);
                $filename  = str_replace('/', '-', $invoice->invoice_number) . '.pdf';
                $toList    = array_map(fn($e) => ['email' => $e], $emails);

                $response = Http::withHeaders([
                    'api-key'      => config('services.brevo.key'),
                    'Content-Type' => 'application/json',
                ])->post('https://api.brevo.com/v3/smtp/email', [
                    'sender'      => [
                        'name'  => config('services.brevo.sender_name'),
                        'email' => config('services.brevo.sender_email'),
                    ],
                    'to'          => $toList,
                    'subject'     => $subject,
                    'textContent' => $body,
                    'attachment'  => [[
                        'content' => $pdfBase64,
                        'name'    => $filename,
                    ]],
                ]);

                if (! $response->successful()) {
                    throw new \RuntimeException($response->json('message', 'Brevo error'));
                }

                $invoice->update(['send_status' => $nextStage]);
                ActivityLogger::log('invoice.auto_sent', $invoice, [
                    'to'    => $emails,
                    'stage' => $nextStage,
                ]);

                $details[] = [
                    'invoice_id'     => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'client'         => $invoice->client->company_name ?? '-',
                    'emails'         => $emails,
                    'stage'          => $nextStage,
                    'status'         => 'sent',
                    'error'          => null,
                ];
                $sent++;

            } catch (\Throwable $e) {
                $details[] = [
                    'invoice_id'     => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'client'         => $invoice->client->company_name ?? '-',
                    'stage'          => $nextStage,
                    'status'         => 'failed',
                    'error'          => $e->getMessage(),
                ];
                $failed++;
                $this->error("Gagal kirim {$invoice->invoice_number} [{$nextStage}]: " . $e->getMessage());
            }
        }

        // ── Loop 2: Kirim receipt ke invoice yang baru lunas ──────────────────
        $paidCandidates = Invoice::with([
                'client.emails', 'projectCategory', 'documentIssuer',
                'bankAccount', 'signature', 'items', 'user',
                'emailTemplateGroup', 'carriedFrom.items',
                'reaktivasiChain', 'prepayChain',
            ])
            ->where('payment_status', 'paid')
            ->whereNull('receipt_sent_at')
            ->where('is_demo', false)
            ->whereRaw("invoice_number NOT REGEXP '^[CRPF]-'")
            ->get();

        foreach ($paidCandidates as $invoice) {
            $emails = $invoice->client->emails->pluck('email')->toArray();

            if (empty($emails)) {
                $invoice->update(['receipt_sent_at' => now()]);
                $details[] = [
                    'invoice_id'     => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'client'         => $invoice->client->company_name ?? '-',
                    'stage'          => 'paid',
                    'status'         => 'skipped',
                    'error'          => 'Client tidak punya email terdaftar',
                ];
                $failed++;
                continue;
            }

            $group = $invoice->emailTemplateGroup
                ?? EmailTemplateGroup::where('is_default', true)->first()
                ?? EmailTemplateGroup::orderBy('id')->first();

            $stageTpl = $group ? $group->getStage('paid') : ['subject' => '', 'body' => ''];
            $subject  = $this->renderTemplate($stageTpl['subject'], $invoice, 'paid');
            $body     = $this->renderTemplate($stageTpl['body'],    $invoice, 'paid');

            $subject = $subject ?: "Konfirmasi Pembayaran – {$invoice->invoice_number}";
            $body    = $body    ?: "Pembayaran invoice {$invoice->invoice_number} telah kami terima. Terlampir bukti penerimaan pembayaran.";

            try {
                $html = view('invoices.receipt', [
                    'invoice' => $invoice,
                    'imgB64'  => fn($url) => $this->urlToBase64($url),
                ])->render();

                $pdfBase64 = $pdfService->generate($html);
                $filename  = 'RCP-' . str_replace('/', '-', $invoice->invoice_number) . '.pdf';
                $toList    = array_map(fn($e) => ['email' => $e], $emails);

                $response = Http::withHeaders([
                    'api-key'      => config('services.brevo.key'),
                    'Content-Type' => 'application/json',
                ])->post('https://api.brevo.com/v3/smtp/email', [
                    'sender'      => [
                        'name'  => config('services.brevo.sender_name'),
                        'email' => config('services.brevo.sender_email'),
                    ],
                    'to'          => $toList,
                    'subject'     => $subject,
                    'textContent' => $body,
                    'attachment'  => [[
                        'content' => $pdfBase64,
                        'name'    => $filename,
                    ]],
                ]);

                if (! $response->successful()) {
                    throw new \RuntimeException($response->json('message', 'Brevo error'));
                }

                $invoice->update(['receipt_sent_at' => now()]);
                ActivityLogger::log('invoice.receipt_sent', $invoice, ['to' => $emails]);

                $details[] = [
                    'invoice_id'     => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'client'         => $invoice->client->company_name ?? '-',
                    'emails'         => $emails,
                    'stage'          => 'paid',
                    'status'         => 'sent',
                    'error'          => null,
                ];
                $sent++;

            } catch (\Throwable $e) {
                $details[] = [
                    'invoice_id'     => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'client'         => $invoice->client->company_name ?? '-',
                    'stage'          => 'paid',
                    'status'         => 'failed',
                    'error'          => $e->getMessage(),
                ];
                $failed++;
                $this->error("Gagal kirim receipt {$invoice->invoice_number}: " . $e->getMessage());
            }
        }
        // ──────────────────────────────────────────────────────────────────────

        $found    = count($details);
        $duration = (int) round((microtime(true) - $start) * 1000);

        $runStatus = match(true) {
            $found === 0              => 'empty',
            $failed === 0             => 'success',
            $sent === 0 && $failed > 0 => 'error',
            $failed > 0               => 'partial',
            default                   => 'success',
        };

        CronRun::create([
            'triggered_by'    => $this->option('triggered-by') ?? 'schedule',
            'invoices_found'  => $found,
            'invoices_sent'   => $sent,
            'invoices_failed' => $failed,
            'details'         => $details,
            'status'          => $runStatus,
            'duration_ms'     => $duration,
        ]);

        $this->info("Selesai: {$found} kandidat, {$sent} terkirim, {$failed} gagal ({$duration} ms)");

        return self::SUCCESS;
    }

    /**
     * Tentukan tahap pengiriman berikutnya untuk invoice ini.
     * Return null jika belum waktunya.
     */
    private function getNextSendStage(Invoice $invoice, Carbon $today): ?string
    {
        $issueDate = Carbon::parse($invoice->issue_date);

        $stages = ['send1', 'send2', 'send3', 'send4', 'send5'];

        foreach ($stages as $stage) {
            $triggerDate = $issueDate->copy()->addDays(self::SEND_SCHEDULE[$stage]);

            // Sudah waktunya DAN belum pernah dikirim di tahap ini atau lebih
            if ($today->gte($triggerDate) && $this->isBeforeStage($invoice->send_status, $stage)) {
                return $stage;
            }
        }

        return null;
    }

    /**
     * Cek apakah current_status masih "sebelum" target_stage.
     * send_status: unsent < send1 < send2 < send3 < send4 < send5
     */
    private function isBeforeStage(string $currentStatus, string $targetStage): bool
    {
        $order = ['unsent' => 0, 'send1' => 1, 'send2' => 2, 'send3' => 3, 'send4' => 4, 'send5' => 5];
        return ($order[$currentStatus] ?? 0) < ($order[$targetStage] ?? 99);
    }

    private function renderTemplate(?string $text, Invoice $invoice, string $stage = 'send1'): string
    {
        if (!$text) return '';
        $fmtDate = fn($d) => $d instanceof \Carbon\Carbon
            ? $d->locale('id')->translatedFormat('j F Y')
            : ($d ? Carbon::parse($d)->locale('id')->translatedFormat('j F Y') : '');

        $fmtRp = fn($v) => $v !== null ? 'Rp ' . number_format((float) $v, 0, ',', '.') : '';

        $vars = [
            '{{invoice_number}}'      => $invoice->invoice_number ?? '',
            '{{issue_date}}'          => $fmtDate($invoice->issue_date),
            '{{due_date}}'            => $fmtDate($invoice->due_date),
            '{{amount}}'              => $fmtRp($invoice->total ?? 0),
            '{{project_name}}'        => $invoice->projectCategory->name ?? '',
            '{{client_name}}'         => $invoice->client->company_name ?? '',
            '{{director}}'            => $invoice->client->director ?? '',
            '{{pic}}'                 => $invoice->client->pic ?? '',
            '{{client_city}}'         => $invoice->client->city ?? '',
            '{{issuer_name}}'         => $invoice->documentIssuer->name ?? '',
            '{{bank_name}}'           => $invoice->bankAccount->bank_name ?? '',
            '{{bank_account_number}}' => $invoice->bankAccount->account_number ?? '',
            '{{bank_holder}}'         => $invoice->bankAccount->name ?? '',
            '{{send_stage}}'          => $stage,
        ];

        return str_replace(array_keys($vars), array_values($vars), $text);
    }

    private function urlToBase64(?string $url): ?string
    {
        if (! $url) return null;
        try {
            if (str_starts_with($url, '/storage/')) {
                $path = storage_path('app/public/' . substr($url, 9));
                if (file_exists($path)) {
                    $content = file_get_contents($path);
                    $mime    = (new finfo(FILEINFO_MIME_TYPE))->buffer($content);
                    return "data:{$mime};base64," . base64_encode($content);
                }
            }
            $appUrl = rtrim(config('app.url'), '/');
            if (str_starts_with($url, $appUrl)) {
                $rel  = ltrim(substr($url, strlen($appUrl)), '/');
                $path = str_starts_with($rel, 'storage/')
                    ? storage_path('app/public/' . substr($rel, 8))
                    : public_path($rel);
                if (file_exists($path)) {
                    $content = file_get_contents($path);
                    $mime    = (new finfo(FILEINFO_MIME_TYPE))->buffer($content);
                    return "data:{$mime};base64," . base64_encode($content);
                }
            }
        } catch (\Throwable) {}
        return null;
    }

    protected function getOptions(): array
    {
        return [
            ['triggered-by', null, \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'Source of trigger', 'schedule'],
        ];
    }
}
