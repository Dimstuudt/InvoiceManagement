<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $today    = Carbon::today();
        $in3days  = Carbon::today()->addDays(3);
        $in5days  = Carbon::today()->addDays(5);

        $base = Invoice::with(['client:id,company_name', 'projectCategory:id,name,code'])
            ->where('is_demo', false)
            ->where('user_id', auth()->id())
            ->where('payment_status', 'unpaid')
            ->whereNotIn('document_status', ['frozen', 'carried'])
            ->whereNotNull('due_date');

        // Overdue
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

        // Jatuh tempo hari ini
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

        // Jatuh tempo 1-3 hari lagi
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

        // Draft belum diverifikasi — issue_date dalam 5 hari ke depan (atau sudah lewat)
        $draftUnverified = Invoice::with(['client:id,company_name', 'projectCategory:id,name,code'])
            ->where('is_demo', false)
            ->where('user_id', auth()->id())
            ->where('document_status', 'draft')
            ->whereNotNull('issue_date')
            ->whereDate('issue_date', '<=', $in5days)
            ->orderBy('issue_date')
            ->get()
            ->map(fn($inv) => [
                'type'             => 'draft_unverified',
                'id'               => $inv->id,
                'invoice_number'   => $inv->invoice_number,
                'client_name'      => $inv->client?->company_name ?? '—',
                'category'         => $inv->projectCategory?->name ?? '—',
                'issue_date'       => $inv->issue_date->toDateString(),
                'days_until_issue' => $inv->issue_date->gte($today)
                    ? (int) $today->diffInDays($inv->issue_date)
                    : -(int) $today->diffInDays($inv->issue_date),
                'client_id'        => $inv->client_id,
            ]);

        return response()->json([
            'overdue'          => $overdue,
            'due_today'        => $dueToday,
            'due_soon'         => $dueSoon,
            'draft_unverified' => $draftUnverified,
            'total'            => $overdue->count() + $dueToday->count() + $dueSoon->count() + $draftUnverified->count(),
        ]);
    }

    public static function pendingCount(): int
    {
        $paymentCount = Invoice::where('is_demo', false)
            ->where('user_id', auth()->id())
            ->where('payment_status', 'unpaid')
            ->whereNotIn('document_status', ['frozen', 'carried'])
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
            ->whereNotNull('issue_date')
            ->whereDate('issue_date', '<=', Carbon::today()->addDays(5))
            ->count();

        return $paymentCount + $draftCount;
    }
}
