<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $masterPrefixes = ['bank_account.', 'client_category.', 'project_category.', 'document_issuer.', 'signature.', 'email_template.'];

        $query = ActivityLog::with('user')->orderByDesc('created_at');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('subject_label', 'like', "%{$s}%")
                ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$s}%"))
                ->orWhere('ip_address', 'like', "%{$s}%")
            );
        }

        if ($request->filled('kategori')) {
            $kat = $request->kategori;
            $prefixes = match ($kat) {
                'invoice' => ['invoice.'],
                'client'  => ['client.'],
                'auth'    => ['user.'],
                'master'  => $masterPrefixes,
                default   => [],
            };
            if ($prefixes) {
                $query->where(fn($q) => collect($prefixes)->each(fn($p) => $q->orWhere('action', 'like', $p . '%')));
            }
        }

        $logs = $query->paginate(25)->withQueryString();

        $logs->through(fn($log) => [
            'id'            => $log->id,
            'user'          => $log->user ? ['id' => $log->user->id, 'name' => $log->user->name] : null,
            'action'        => $log->action,
            'action_label'  => ActivityLogger::actionLabel($log->action),
            'action_group'  => ActivityLogger::actionGroup($log->action),
            'subject_type'  => $log->subject_type,
            'subject_id'    => $log->subject_id,
            'subject_label' => $log->subject_label,
            'detail'        => $log->detail,
            'ip_address'    => $log->ip_address,
            'user_agent'    => $log->user_agent,
            'created_at'    => $log->created_at,
        ]);

        $stats = [
            'total'   => ActivityLog::count(),
            'invoice' => ActivityLog::where('action', 'like', 'invoice.%')->count(),
            'client'  => ActivityLog::where('action', 'like', 'client.%')->count(),
            'auth'    => ActivityLog::where('action', 'like', 'user.%')->count(),
            'master'  => ActivityLog::where(fn($q) => collect($masterPrefixes)
                ->each(fn($p) => $q->orWhere('action', 'like', $p . '%')))->count(),
        ];

        return Inertia::render('Logs/Index', [
            'logs'    => $logs,
            'stats'   => $stats,
            'filters' => $request->only('search', 'kategori'),
        ]);
    }
}
