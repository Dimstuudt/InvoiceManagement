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

        $query = ActivityLog::with('user')
            ->where('action', 'not like', 'invoice.auto_%')
            ->orderByDesc('created_at');

        // Search — subject, user name, IP, action
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('subject_label', 'like', "%{$s}%")
                ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$s}%"))
                ->orWhere('ip_address', 'like', "%{$s}%")
                ->orWhere('action', 'like', "%{$s}%")
            );
        }

        // Kategori
        if ($request->filled('kategori')) {
            $kat = $request->kategori;
            $prefixes = match ($kat) {
                'invoice' => ['invoice.'],
                'client'  => ['client.'],
                'auth'    => ['user.', 'profile.'],
                'master'  => $masterPrefixes,
                default   => [],
            };
            if ($prefixes) {
                $query->where(function ($q) use ($prefixes) {
                    foreach ($prefixes as $p) {
                        $q->orWhere('action', 'like', $p . '%');
                    }
                });
            }
        }

        // Date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // User filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Per page (25 / 50 / 100 / 250)
        $perPage = in_array((int) $request->per_page, [25, 50, 100, 250]) ? (int) $request->per_page : 25;

        $logs = $query->paginate($perPage)->withQueryString();

        $logs->through(fn($log) => [
            'id'            => $log->id,
            'user'          => $log->user ? ['id' => $log->user->id, 'name' => $log->user->name, 'avatar_url' => $log->user->avatar_url] : null,
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
            'total'   => ActivityLog::where('action', 'not like', 'invoice.auto_%')->count(),
            'invoice' => ActivityLog::where('action', 'like', 'invoice.%')->where('action', 'not like', 'invoice.auto_%')->count(),
            'client'  => ActivityLog::where('action', 'like', 'client.%')->count(),
            'auth'    => ActivityLog::where(fn($q) => $q->where('action', 'like', 'user.%')->orWhere('action', 'like', 'profile.%'))->count(),
            'master'  => ActivityLog::where(function ($q) use ($masterPrefixes) {
                foreach ($masterPrefixes as $p) {
                    $q->orWhere('action', 'like', $p . '%');
                }
            })->count(),
        ];

        $users = User::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Logs/Index', [
            'logs'    => $logs,
            'stats'   => $stats,
            'users'   => $users,
            'filters' => $request->only('search', 'kategori', 'date_from', 'date_to', 'user_id', 'per_page'),
        ]);
    }

    public function cronLogs(Request $request)
    {
        $query = ActivityLog::whereIn('action', ['invoice.auto_sent', 'invoice.auto_overdue'])
            ->orderByDesc('created_at');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = in_array((int) $request->per_page, [25, 50, 100, 250]) ? (int) $request->per_page : 50;

        $logs = $query->paginate($perPage)->withQueryString();

        $logs->through(fn($log) => [
            'id'            => $log->id,
            'action'        => $log->action,
            'subject_type'  => $log->subject_type,
            'subject_id'    => $log->subject_id,
            'subject_label' => $log->subject_label,
            'detail'        => $log->detail,
            'created_at'    => $log->created_at,
        ]);

        $stats = [
            'total'     => ActivityLog::whereIn('action', ['invoice.auto_sent', 'invoice.auto_overdue'])->count(),
            'auto_sent' => ActivityLog::where('action', 'invoice.auto_sent')->count(),
            'overdue'   => ActivityLog::where('action', 'invoice.auto_overdue')->count(),
        ];

        return Inertia::render('Logs/CronLogs', [
            'logs'    => $logs,
            'stats'   => $stats,
            'filters' => $request->only('date_from', 'date_to', 'per_page'),
        ]);
    }

    public function deleteCronLogs()
    {
        ActivityLog::whereIn('action', ['invoice.auto_sent', 'invoice.auto_overdue'])->delete();

        return redirect()->route('logs.cron');
    }
}
