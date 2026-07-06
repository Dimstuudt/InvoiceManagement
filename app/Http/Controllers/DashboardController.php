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
        $paidThisMonth = Invoice::where('status', 'paid')
            ->where('is_demo', false)
            ->whereBetween('issue_date', [$start, $end])
            ->with('items')
            ->get();
        $revenueThisMonth = $paidThisMonth->sum(fn($i) => $i->total);

        // Outstanding (sent + unpaid, belum overdue)
        $outstanding = Invoice::whereIn('status', ['sent', 'unpaid'])
            ->where('is_demo', false)
            ->where('due_date', '>=', $now->toDateString())
            ->with('items')
            ->get()
            ->sum(fn($i) => $i->total);

        // Overdue
        $overdueRaw = Invoice::whereNotIn('status', ['paid', 'frozen', 'carried'])
            ->where('is_demo', false)
            ->where('due_date', '<', $now->toDateString())
            ->with(['client', 'items'])
            ->orderBy('due_date')
            ->get();

        $overdueInvoices = $overdueRaw->map(fn($inv) => [
            'id'             => $inv->id,
            'invoice_number' => $inv->invoice_number,
            'client_name'    => $inv->client?->company_name,
            'due_date'       => $inv->due_date->toDateString(),
            'days_overdue'   => (int) $inv->due_date->diffInDays($now),
            'total'          => $inv->total,
            'status'         => $inv->status,
        ]);

        // Jatuh tempo 7 hari ke depan
        $upcomingInvoices = Invoice::whereNotIn('status', ['paid', 'frozen', 'carried'])
            ->where('is_demo', false)
            ->whereBetween('due_date', [$now->toDateString(), $now->copy()->addDays(7)->toDateString()])
            ->with(['client', 'items'])
            ->orderBy('due_date')
            ->get()
            ->map(fn($inv) => [
                'id'             => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'client_name'    => $inv->client?->company_name,
                'due_date'       => $inv->due_date->toDateString(),
                'days_until'     => (int) $now->diffInDays($inv->due_date),
                'total'          => $inv->total,
                'status'         => $inv->status,
            ]);

        // Revenue 6 bulan terakhir
        $monthlyRevenue = collect(range(5, 0))->map(function ($i) use ($now) {
            $month = $now->copy()->subMonths($i);
            $value = Invoice::where('status', 'paid')
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
        $revenueLastMonth = Invoice::where('status', 'paid')
            ->where('is_demo', false)
            ->whereYear('issue_date', $lastMonth->year)
            ->whereMonth('issue_date', $lastMonth->month)
            ->with('items')
            ->get()
            ->sum(fn($i) => $i->total);

        $statusCounts = Invoice::where('is_demo', false)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

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
            'status_distribution' => [
                'paid'    => (int) ($statusCounts['paid']    ?? 0),
                'sent'    => (int) ($statusCounts['sent']    ?? 0),
                'unpaid'  => (int) ($statusCounts['unpaid']  ?? 0),
                'draft'   => (int) ($statusCounts['draft']   ?? 0),
                'carried' => (int) ($statusCounts['carried'] ?? 0),
                'frozen'  => (int) ($statusCounts['frozen']  ?? 0),
            ],
            'monthly_revenue'   => $monthlyRevenue,
            'overdue_invoices'  => $overdueInvoices->take(8),
            'upcoming_invoices' => $upcomingInvoices,
        ]);
    }
}
