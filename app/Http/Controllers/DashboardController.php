<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $now   = Carbon::now();
        $start = $now->copy()->startOfMonth();
        $end   = $now->copy()->endOfMonth();

        // Revenue bulan ini (paid)
        $paidThisMonth    = Invoice::where('payment_status', 'paid')
            ->where('is_demo', false)
            ->whereBetween('issue_date', [$start, $end])
            ->with('items')
            ->get();
        $revenueThisMonth = $paidThisMonth->sum(fn($i) => $i->total);

        // Outstanding: verified + unpaid + belum jatuh tempo
        $outstanding = Invoice::where('document_status', 'verified')
            ->where('payment_status', 'unpaid')
            ->where('is_demo', false)
            ->where('due_date', '>=', $now->toDateString())
            ->with('items')
            ->get()
            ->sum(fn($i) => $i->total);

        // Overdue: unpaid, tidak frozen/carried, due_date sudah lewat
        $overdueRaw = Invoice::where('payment_status', 'unpaid')
            ->whereNotIn('document_status', ['frozen', 'carried'])
            ->where('is_demo', false)
            ->where('due_date', '<', $now->toDateString())
            ->with(['client', 'items'])
            ->orderBy('due_date')
            ->get();

        $overdueInvoices = $overdueRaw->map(fn($inv) => [
            'id'              => $inv->id,
            'invoice_number'  => $inv->invoice_number,
            'client_name'     => $inv->client?->company_name,
            'due_date'        => $inv->due_date->toDateString(),
            'days_overdue'    => (int) $inv->due_date->diffInDays($now),
            'total'           => $inv->total,
            'payment_status'  => $inv->payment_status,
            'document_status' => $inv->document_status,
            'send_status'     => $inv->send_status,
        ]);

        // Jatuh tempo 7 hari ke depan: unpaid, tidak frozen/carried
        $upcomingInvoices = Invoice::where('payment_status', 'unpaid')
            ->whereNotIn('document_status', ['frozen', 'carried'])
            ->where('is_demo', false)
            ->whereBetween('due_date', [$now->toDateString(), $now->copy()->addDays(7)->toDateString()])
            ->with(['client', 'items'])
            ->orderBy('due_date')
            ->get()
            ->map(fn($inv) => [
                'id'              => $inv->id,
                'invoice_number'  => $inv->invoice_number,
                'client_name'     => $inv->client?->company_name,
                'due_date'        => $inv->due_date->toDateString(),
                'days_until'      => (int) $now->diffInDays($inv->due_date),
                'total'           => $inv->total,
                'payment_status'  => $inv->payment_status,
                'document_status' => $inv->document_status,
                'send_status'     => $inv->send_status,
            ]);

        // Revenue 6 bulan terakhir
        $monthlyRevenue = collect(range(5, 0))->map(function ($i) use ($now) {
            $month = $now->copy()->subMonths($i);
            $value = Invoice::where('payment_status', 'paid')
                ->where('is_demo', false)
                ->whereYear('issue_date', $month->year)
                ->whereMonth('issue_date', $month->month)
                ->with('items')
                ->get()
                ->sum(fn($inv) => $inv->total);
            return [
                'label'   => $month->translatedFormat('M'),
                'year'    => $month->year,
                'month'   => $month->month,
                'value'   => $value,
                'current' => $month->month === $now->month && $month->year === $now->year,
            ];
        })->values();

        $lastMonth        = $now->copy()->subMonth();
        $revenueLastMonth = Invoice::where('payment_status', 'paid')
            ->where('is_demo', false)
            ->whereYear('issue_date', $lastMonth->year)
            ->whereMonth('issue_date', $lastMonth->month)
            ->with('items')
            ->get()
            ->sum(fn($i) => $i->total);

        // Status distribution berdasarkan 3 kolom baru
        $base = Invoice::where('is_demo', false);
        $statusCounts = [
            'paid'    => (clone $base)->where('payment_status', 'paid')->count(),
            'sent'    => (clone $base)->where('document_status', 'verified')->where('payment_status', 'unpaid')->where('send_status', '!=', 'unsent')->count(),
            'antrean' => (clone $base)->where('document_status', 'verified')->where('payment_status', 'unpaid')->where('send_status', 'unsent')->count(),
            'draft'   => (clone $base)->where('document_status', 'draft')->count(),
            'carried' => (clone $base)->where('document_status', 'carried')->count(),
            'frozen'  => (clone $base)->where('document_status', 'frozen')->count(),
        ];

        return Inertia::render('Dashboard', [
            'stats' => [
                'revenue_this_month'  => $revenueThisMonth,
                'revenue_last_month'  => $revenueLastMonth,
                'outstanding'         => $outstanding,
                'overdue_count'       => $overdueRaw->count(),
                'overdue_amount'      => $overdueRaw->sum(fn($i) => $i->total),
                'invoices_this_month' => Invoice::where('is_demo', false)->whereBetween('issue_date', [$start, $end])->count(),
                'active_clients'      => Client::where('is_active', true)->count(),
            ],
            'status_distribution' => $statusCounts,
            'monthly_revenue'     => $monthlyRevenue,
            'overdue_invoices'    => $overdueInvoices->take(8),
            'upcoming_invoices'   => $upcomingInvoices,
        ]);
    }
}
