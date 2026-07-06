<?php

namespace App\Http\Controllers;

use App\Models\CronRun;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Client;
use App\Models\EmailTemplate;
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
        $template = EmailTemplate::where('is_default', true)->first()
            ?? EmailTemplate::orderBy('id')->first();

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
            'email_template_id'   => $template?->id,
            'tax_percentage'      => 0,
            'interval_months'     => 1,
            'with_signature'      => true,
            'is_demo'             => true,
        ];

        $rand = strtoupper(substr(md5(uniqid()), 0, 5));

        if ($type === 'auto-send') {
            $invoice = Invoice::create(array_merge($base, [
                'invoice_number' => "DEMO-SEND-{$rand}/INV/MVC/VII/2026",
                'issue_date'     => Carbon::today(),
                'due_date'       => Carbon::today()->addDays(14),
                'status'         => 'draft',
                'is_marked'      => true,
                'attention'      => 'Demo Auto-Send',
                'notes'          => '[DEMO] Auto-Send: is_marked=true + issue_date=hari ini → cron akan kirim email',
            ]));
            InvoiceItem::create(['invoice_id' => $invoice->id, 'description' => 'Layanan Demo Auto-Send', 'amount' => 100000, 'discount' => 0, 'sort_order' => 0]);

            return response()->json([
                'invoice_number' => $invoice->invoice_number,
                'client'         => $client->company_name,
                'info'           => 'Invoice sudah ditandai (is_marked=true) dan issue_date hari ini. Jalankan cron → akan terkirim.',
            ]);
        }

        if ($type === 'auto-overdue') {
            $invoice = Invoice::create(array_merge($base, [
                'invoice_number' => "DEMO-OVERDUE-{$rand}/INV/MVC/VII/2026",
                'issue_date'     => Carbon::today()->subDays(30),
                'due_date'       => Carbon::yesterday(),
                'status'         => 'sent',
                'is_marked'      => false,
                'attention'      => 'Demo Auto-Overdue',
                'notes'          => '[DEMO] Auto-Overdue: status=sent + due_date=kemarin → cron akan ubah ke unpaid',
            ]));
            InvoiceItem::create(['invoice_id' => $invoice->id, 'description' => 'Layanan Demo Auto-Overdue', 'amount' => 100000, 'discount' => 0, 'sort_order' => 0]);

            return response()->json([
                'invoice_number' => $invoice->invoice_number,
                'client'         => $client->company_name,
                'info'           => 'Invoice status sent dengan due_date kemarin. Jalankan cron → status berubah ke unpaid.',
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
