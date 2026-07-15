<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class NotificationController extends Controller
{
    private function buildData(): array
    {
        $today   = Carbon::today();
        $in3days = Carbon::today()->addDays(3);
        $in5days = Carbon::today()->addDays(5);

        $with = ['client:id,company_name', 'projectCategory:id,name,code'];

        $base = Invoice::with($with)
            ->where('is_demo', false)
            ->where('user_id', auth()->id())
            ->where('payment_status', 'unpaid')
            ->whereNotIn('document_status', ['frozen', 'carried', 'inactive'])
            ->whereNotNull('due_date');

        $overdue = (clone $base)
            ->where('due_date', '<', $today)
            ->orderBy('due_date')
            ->get()
            ->map(fn($inv) => [
                'type'           => 'overdue',
                'id'             => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'client_name'    => $inv->client?->company_name ?? '—',
                'category'       => $inv->projectCategory?->name ?? '—',
                'due_date'       => $inv->due_date->toDateString(),
                'days_overdue'   => $today->diffInDays($inv->due_date),
                'client_id'      => $inv->client_id,
            ]);

        $dueToday = (clone $base)
            ->where('document_status', 'verified')
            ->whereDate('due_date', $today)
            ->orderBy('due_date')
            ->get()
            ->map(fn($inv) => [
                'type'           => 'due_today',
                'id'             => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'client_name'    => $inv->client?->company_name ?? '—',
                'category'       => $inv->projectCategory?->name ?? '—',
                'due_date'       => $inv->due_date->toDateString(),
                'days_overdue'   => 0,
                'client_id'      => $inv->client_id,
            ]);

        $dueSoon = (clone $base)
            ->where('document_status', 'verified')
            ->whereDate('due_date', '>', $today)
            ->whereDate('due_date', '<=', $in3days)
            ->orderBy('due_date')
            ->get()
            ->map(fn($inv) => [
                'type'           => 'due_soon',
                'id'             => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'client_name'    => $inv->client?->company_name ?? '—',
                'category'       => $inv->projectCategory?->name ?? '—',
                'due_date'       => $inv->due_date->toDateString(),
                'days_until'     => $today->diffInDays($inv->due_date),
                'client_id'      => $inv->client_id,
            ]);

        $draftBase = Invoice::with($with)
            ->where('is_demo', false)
            ->where('user_id', auth()->id())
            ->where('document_status', 'draft')
            ->where('payment_status', 'unpaid')
            ->whereNotNull('issue_date');

        $draftLate = (clone $draftBase)
            ->whereDate('issue_date', '<', $today)
            ->orderBy('issue_date')
            ->get()
            ->map(fn($inv) => [
                'type'         => 'draft_late',
                'id'           => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'client_name'  => $inv->client?->company_name ?? '—',
                'category'     => $inv->projectCategory?->name ?? '—',
                'issue_date'   => $inv->issue_date->toDateString(),
                'days_past'    => (int) $today->diffInDays($inv->issue_date),
                'client_id'    => $inv->client_id,
            ]);

        $draftUpcoming = (clone $draftBase)
            ->whereDate('issue_date', '>=', $today)
            ->whereDate('issue_date', '<=', $in5days)
            ->orderBy('issue_date')
            ->get()
            ->map(fn($inv) => [
                'type'         => 'draft_upcoming',
                'id'           => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'client_name'  => $inv->client?->company_name ?? '—',
                'category'     => $inv->projectCategory?->name ?? '—',
                'issue_date'   => $inv->issue_date->toDateString(),
                'days_until'   => (int) $today->diffInDays($inv->issue_date),
                'client_id'    => $inv->client_id,
            ]);

        return [
            'overdue'        => $overdue->values(),
            'due_today'      => $dueToday->values(),
            'due_soon'       => $dueSoon->values(),
            'draft_late'     => $draftLate->values(),
            'draft_upcoming' => $draftUpcoming->values(),
            'total'          => $overdue->count() + $dueToday->count() + $dueSoon->count() + $draftLate->count() + $draftUpcoming->count(),
        ];
    }

    public function index(): JsonResponse
    {
        return response()->json($this->buildData());
    }

    public function page(): InertiaResponse
    {
        return Inertia::render('Notifications/Index', $this->buildData());
    }

    public static function pendingCount(): int
    {
        $paymentCount = Invoice::where('is_demo', false)
            ->where('user_id', auth()->id())
            ->where('payment_status', 'unpaid')
            ->whereNotIn('document_status', ['frozen', 'carried', 'inactive'])
            ->whereNotNull('due_date')
            ->where(function ($q) {
                $q->where('due_date', '<', Carbon::today())
                  ->orWhere(function ($q2) {
                      $q2->where('document_status', 'verified')
                         ->whereDate('due_date', '<=', Carbon::today()->addDays(3));
                  });
            })
            ->count();

        $draftCount = Invoice::where('is_demo', false)
            ->where('user_id', auth()->id())
            ->where('document_status', 'draft')
            ->where('payment_status', 'unpaid')
            ->whereNotNull('issue_date')
            ->whereDate('issue_date', '<=', Carbon::today()->addDays(5))
            ->count();

        return $paymentCount + $draftCount;
    }
}
