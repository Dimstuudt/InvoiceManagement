<?php

namespace App\Console\Commands;

use App\Models\CronRun;
use App\Models\Invoice;
use App\Models\EmailTemplate;
use App\Services\PdfService;
use App\Support\ActivityLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use finfo;

class InvoiceAutoSend extends Command
{
    protected $signature   = 'invoice:auto-send {--triggered-by=schedule : Source of trigger (schedule|http|manual)}';
    protected $description = 'Kirim invoice yang sudah ditandai dan telah melewati tanggal terbit';

    public function handle(PdfService $pdfService): int
    {
        $start = microtime(true);

        $invoices = Invoice::with([
                'client.emails', 'projectCategory', 'documentIssuer',
                'bankAccount', 'signature', 'items', 'user',
                'emailTemplate', 'carriedFrom.items',
            ])
            ->where('is_marked', true)
            ->whereDate('issue_date', '<=', Carbon::today())
            ->whereNotIn('status', ['sent', 'paid', 'frozen'])
            ->get();

        $details  = [];
        $sent     = 0;
        $failed   = 0;

        foreach ($invoices as $invoice) {
            $emails = $invoice->client->emails->pluck('email')->toArray();

            if (empty($emails)) {
                $details[] = [
                    'invoice_id'     => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'client'         => $invoice->client->company_name ?? '-',
                    'status'         => 'skipped',
                    'error'          => 'Client tidak punya email terdaftar',
                ];
                $failed++;
                continue;
            }

            // Resolve template: assigned → default → lowest-id fallback
            $tpl = $invoice->emailTemplate
                ?? EmailTemplate::where('is_default', true)->first()
                ?? EmailTemplate::orderBy('id')->first();

            $renderedSubject = $tpl ? $this->renderTemplate($tpl->subject ?? '', $invoice) : '';
            $renderedBody    = $tpl ? $this->renderTemplate($tpl->body ?? '', $invoice)    : '';

            $subject = $renderedSubject ?: "Invoice {$invoice->invoice_number}";
            $body    = $renderedBody    ?: "Terlampir invoice {$invoice->invoice_number}.";

            try {
                $html = view('invoices.pdf', [
                    'invoice' => $invoice,
                    'imgB64'  => fn($url) => $this->urlToBase64($url),
                ])->render();

                $pdfBase64 = $pdfService->generate($html);
                $filename  = str_replace('/', '-', $invoice->invoice_number) . '.pdf';

                $toList = array_map(fn($e) => ['email' => $e], $emails);

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

                $invoice->update(['status' => 'sent', 'is_marked' => false]);
                ActivityLogger::log('invoice.auto_sent', $invoice, ['to' => $emails]);

                $details[] = [
                    'invoice_id'     => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'client'         => $invoice->client->company_name ?? '-',
                    'emails'         => $emails,
                    'status'         => 'sent',
                    'error'          => null,
                ];
                $sent++;

            } catch (\Throwable $e) {
                $details[] = [
                    'invoice_id'     => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'client'         => $invoice->client->company_name ?? '-',
                    'status'         => 'failed',
                    'error'          => $e->getMessage(),
                ];
                $failed++;
                $this->error("Gagal kirim {$invoice->invoice_number}: " . $e->getMessage());
            }
        }

        // ── Auto-ubah ke unpaid: sent + due_date sudah lewat ──
        $overdueInvoices = Invoice::with('client')
            ->where('status', 'sent')
            ->whereDate('due_date', '<', Carbon::today())
            ->get();

        $overdueCount = 0;
        foreach ($overdueInvoices as $ov) {
            $ov->update(['status' => 'unpaid']);
            ActivityLogger::log('invoice.auto_overdue', $ov, ['due_date' => $ov->due_date->toDateString()]);
            $details[] = [
                'invoice_id'     => $ov->id,
                'invoice_number' => $ov->invoice_number,
                'client'         => $ov->client->company_name ?? '-',
                'status'         => 'overdue',
                'error'          => null,
            ];
            $overdueCount++;
        }

        $found    = $invoices->count() + $overdueCount;
        $duration = (int) round((microtime(true) - $start) * 1000);

        $runStatus = match(true) {
            $found === 0        => 'empty',
            $failed === 0       => 'success',
            $sent === 0 && $overdueCount === 0 && $failed > 0 => 'error',
            $failed > 0         => 'partial',
            default             => 'success',
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

        $this->info("Selesai: {$found} ditemukan, {$sent} terkirim, {$failed} gagal ({$duration} ms)");

        return self::SUCCESS;
    }

    private function renderTemplate(?string $text, Invoice $invoice): string
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
