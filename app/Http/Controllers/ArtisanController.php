<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;

class ArtisanController extends Controller
{
    private const ALLOWED = [
        'migrate', 'migrate:status', 'migrate:rollback',
        'storage:link',
        'optimize', 'optimize:clear',
        'config:cache', 'config:clear',
        'route:cache',  'route:clear',
        'view:cache',   'view:clear',
        'cache:clear',
        'queue:restart',
        'invoice:auto-send',
    ];

    public function dashboard()
    {
        return Inertia::render('Admin/ArtisanPanel', [
            'hasSecret' => !empty(config('app.artisan_secret')),
        ]);
    }

    public function run(Request $request)
    {
        $secret = config('app.artisan_secret');

        if (!$secret || $request->input('secret') !== $secret) {
            return response()->json([
                'status'  => 'error',
                'output'  => 'Kunci rahasia salah atau belum dikonfigurasi di .env (ARTISAN_SECRET).',
                'elapsed' => '-',
            ], 403);
        }

        $cmd = $request->input('command');

        if (!in_array($cmd, self::ALLOWED)) {
            return response()->json([
                'status'  => 'error',
                'output'  => "Perintah \"{$cmd}\" tidak diizinkan.",
                'elapsed' => '-',
            ], 400);
        }

        $start = microtime(true);

        $forceCommands = ['migrate', 'migrate:rollback'];
        $args = in_array($cmd, $forceCommands) ? ['--force' => true] : [];

        try {
            Artisan::call($cmd, $args);
            $output = Artisan::output();
            $status = 'ok';
        } catch (\Throwable $e) {
            $output = $e->getMessage();
            $status = 'error';
        }

        $elapsed = round((microtime(true) - $start) * 1000) . ' ms';

        return response()->json([
            'status'  => $status,
            'command' => $cmd,
            'output'  => trim($output) ?: '(selesai tanpa output)',
            'elapsed' => $elapsed,
            'time'    => now()->format('H:i:s'),
        ]);
    }
}
