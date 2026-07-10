<?php

namespace App\Http\Controllers;

use App\Models\CronRun;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Client;
use App\Models\EmailTemplateGroup;
use App\Models\ProjectCategory;
use App\Models\DocumentIssuer;
use App\Models\BankAccount;
use App\Models\Signature;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;

class CronController extends Controller
{
    /**
     * Public HTTP endpoint for cPanel cron:
     * curl "https://domain.com/cron/run?secret=xxx"
     */
    public function run(Request $request)
    {
        $secret = config('app.artisan_secret');

        if (! $secret || $request->query('secret') !== $secret) {
            return response('Unauthorized', 401);
        }

        $start = microtime(true);

        Artisan::call('invoice:auto-send', ['--triggered-by' => 'http']);

        $elapsed = round((microtime(true) - $start) * 1000);
        $output  = trim(Artisan::output()) ?: '(selesai tanpa output)';

        return response()->json([
            'status'  => 'ok',
            'output'  => $output,
            'elapsed' => "{$elapsed} ms",
            'time'    => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Admin Inertia page
     */
    public function dashboard()
    {
        $runs = CronRun::latest()->limit(5)->get();

        $lastRun  = $runs->first();
        $isAlive  = $lastRun && $lastRun->created_at->diffInMinutes(now()) <= 10;

        return Inertia::render('Admin/CronPanel', [
            'runs'    => $runs,
            'lastRun' => $lastRun,
            'isAlive' => $isAlive,
            'cronUrl' => url('/cron/run') . '?secret=' . config('app.artisan_secret', '(belum diset)'),
            'clients' => Client::orderBy('company_name')->get(['id', 'company_name']),
        ]);
    }

    /**
     * Buat invoice demo untuk test cron
     * type: auto-send | auto-overdue
     */
    public function createDemo(Request $request)
    {
        $type = $request->input('type');

        $clientId = $request->input('client_id');
        $client   = $clientId ? Client::find($clientId) : Client::first();
        if (! $client) {
            return response()->json(['error' => 'Client tidak ditemukan.'], 422);
        }
        $project  = ProjectCategory::first();
        $issuer   = DocumentIssuer::first();
        $bank     = BankAccount::first();
        $sig      = Signature::first();
        $user     = User::first();
        $template = EmailTemplateGroup::where('is_default', true)->first()
            ?? EmailTemplateGroup::orderBy('id')->first();

        if (! $client || ! $project || ! $issuer || ! $bank || ! $sig || ! $user) {
            return response()->json(['error' => 'Master data belum lengkap (Client/ProjectCategory/DocumentIssuer/BankAccount/Signature).'], 422);
        }

        $base = [
            'user_id'             => $user->id,
            'client_id'           => $client->id,
            'project_category_id' => $project->id,
            'document_issuer_id'  => $issuer->id,
            'bank_account_id'     => $bank->id,
            'signature_id'        => $sig->id,
            'email_template_group_id' => $template?->id,
            'tax_percentage'      => 0,
            'interval_months'     => 1,
            'with_signature'      => true,
            'is_demo'             => true,
        ];

        $rand = strtoupper(substr(md5(uniqid()), 0, 5));

        if ($type === 'auto-send') {
            $invoice = Invoice::create(array_merge($base, [
                'invoice_number'  => "DEMO-SEND-{$rand}/INV/MVC/VII/2026",
                'issue_date'      => Carbon::today(),
                'due_date'        => Carbon::today()->addDays(35),
                'document_status' => 'verified',
                'payment_status'  => 'unpaid',
                'send_status'     => 'unsent',
                'attention'       => 'Demo Auto-Send',
                'notes'           => '[DEMO] Auto-Send: document_status=verified + issue_date=hari ini → cron akan kirim send1',
            ]));
            InvoiceItem::create(['invoice_id' => $invoice->id, 'description' => 'Layanan Demo Auto-Send', 'amount' => 100000, 'discount' => 0, 'sort_order' => 0]);

            return response()->json([
                'invoice_number' => $invoice->invoice_number,
                'client'         => $client->company_name,
                'info'           => 'Invoice verified + issue_date hari ini. Jalankan cron → akan terkirim (send1).',
            ]);
        }

        if ($type === 'auto-send-history') {
            // Demo invoice yang sudah melewati send1-send4, siap untuk send5
            $invoice = Invoice::create(array_merge($base, [
                'invoice_number'  => "DEMO-S5-{$rand}/INV/MVC/VII/2026",
                'issue_date'      => Carbon::today()->subDays(35),
                'due_date'        => Carbon::today()->addDays(7),
                'document_status' => 'verified',
                'payment_status'  => 'unpaid',
                'send_status'     => 'send4',
                'attention'       => 'Demo Send5',
                'notes'           => '[DEMO] Sudah send1-send4, issue_date 35 hari lalu → cron akan kirim send5 (peringatan nonaktif)',
            ]));
            InvoiceItem::create(['invoice_id' => $invoice->id, 'description' => 'Layanan Demo Send5', 'amount' => 100000, 'discount' => 0, 'sort_order' => 0]);

            return response()->json([
                'invoice_number' => $invoice->invoice_number,
                'client'         => $client->company_name,
                'info'           => 'Invoice sudah send4, issue_date 35 hari lalu. Jalankan cron → akan kirim send5 (peringatan nonaktif).',
            ]);
        }

        return response()->json(['error' => 'Type tidak dikenal.'], 400);
    }

    /**
     * Manual trigger from the admin UI
     */
    public function manualRun(Request $request)
    {
        $secret = config('app.artisan_secret');

        if (! $secret || $request->input('secret') !== $secret) {
            return response()->json(['status' => 'error', 'output' => 'Kunci rahasia salah.'], 403);
        }

        $start = microtime(true);
        Artisan::call('invoice:auto-send', ['--triggered-by' => 'manual']);
        $elapsed = round((microtime(true) - $start) * 1000);
        $output  = trim(Artisan::output()) ?: '(selesai tanpa output)';

        $lastRun = CronRun::latest()->first();

        return response()->json([
            'status'  => 'ok',
            'output'  => $output,
            'elapsed' => "{$elapsed} ms",
            'time'    => now()->format('H:i:s'),
            'run'     => $lastRun,
        ]);
    }
}
