<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Client;
use App\Models\DocumentIssuer;
use App\Models\EmailTemplateGroup;
use App\Models\Invoice;
use App\Models\ProjectCategory;
use App\Models\Signature;
use App\Support\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    // ── Helper: gabungkan 3 kolom status jadi label tunggal ──────────
    private function statusLabel(Invoice $inv): string
    {
        if ($inv->document_status === 'frozen')   return 'Dibekukan';
        if ($inv->document_status === 'carried')  return 'Carry';
        if ($inv->document_status === 'inactive') return 'Tidak Aktif';
        if ($inv->payment_status  === 'paid')     return 'Lunas';
        if ($inv->document_status === 'draft')    return 'Draft';
        // verified + unpaid
        return match($inv->send_status) {
            'unsent' => 'Terverifikasi',
            'send1'  => 'Terkirim 1×',
            'send2'  => 'Terkirim 2×',
            'send3'  => 'Terkirim 3×',
            'send4'  => 'Terkirim 4×',
            'send5'  => 'Terkirim 5×',
            default  => 'Aktif',
        };
    }

    // ── Helper: apply "status" filter ke query (untuk numbering/export) ──
    private function applyStatusFilter($query, array $statuses)
    {
        if (empty($statuses)) return $query;
        return $query->where(function ($q) use ($statuses) {
            foreach ($statuses as $s) {
                $q->orWhere(function ($inner) use ($s) {
                    match ($s) {
                        'draft'    => $inner->where('document_status', 'draft'),
                        'verified' => $inner->where('document_status', 'verified'),
                        'frozen'   => $inner->where('document_status', 'frozen'),
                        'carried'  => $inner->where('document_status', 'carried'),
                        'inactive' => $inner->where('document_status', 'inactive'),
                        default    => null,
                    };
                });
            }
        });
    }

    public function resetAll()
    {
        $totalInvoices = Invoice::count();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        \App\Models\InvoiceItem::query()->delete();
        Invoice::query()->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        ActivityLogger::log('invoice.reset_all', null, [
            'total_dihapus' => $totalInvoices,
            'keterangan'    => 'Reset data demo — semua invoice dihapus, client aman',
        ]);

        return redirect()->route('invoices.index')->with('success', 'Semua invoice berhasil dihapus.');
    }

    public function index()
    {
        $userId = auth()->id();

        $clients = Client::where('is_active', true)
            ->with(['category', 'invoices' => fn($q) => $q->with('items')])
            ->withCount([
                'invoices as draft_count'      => fn($q) => $q->where('document_status', 'draft')->where('send_status', 'unsent')->where('payment_status', '!=', 'paid'),
                'invoices as sent_count'       => fn($q) => $q->where('document_status', 'verified')->where('payment_status', 'unpaid'),
                'invoices as paid_count'       => fn($q) => $q->where('payment_status', 'paid')->where('document_status', '!=', 'carried'),
                'invoices as unpaid_count'     => fn($q) => $q->where('payment_status', 'unpaid')->where('document_status', 'verified')->where('send_status', '!=', 'unsent'),
                'invoices as frozen_count'     => fn($q) => $q->where('document_status', 'frozen'),
                'invoices as inactive_count'   => fn($q) => $q->where('document_status', 'inactive'),
                'invoices as overdue_count'    => fn($q) => $q->where('payment_status', '!=', 'paid')
                    ->whereNotIn('document_status', ['frozen', 'carried', 'inactive'])
                    ->whereNotNull('due_date')
                    ->where('due_date', '<', now()),
                'invoices as my_invoice_count' => fn($q) => $q->where('user_id', $userId),
            ])
            ->withMax('invoices', 'issue_date')
            ->orderBy('company_name')
            ->get()
            ->sortBy([
                fn($a, $b) => ($b->my_invoice_count > 0) <=> ($a->my_invoice_count > 0),
                fn($a, $b) => strcmp($a->company_name, $b->company_name),
            ])
            ->values();

        $clients->each(function ($c) {
            $c->product_type_count = $c->invoices->pluck('project_category_id')->unique()->filter()->count();
            $c->unpaid_amount      = (float) $c->invoices
                ->filter(fn($inv) => $inv->payment_status === 'unpaid' && $inv->document_status === 'verified')
                ->sum(fn($inv) => $inv->total);
            $c->total_amount = (float) $c->invoices->sum(fn($inv) => $inv->total);
        });

        $summary = [
            // Status Pembayaran
            'total_outstanding' => $clients->sum('unpaid_amount'),
            'total_paid_amount' => $clients->sum(fn($c) => (float) $c->invoices
                ->filter(fn($inv) => $inv->payment_status === 'paid' && $inv->document_status !== 'carried')
                ->sum(fn($inv) => $inv->total)),
            'count_unpaid'      => $clients->sum('sent_count'),
            'count_paid'        => $clients->sum('paid_count'),
            // Status Invoice (doc_status)
            'count_overdue'     => $clients->sum('overdue_count'),
            'count_draft'       => $clients->sum('draft_count'),
            'count_frozen'      => $clients->sum('frozen_count'),
            'count_inactive'    => $clients->sum('inactive_count'),
            // General
            'total_invoices'    => $clients->sum(fn($c) => $c->invoices->count()),
        ];

        return Inertia::render('Invoices/Index', compact('clients', 'summary'));
    }

    public function schedule(Request $request)
    {
        $now   = Carbon::now();
        $month = $request->integer('month', $now->month);
        $year  = $request->integer('year',  $now->year);

        $next = Carbon::create($year, $month, 1)->addMonth();
        $with = [
            'client.emails', 'projectCategory.company', 'user', 'bankAccount', 'documentIssuer', 'emailTemplateGroup',
            'items',
            'carriedFrom.items', 'carriedFrom.carriedFrom.items',
            'reaktivasiChain.items',
            'prepayChain.items',
        ];

        $mainOnly = fn($q) => $q
            ->where('is_demo', false)
            ->whereNotIn('document_status', ['frozen', 'carried'])
            ->where('invoice_number', 'not like', 'C-%')
            ->where('invoice_number', 'not like', 'R-%')
            ->where('invoice_number', 'not like', 'P-%')
            ->where('invoice_number', 'not like', 'F-%');

        $priorityInvoices = Invoice::with($with)
            ->withSum('items', 'amount')
            ->where('user_id', auth()->id())
            ->whereYear('issue_date',  $next->year)
            ->whereMonth('issue_date', $next->month)
            ->tap($mainOnly)
            ->orderBy('due_date')
            ->get();

        $otherInvoices = Invoice::with($with)
            ->withSum('items', 'amount')
            ->where('user_id', auth()->id())
            ->where(fn($q) => $q
                ->whereYear('issue_date', '!=', $next->year)
                ->orWhereMonth('issue_date', '!=', $next->month)
            )
            ->tap($mainOnly)
            ->orderByDesc('issue_date')
            ->get();

        $addChainData = function (Invoice $inv): void {
            $isCarryHead  = (bool) $inv->carried_from_id;
            $isReakHead   = $inv->is_reaktivasi && !$inv->reaktivasi_chain_id && $inv->reaktivasiChain->isNotEmpty();
            $isPrepayHead = $inv->is_prepay && !$inv->prepay_chain_id && $inv->prepayChain->isNotEmpty();

            // Earliest issue_date across chain
            $chainIssue = $inv->issue_date;
            if ($isCarryHead) {
                $cursor = $inv;
                while ($cursor->carried_from_id && $cursor->relationLoaded('carriedFrom') && $cursor->carriedFrom) {
                    $cursor = $cursor->carriedFrom;
                    if ($cursor->issue_date && (!$chainIssue || $cursor->issue_date < $chainIssue)) {
                        $chainIssue = $cursor->issue_date;
                    }
                }
            } elseif ($isReakHead || $isPrepayHead) {
                $members = $isReakHead ? $inv->reaktivasiChain : $inv->prepayChain;
                $min = $members->min('issue_date');
                if ($min && (!$chainIssue || $min < $chainIssue)) $chainIssue = $min;
            }

            // Latest due_date across chain (carry head's own is already the newest)
            $chainDue = $inv->due_date;
            if ($isPrepayHead || $isReakHead) {
                $members = $isPrepayHead ? $inv->prepayChain : $inv->reaktivasiChain;
                $max = $members->max('due_date');
                if ($max && (!$chainDue || $max > $chainDue)) $chainDue = $max;
            }

            // Total across chain
            $chainTotal = match(true) {
                $isCarryHead  => $inv->grand_total,
                $isPrepayHead => $inv->prepay_grand_total,
                $isReakHead   => $inv->reaktivasi_grand_total,
                default       => null,
            };

            $inv->setAttribute('chain_issue_date', $chainIssue instanceof \Carbon\Carbon ? $chainIssue->toDateString() : ($chainIssue ? (string) $chainIssue : null));
            $inv->setAttribute('chain_due_date',   $chainDue   instanceof \Carbon\Carbon ? $chainDue->toDateString()   : ($chainDue   ? (string) $chainDue   : null));
            $inv->setAttribute('chain_total',      $chainTotal);
            $inv->setAttribute('is_chain_head',    $isCarryHead || $isReakHead || $isPrepayHead);
            $inv->setAttribute('chain_type',       $isCarryHead ? 'carry' : ($isPrepayHead ? 'prepay' : ($isReakHead ? 'reaktivasi' : null)));
        };

        $priorityInvoices->each($addChainData);
        $otherInvoices->each($addChainData);

        return Inertia::render('Invoices/ScheduleInvoice', [
            'priorityInvoices' => $priorityInvoices,
            'otherInvoices'    => $otherInvoices,
            'nextMonthLabel'   => $next->translatedFormat('F Y'),
            'filters'          => ['month' => $month, 'year' => $year],
            'emailTemplates'   => EmailTemplateGroup::orderBy('name')->get(['id', 'name', 'is_default']),
        ]);
    }

    public function clientInvoices(Client $client, Request $request)
    {
        $client->load('category', 'emails', 'phones', 'socialMedia');

        $invoices = Invoice::with(['projectCategory.company', 'documentIssuer', 'user', 'emailTemplateGroup'])
            ->withSum('items', 'amount')
            ->withSum('items', 'discount')
            ->where('client_id', $client->id)
            ->orderByDesc('issue_date')
            ->get();

        return Inertia::render('Invoices/ClientInvoices', [
            'client'         => $client->toArray(),
            'invoices'       => $invoices,
            'highlight'      => $request->integer('highlight') ?: null,
            'emailTemplates' => EmailTemplateGroup::orderBy('name')->get(['id', 'name', 'is_default']),
        ]);
    }

    public function create(Request $request)
    {
        $fromInvoice = null;
        if ($request->filled('from')) {
            $fromInvoice = Invoice::with('items')->findOrFail($request->from);

            if ($fromInvoice->payment_status !== 'paid') {
                return redirect()->route('invoices.show', $fromInvoice->id)
                    ->with('error', "Invoice {$fromInvoice->invoice_number} belum lunas. Lunasi dulu sebelum perpanjang.");
            }
        }

        return Inertia::render('Invoices/Create', [
            'clients'           => Client::where('is_active', true)->get(['id', 'company_name', 'address', 'city', 'pic']),
            'projectCategories' => ProjectCategory::all(['id', 'name', 'code']),
            'documentIssuers'   => DocumentIssuer::all(['id', 'name']),
            'bankAccounts'      => BankAccount::all(['id', 'name', 'bank_name', 'account_number']),
            'signatures'        => Signature::all(['id', 'name', 'position']),
            'emailTemplates'    => EmailTemplateGroup::orderBy('name')->get(['id', 'name', 'is_default']),
            'fromInvoice'       => $fromInvoice,
        ]);
    }

    public function numberPreview(Request $request)
    {
        $request->validate([
            'project_category_id' => 'required|exists:project_categories,id',
            'issue_date'          => 'required|date',
            'invoice_type'        => 'required|in:monthly,yearly',
            'seq'                 => 'nullable|integer|min:1',
        ]);

        $category  = ProjectCategory::findOrFail($request->project_category_id);
        $issueDate = Carbon::parse($request->issue_date);
        $year      = $issueDate->year;
        $roman     = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][$issueDate->month - 1];

        if ($request->filled('seq')) {
            $seqNum = (int) $request->seq;
            $seq    = str_pad($seqNum, 3, '0', STR_PAD_LEFT);
            $number = "{$seq}/{$category->code}/INV/MVC/{$roman}/{$year}";

            $query = Invoice::whereYear('issue_date', $year)
                ->where('invoice_number', 'like', "%/INV/MVC/%/{$year}")
                ->where('is_demo', false);
            if ($request->filled('exclude_id')) {
                $query->where('id', '!=', (int) $request->exclude_id);
            }
            $taken = $query->get()
                ->filter(fn($inv) => (int) explode('/', $inv->invoice_number)[0] === $seqNum)
                ->isNotEmpty();

            return response()->json(['number' => $number, 'seq' => $seqNum, 'available' => !$taken]);
        }

        $number = Invoice::generateNumber($category->code, $issueDate, $request->invoice_type);
        $seq    = (int) explode('/', $number)[0];
        return response()->json(['number' => $number, 'seq' => $seq, 'available' => true]);
    }

    public function numbering(Request $request)
    {
        $now         = now();
        $defaultFrom = $now->copy()->startOfMonth()->toDateString();
        $defaultTo   = $now->toDateString();

        $fromDate     = $request->input('from_date', $defaultFrom) ?: $defaultFrom;
        $toDate       = $request->input('to_date',   $defaultTo)   ?: $defaultTo;
        $statuses = array_values(array_filter(
            (array) $request->input('statuses', []),
            fn($s) => in_array($s, ['draft', 'verified', 'frozen', 'carried', 'inactive', 'overdue', 'outstanding'])
        ));
        $paymentStatuses = array_values(array_filter(
            (array) $request->input('payment_statuses', []),
            fn($s) => in_array($s, ['paid', 'unpaid'])
        ));
        $sendStatuses = array_values(array_filter(
            (array) $request->input('send_statuses', []),
            fn($s) => in_array($s, ['unsent', 'send1', 'send2', 'send3', 'send4', 'send5'])
        ));
        $clientId    = $request->integer('client_id', 0);
        $companyIds  = array_values(array_filter((array) $request->input('company_ids', []),  fn($v) => is_numeric($v)));
        $categoryIds = array_values(array_filter((array) $request->input('category_ids', []), fn($v) => is_numeric($v)));

        $clients    = Client::orderBy('company_name')->get(['id', 'company_name']);
        $userCatIds = Invoice::where('is_demo', false)->whereNotNull('project_category_id')->pluck('project_category_id')->unique();
        $allCats    = ProjectCategory::whereIn('id', $userCatIds)->orderBy('name')->get(['id', 'name', 'code', 'company_id']);
        $companies  = \App\Models\Company::whereIn('id', $allCats->pluck('company_id')->unique())
            ->orderBy('name')->get(['id', 'name', 'code']);

        $query = Invoice::with([
                'client', 'projectCategory.company', 'items',
                'carriedFrom.items',
                'carriedFrom.carriedFrom.items',
                'prepayChain.items',
                'reaktivasiChain.items',
            ])
            ->where('invoice_number', 'regexp', '^[0-9]+/')
            ->where('is_demo', false)
            ->whereBetween('issue_date', [$fromDate, $toDate])
            ->when($clientId > 0,         fn($q) => $q->where('client_id', $clientId))
            ->when(!empty($companyIds),   fn($q) => $q->whereHas('projectCategory', fn($i) => $i->whereIn('company_id', $companyIds)))
            ->when(!empty($categoryIds),  fn($q) => $q->whereIn('project_category_id', $categoryIds))
            ->when(!empty($sendStatuses), fn($q) => $q->whereIn('send_status', $sendStatuses))
            ->when(!empty($paymentStatuses), fn($q) => $q->whereIn('payment_status', $paymentStatuses));

        // Status invoice (document_status + computed overdue/outstanding)
        $hasComputed = !empty(array_filter($statuses, fn($s) => in_array($s, ['overdue', 'outstanding'])));
        if (!$hasComputed && !empty($statuses)) {
            $query = $this->applyStatusFilter($query, $statuses);
        }

        $today    = $now->toDateString();
        $invoices = $query->get()
            ->map(function ($inv) use ($today) {
                $parts    = explode('/', $inv->invoice_number);
                $inv->seq = (int) ($parts[0] ?? 0);

                if ($inv->carried_from_id) {
                    $grandTotal = $inv->grand_total;
                } elseif ($inv->is_prepay && !$inv->prepay_chain_id) {
                    $grandTotal = $inv->prepay_grand_total;
                } elseif ($inv->is_reaktivasi && !$inv->reaktivasi_chain_id) {
                    $grandTotal = $inv->reaktivasi_grand_total;
                } else {
                    $grandTotal = $inv->total;
                }

                $inv->setAttribute('computed_total', $grandTotal);

                $isOverdue = $inv->payment_status !== 'paid'
                    && !in_array($inv->document_status, ['frozen', 'carried', 'inactive'])
                    && $inv->due_date && $inv->due_date->toDateString() < $today;

                $isOutstanding = $inv->payment_status === 'unpaid'
                    && $inv->document_status === 'verified'
                    && $inv->issue_date && $inv->issue_date->toDateString() <= $today
                    && $inv->due_date  && $inv->due_date->toDateString()  >= $today;

                $inv->setAttribute('is_overdue',     $isOverdue);
                $inv->setAttribute('is_outstanding', $isOutstanding);

                return $inv;
            })
            ->filter(fn($inv) => $inv->seq > 0);

        // Post-query status invoice filter (ketika ada computed status)
        if (!empty($statuses) && $hasComputed) {
            $invoices = $invoices->filter(function ($inv) use ($statuses) {
                foreach ($statuses as $s) {
                    if ($s === 'overdue'     && $inv->is_overdue)                     return true;
                    if ($s === 'outstanding' && $inv->is_outstanding)                 return true;
                    if ($s === 'draft'       && $inv->document_status === 'draft')    return true;
                    if ($s === 'verified'    && $inv->document_status === 'verified') return true;
                    if ($s === 'frozen'      && $inv->document_status === 'frozen')   return true;
                    if ($s === 'carried'     && $inv->document_status === 'carried')  return true;
                    if ($s === 'inactive'    && $inv->document_status === 'inactive') return true;
                }
                return false;
            });
        }

        $invoices = $invoices->sortByDesc('seq')->values();

        $summary = [
            'total_nilai'       => $invoices->sum('computed_total'),
            'total_outstanding' => $invoices->where('is_outstanding', true)->sum('computed_total'),
            'total_overdue'     => $invoices->where('is_overdue', true)->sum('computed_total'),
        ];

        // Monthly stats: count per year/month (all user invoices, ignoring current filters)
        $rawStats = Invoice::selectRaw('YEAR(issue_date) as y, MONTH(issue_date) as m, COUNT(*) as cnt')
            ->where('invoice_number', 'regexp', '^[0-9]+/')
            ->where('is_demo', false)
            ->whereNotNull('issue_date')
            ->groupByRaw('YEAR(issue_date), MONTH(issue_date)')
            ->orderByRaw('YEAR(issue_date) DESC, MONTH(issue_date)')
            ->get();

        $monthlyStats = [];
        foreach ($rawStats as $row) {
            $monthlyStats[(int)$row->y][(int)$row->m] = (int)$row->cnt;
        }

        return Inertia::render('Invoices/Numbering', [
            'invoices'      => $invoices->map(fn($inv) => $inv->makeHidden(['items', 'carried_from', 'prepay_chain', 'reaktivasi_chain'])),
            'from_date'     => $fromDate,
            'to_date'       => $toDate,
            'clients'       => $clients,
            'companies'     => $companies,
            'categories'    => $allCats,
            'summary'       => $summary,
            'monthly_stats' => $monthlyStats,
            'filters'       => [
                'statuses'         => $statuses,
                'payment_statuses' => $paymentStatuses,
                'send_statuses'    => $sendStatuses,
                'client_id'        => $clientId ?: null,
                'company_ids'      => $companyIds,
                'category_ids'     => $categoryIds,
            ],
        ]);
    }

    public function numberingExport(Request $request)
    {
        $now         = now();
        $defaultFrom = $now->copy()->startOfMonth()->toDateString();
        $defaultTo   = $now->toDateString();

        $fromDate     = $request->input('from_date', $defaultFrom) ?: $defaultFrom;
        $toDate       = $request->input('to_date',   $defaultTo)   ?: $defaultTo;
        $statuses = array_values(array_filter(
            (array) $request->input('statuses', []),
            fn($s) => in_array($s, ['draft', 'verified', 'frozen', 'carried', 'inactive', 'overdue', 'outstanding'])
        ));
        $paymentStatuses = array_values(array_filter(
            (array) $request->input('payment_statuses', []),
            fn($s) => in_array($s, ['paid', 'unpaid'])
        ));
        $sendStatuses = array_values(array_filter(
            (array) $request->input('send_statuses', []),
            fn($s) => in_array($s, ['unsent', 'send1', 'send2', 'send3', 'send4', 'send5'])
        ));
        $clientId    = $request->integer('client_id', 0);
        $companyIds  = array_values(array_filter((array) $request->input('company_ids', []),  fn($v) => is_numeric($v)));
        $categoryIds = array_values(array_filter((array) $request->input('category_ids', []), fn($v) => is_numeric($v)));

        $query = Invoice::with([
                'client', 'projectCategory.company', 'items',
                'carriedFrom.items',
                'carriedFrom.carriedFrom.items',
                'prepayChain.items',
                'reaktivasiChain.items',
            ])
            ->where('invoice_number', 'regexp', '^[0-9]+/')
            ->where('is_demo', false)
            ->whereBetween('issue_date', [$fromDate, $toDate])
            ->when($clientId > 0,         fn($q) => $q->where('client_id', $clientId))
            ->when(!empty($companyIds),   fn($q) => $q->whereHas('projectCategory', fn($i) => $i->whereIn('company_id', $companyIds)))
            ->when(!empty($categoryIds),  fn($q) => $q->whereIn('project_category_id', $categoryIds))
            ->when(!empty($sendStatuses), fn($q) => $q->whereIn('send_status', $sendStatuses))
            ->when(!empty($paymentStatuses), fn($q) => $q->whereIn('payment_status', $paymentStatuses));

        $hasComputed = !empty(array_filter($statuses, fn($s) => in_array($s, ['overdue', 'outstanding'])));
        if (!$hasComputed && !empty($statuses)) {
            $query = $this->applyStatusFilter($query, $statuses);
        }

        $today    = $now->toDateString();
        $invoices = $query->get()
            ->map(function ($inv) use ($today) {
                $parts    = explode('/', $inv->invoice_number);
                $inv->seq = (int) ($parts[0] ?? 0);

                if ($inv->carried_from_id) {
                    $grandTotal = $inv->grand_total;
                } elseif ($inv->is_prepay && !$inv->prepay_chain_id) {
                    $grandTotal = $inv->prepay_grand_total;
                } elseif ($inv->is_reaktivasi && !$inv->reaktivasi_chain_id) {
                    $grandTotal = $inv->reaktivasi_grand_total;
                } else {
                    $grandTotal = $inv->total;
                }

                $inv->setAttribute('computed_total', $grandTotal);
                $inv->setAttribute('is_overdue',
                    $inv->payment_status !== 'paid'
                    && !in_array($inv->document_status, ['frozen', 'carried', 'inactive'])
                    && $inv->due_date && $inv->due_date->toDateString() < $today
                );
                $inv->setAttribute('is_outstanding',
                    $inv->payment_status === 'unpaid'
                    && $inv->document_status === 'verified'
                    && $inv->issue_date && $inv->issue_date->toDateString() <= $today
                    && $inv->due_date  && $inv->due_date->toDateString()  >= $today
                );
                return $inv;
            })
            ->filter(fn($inv) => $inv->seq > 0);

        if (!empty($statuses) && $hasComputed) {
            $invoices = $invoices->filter(function ($inv) use ($statuses) {
                foreach ($statuses as $s) {
                    if ($s === 'overdue'     && $inv->is_overdue)                     return true;
                    if ($s === 'outstanding' && $inv->is_outstanding)                 return true;
                    if ($s === 'draft'       && $inv->document_status === 'draft')    return true;
                    if ($s === 'verified'    && $inv->document_status === 'verified') return true;
                    if ($s === 'frozen'      && $inv->document_status === 'frozen')   return true;
                    if ($s === 'carried'     && $inv->document_status === 'carried')  return true;
                    if ($s === 'inactive'    && $inv->document_status === 'inactive') return true;
                }
                return false;
            });
        }

        $invoices = $invoices->sortByDesc('seq')->values();

        // ── Column selector ───────────────────────────────────────
        $allColDefs = [
            'seq'            => ['Seq',            8],
            'invoice_number' => ['Nomor Invoice', 28],
            'client'         => ['Client',        28],
            'category'       => ['Kategori',      20],
            'issue_date'     => ['Terbit',        14],
            'due_date'       => ['Jatuh Tempo',   14],
            'status'         => ['Status',        18],
            'overdue'        => ['Overdue',       10],
            'nominal'        => ['Nominal (Rp)',  20],
        ];
        $validKeys    = array_keys($allColDefs);
        $selectedCols = array_values(array_filter(
            (array) $request->input('columns', $validKeys),
            fn($c) => in_array($c, $validKeys)
        ));
        if (empty($selectedCols)) $selectedCols = $validKeys;

        $colMap  = [];
        foreach ($selectedCols as $i => $key) {
            $colMap[$key] = chr(65 + $i);
        }
        $lastCol = chr(65 + count($selectedCols) - 1);

        // ── Spreadsheet setup ─────────────────────────────────────
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Semua Invoice');

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['vertical' => 'center'],
        ];

        foreach ($selectedCols as $key) {
            [$label, $width] = $allColDefs[$key];
            $col = $colMap[$key];
            $sheet->setCellValue("{$col}1", $label);
            $sheet->getStyle("{$col}1")->applyFromArray($headerStyle);
            $sheet->getColumnDimension($col)->setWidth($width);
        }
        $sheet->getRowDimension(1)->setRowHeight(22);

        $numFmt  = '#,##0';
        $altFill = ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'EEEEFF']]];
        $redFill = ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FEE2E2']]];

        foreach ($invoices as $i => $inv) {
            $row = $i + 2;

            $rowValues = [
                'seq'            => $inv->seq,
                'invoice_number' => $inv->invoice_number,
                'client'         => $inv->client?->company_name ?? '-',
                'category'       => $inv->projectCategory?->name ?? '-',
                'issue_date'     => $inv->issue_date->format('d/m/Y'),
                'due_date'       => $inv->due_date?->format('d/m/Y') ?? '-',
                'status'         => $this->statusLabel($inv),
                'overdue'        => $inv->is_overdue ? 'Ya' : '-',
                'nominal'        => (int) $inv->computed_total,
            ];

            foreach ($selectedCols as $key) {
                $col = $colMap[$key];
                $sheet->setCellValue("{$col}{$row}", $rowValues[$key]);
                if ($key === 'nominal') {
                    $sheet->getStyle("{$col}{$row}")->getNumberFormat()->setFormatCode($numFmt);
                }
            }

            if ($inv->is_overdue) {
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($redFill);
            } elseif ($i % 2 !== 0) {
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($altFill);
            }
        }

        $sheet->freezePane('A2');
        $lastRow = count($invoices) + 1;
        $sheet->setAutoFilter("A1:{$lastCol}{$lastRow}");
        if (isset($colMap['nominal'])) {
            $nomCol = $colMap['nominal'];
            $sheet->getStyle("{$nomCol}1:{$nomCol}{$lastRow}")->getFont()->setBold(true);
        }

        $filename = "invoice-{$fromDate}-sd-{$toDate}.xlsx";
        $tmpPath  = sys_get_temp_dir() . '/' . $filename;

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tmpPath);

        ActivityLogger::log('invoice.numbering_exported', null, [
            'from'  => $fromDate,
            'to'    => $toDate,
            'count' => $invoices->count(),
        ]);

        return response()->download($tmpPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'           => 'required|exists:clients,id',
            'project_category_id' => 'required|exists:project_categories,id',
            'document_issuer_id'  => 'required|exists:document_issuers,id',
            'bank_account_id'     => 'required|exists:bank_accounts,id',
            'signature_id'        => 'nullable|exists:signatures,id',
            'email_template_group_id'   => 'nullable|exists:email_template_groups,id',
            'with_signature'      => 'boolean',
            'spk_number'          => 'nullable|string|max:100',
            'issue_date'          => 'required|date',
            'due_date'            => 'required|date|after_or_equal:issue_date',
            'attention'           => 'nullable|string|max:255',
            'notes'               => 'nullable|string',
            'document_status'     => 'nullable|in:draft',
            'tax_percentage'      => 'nullable|numeric|min:0|max:100',
            'interval_months'     => 'nullable|integer|min:1|max:12',
            'invoice_type'        => 'required|in:monthly,yearly',
            'invoice_number'      => 'nullable|string|unique:invoices,invoice_number',
        ]);

        $category  = ProjectCategory::findOrFail($validated['project_category_id']);
        $issueDate = Carbon::parse($validated['issue_date']);

        $invoiceNumber = !empty($validated['invoice_number'])
            ? $validated['invoice_number']
            : Invoice::generateNumber($category->code, $issueDate, $validated['invoice_type']);

        if (Invoice::where('invoice_number', $invoiceNumber)->exists()) {
            return back()->withErrors(['invoice_number' => 'Nomor invoice sudah digunakan, pilih nomor lain.'])->withInput();
        }

        $invoice = Invoice::create([
            ...$validated,
            'user_id'         => auth()->id(),
            'invoice_number'  => $invoiceNumber,
            'with_signature'  => $request->boolean('with_signature'),
            'document_status' => $validated['document_status'] ?? 'draft',
            'payment_status'  => 'unpaid',
            'send_status'     => 'unsent',
        ]);

        ActivityLogger::log('invoice.created', $invoice, ['client' => $invoice->client?->company_name]);

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice dibuat. Silakan tambahkan item.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load([
            'client.emails', 'projectCategory', 'documentIssuer',
            'bankAccount', 'signature', 'items', 'user', 'emailTemplateGroup',
            'parent', 'children', 'carriedFrom.items',
        ]);

        return Inertia::render('Invoices/Show', [
            'invoice' => array_merge($invoice->toArray(), [
                'subtotal'        => $invoice->subtotal,
                'discount_amount' => $invoice->discount_amount,
                'after_discount'  => $invoice->after_discount,
                'dpp_base'        => $invoice->dpp_base,
                'tax_amount'      => $invoice->tax_amount,
                'total'           => $invoice->total,
                'carried_total'   => $invoice->carried_total,
                'total_payable'   => $invoice->grand_total,
                'client_emails'   => $invoice->client?->emails->pluck('email')->toArray() ?? [],
            ]),
            'emailTemplates' => EmailTemplateGroup::orderBy('name')->get(['id', 'name', 'is_default']),
        ]);
    }

    public function saveAll(Request $request, Invoice $invoice)
    {
        $request->validate([
            'items'               => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.amount'      => 'required|numeric|min:0',
            'items.*.discount'    => 'nullable|numeric|min:0',
            'tax_percentage'      => 'nullable|numeric|min:0|max:100',
            'discount_type'       => 'nullable|in:percent,amount',
            'discount_value'      => 'nullable|numeric|min:0',
            'is_dpp'              => 'boolean',
            'spk_number'          => 'nullable|string|max:100',
            'attention'           => 'nullable|string|max:255',
            'notes'               => 'nullable|string',
        ]);

        $invoice->items()->delete();
        foreach ($request->items as $i => $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'amount'      => $item['amount'],
                'discount'    => isset($item['discount']) && $item['discount'] > 0 ? $item['discount'] : null,
                'sort_order'  => $i,
            ]);
        }

        $dv = $request->input('discount_value');

        $invoice->update([
            'tax_percentage' => $request->input('tax_percentage'),
            'discount_type'  => $dv > 0 ? $request->input('discount_type') : null,
            'discount_value' => $dv > 0 ? $dv : null,
            'is_dpp'         => $request->boolean('is_dpp'),
            'spk_number'     => $request->input('spk_number'),
            'attention'      => $request->input('attention'),
            'notes'          => $request->input('notes'),
        ]);

        ActivityLogger::log('invoice.saved', $invoice);

        return back()->with('success', 'Invoice disimpan.');
    }

    public function updateItems(Request $request, Invoice $invoice)
    {
        $request->validate([
            'items'               => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.amount'      => 'required|numeric|min:0',
            'items.*.discount'    => 'nullable|numeric|min:0',
            'spk_number'          => 'nullable|string|max:100',
            'attention'           => 'nullable|string|max:255',
            'notes'               => 'nullable|string',
        ]);

        $invoice->items()->delete();
        foreach ($request->items as $i => $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'amount'      => $item['amount'],
                'discount'    => isset($item['discount']) && $item['discount'] > 0 ? $item['discount'] : null,
                'sort_order'  => $i,
            ]);
        }

        $invoice->update($request->only('spk_number', 'attention', 'notes'));

        ActivityLogger::log('invoice.items_updated', $invoice);

        return back()->with('success', 'Item invoice diperbarui.');
    }

    public function updateTotals(Request $request, Invoice $invoice)
    {
        $request->validate([
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_type'  => 'nullable|in:percent,amount',
            'discount_value' => 'nullable|numeric|min:0',
            'is_dpp'         => 'boolean',
            'spk_number'     => 'nullable|string|max:100',
            'attention'      => 'nullable|string|max:255',
            'notes'          => 'nullable|string',
        ]);

        $dv = $request->input('discount_value');

        $invoice->update([
            'tax_percentage' => $request->input('tax_percentage'),
            'discount_type'  => $dv > 0 ? $request->input('discount_type') : null,
            'discount_value' => $dv > 0 ? $dv : null,
            'is_dpp'         => $request->boolean('is_dpp'),
            'spk_number'     => $request->input('spk_number'),
            'attention'      => $request->input('attention'),
            'notes'          => $request->input('notes'),
        ]);

        ActivityLogger::log('invoice.totals_updated', $invoice);

        return back()->with('success', 'Total invoice diperbarui.');
    }

    public function edit(Invoice $invoice)
    {
        if ($invoice->payment_status === 'paid') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Invoice yang sudah lunas tidak dapat diedit.');
        }
        if ($invoice->document_status === 'verified') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Invoice dalam antrean kirim tidak dapat diedit. Keluarkan dari antrean terlebih dahulu.');
        }

        $invoice->load('items', 'projectCategory');

        return Inertia::render('Invoices/Edit', [
            'invoice'           => array_merge($invoice->toArray(), [
                'items' => $invoice->items->toArray(),
                'total' => $invoice->total,
            ]),
            'clients'           => Client::where('is_active', true)->get(['id', 'company_name', 'address', 'city', 'pic']),
            'projectCategories' => ProjectCategory::all(['id', 'name', 'code']),
            'documentIssuers'   => DocumentIssuer::all(['id', 'name']),
            'bankAccounts'      => BankAccount::all(['id', 'name', 'bank_name', 'account_number']),
            'signatures'        => Signature::all(['id', 'name', 'position']),
        ]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->payment_status === 'paid') {
            return back()->with('error', 'Invoice yang sudah lunas tidak dapat diedit.');
        }
        if ($invoice->document_status === 'verified') {
            return back()->with('error', 'Invoice dalam antrean kirim tidak dapat diedit. Keluarkan dari antrean terlebih dahulu.');
        }

        $validated = $request->validate([
            'client_id'           => 'required|exists:clients,id',
            'project_category_id' => 'required|exists:project_categories,id',
            'document_issuer_id'  => 'required|exists:document_issuers,id',
            'bank_account_id'     => 'required|exists:bank_accounts,id',
            'signature_id'        => 'nullable|exists:signatures,id',
            'with_signature'      => 'boolean',
            'spk_number'          => 'nullable|string|max:100',
            'issue_date'          => 'required|date',
            'due_date'            => 'required|date|after_or_equal:issue_date',
            'attention'           => 'nullable|string|max:255',
            'notes'               => 'nullable|string',
            'tax_percentage'      => 'nullable|numeric|min:0|max:100',
            'invoice_type'        => 'required|in:monthly,yearly',
            'invoice_number'      => 'nullable|string|unique:invoices,invoice_number,' . $invoice->id,
        ]);

        $validated['invoice_number'] = !empty($validated['invoice_number'])
            ? $validated['invoice_number']
            : $invoice->invoice_number;

        $invoice->update([
            ...$validated,
            'with_signature' => $request->boolean('with_signature'),
        ]);

        ActivityLogger::log('invoice.updated', $invoice);

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice berhasil diperbarui.');
    }

    public function destroy(Invoice $invoice)
    {
        // Blokir hapus tengah rantai — hanya HEAD (tanpa child) yang boleh dihapus
        if ($invoice->children()->exists()) {
            return back()->with('error', 'Invoice ini tidak bisa dihapus karena ada invoice lanjutan di atasnya. Hapus dari yang paling atas (HEAD) terlebih dahulu.');
        }

        // Jika HEAD carry dihapus, restore C- parent
        if ($invoice->carried_from_id) {
            $carriedParent = Invoice::find($invoice->carried_from_id);
            if ($carriedParent && str_starts_with($carriedParent->invoice_number, 'C-')) {
                $restoredNumber = substr($carriedParent->invoice_number, 2);
                $carriedParent->update([
                    'invoice_number'  => $restoredNumber,
                    'document_status' => 'draft',
                    'payment_status'  => 'unpaid',
                    'send_status'     => 'unsent',
                ]);
                ActivityLogger::log('invoice.restored', $carriedParent, [
                    'reason'          => 'HEAD carry dihapus',
                    'restored_number' => $restoredNumber,
                ]);
            }
        }

        // Jika HEAD resume dihapus, restore F- parent
        if ($invoice->parent_invoice_id) {
            $frozenParent = Invoice::find($invoice->parent_invoice_id);
            if ($frozenParent && str_starts_with($frozenParent->invoice_number, 'F-')) {
                $restoredNumber = substr($frozenParent->invoice_number, 2);
                $frozenParent->update([
                    'invoice_number'  => $restoredNumber,
                    'document_status' => 'frozen',
                ]);
                ActivityLogger::log('invoice.restored', $frozenParent, [
                    'reason'          => 'HEAD resume dihapus',
                    'restored_number' => $restoredNumber,
                ]);
            }
        }

        $prepayChainId = $invoice->prepay_chain_id;

        ActivityLogger::log('invoice.deleted', $invoice);
        $invoice->delete();

        // Kalau P- member terakhir dihapus, reset is_prepay pada HEAD
        if ($prepayChainId) {
            $head = Invoice::find($prepayChainId);
            if ($head && !Invoice::where('prepay_chain_id', $prepayChainId)->exists()) {
                $head->update(['is_prepay' => false]);
            }
        }

        return back()->with('success', 'Invoice berhasil dihapus.');
    }

    public function sendEmail(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'emails'   => 'required|array|min:1',
            'emails.*' => 'required|email',
            'subject'  => 'required|string|max:255',
            'body'     => 'required|string',
        ]);

        $invoice->load(['client', 'projectCategory', 'documentIssuer.senderDomain',
                        'bankAccount', 'signature', 'items', 'user', 'carriedFrom.items']);

        $senderName  = $invoice->documentIssuer?->senderDomain?->display_name  ?? config('services.brevo.sender_name');
        $senderEmail = $invoice->documentIssuer?->senderDomain?->sender_email   ?? config('services.brevo.sender_email');

        $html = view('invoices.pdf', [
            'invoice' => $invoice,
            'imgB64'  => fn($url) => $this->urlToBase64($url),
        ])->render();

        $pdfBase64 = app(\App\Services\PdfService::class)->generate($html);
        $filename  = str_replace('/', '-', $invoice->invoice_number) . '.pdf';
        $toList    = array_map(fn($e) => ['email' => $e], $validated['emails']);
        $htmlEmail = view('emails.wrapper', ['invoice' => $invoice, 'body' => $validated['body'], 'filename' => $filename])->render();

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'api-key'      => config('services.brevo.key'),
            'Content-Type' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender'      => [
                'name'  => $senderName,
                'email' => $senderEmail,
            ],
            'to'          => $toList,
            'subject'     => $validated['subject'],
            'htmlContent' => $htmlEmail,
            'attachment'  => [[
                'content' => $pdfBase64,
                'name'    => $filename,
            ]],
        ]);

        if (! $response->successful()) {
            return back()->with('error', 'Gagal mengirim email: ' . $response->json('message', 'Unknown error'));
        }

        // Advance send_status: unsent → send1, send1 → send2, dst.
        $nextSendStatus = match($invoice->send_status) {
            'unsent' => 'send1',
            'send1'  => 'send2',
            'send2'  => 'send3',
            'send3'  => 'send4',
            'send4'  => 'send5',
            default  => $invoice->send_status,
        };

        $invoice->update([
            'send_status'     => $nextSendStatus,
            'document_status' => 'verified',
        ]);

        ActivityLogger::log('invoice.email_sent', $invoice, ['to' => $validated['emails']]);

        return back()->with('success', 'Invoice berhasil dikirim ke ' . implode(', ', $validated['emails']) . '.');
    }

    public function download(Invoice $invoice)
    {
        $invoice->load([
            'client', 'projectCategory', 'documentIssuer',
            'bankAccount', 'signature', 'items', 'user', 'carriedFrom.items',
            'reaktivasiChain.items', 'prepayChain.items',
        ]);

        $html     = view('invoices.pdf', ['invoice' => $invoice, 'imgB64' => fn($url) => $this->urlToBase64($url)])->render();
        $pdf      = base64_decode(app(\App\Services\PdfService::class)->generate($html));
        $filename = str_replace('/', '-', $invoice->invoice_number) . '.pdf';

        ActivityLogger::log('invoice.downloaded', $invoice);

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function printView(Invoice $invoice)
    {
        $invoice->load([
            'client', 'projectCategory', 'documentIssuer',
            'bankAccount', 'signature', 'items', 'user', 'carriedFrom.items',
            'reaktivasiChain.items', 'prepayChain.items',
        ]);

        ActivityLogger::log('invoice.printed', $invoice);

        return view('invoices.pdf', [
            'invoice'   => $invoice,
            'imgB64'    => fn($url) => $this->urlToBase64($url),
            'printMode' => true,
        ]);
    }

    public function receipt(Invoice $invoice)
    {
        $invoice->load([
            'client', 'projectCategory', 'documentIssuer',
            'bankAccount', 'signature', 'items', 'user', 'carriedFrom.items',
            'reaktivasiChain.items', 'prepayChain.items',
        ]);

        return view('invoices.receipt', [
            'invoice' => $invoice,
            'imgB64'  => fn($url) => $this->urlToBase64($url),
        ]);
    }

    public function export(Request $request)
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->input('from') . '-01')->startOfDay()
            : now()->startOfMonth();
        $to = $request->filled('to')
            ? Carbon::parse($request->input('to') . '-01')->endOfMonth()
            : now()->endOfMonth();

        $validStatuses = ['draft', 'verified', 'paid', 'frozen', 'carried'];
        $statuses = array_values(array_filter(
            (array) $request->input('status', []),
            fn($s) => in_array($s, $validStatuses)
        ));

        $query = Invoice::with(['client', 'projectCategory', 'items'])
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()]);

        $query = $this->applyStatusFilter($query, $statuses);

        $invoices = $query->orderBy('issue_date')->orderBy('invoice_number')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Invoice');

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['vertical' => 'center'],
        ];

        $headers = [
            'A' => ['No',               6],
            'B' => ['Nomor Invoice',    24],
            'C' => ['Client',           28],
            'D' => ['Kategori Proyek',  20],
            'E' => ['Tanggal Issue',    14],
            'F' => ['Jatuh Tempo',      14],
            'G' => ['Status',           18],
            'H' => ['Subtotal (Rp)',    18],
            'I' => ['Diskon (Rp)',      16],
            'J' => ['DPP (Rp)',         16],
            'K' => ['Pajak (%)',        10],
            'L' => ['Pajak (Rp)',       16],
            'M' => ['Total (Rp)',       18],
        ];

        foreach ($headers as $col => [$label, $width]) {
            $sheet->setCellValue("{$col}1", $label);
            $sheet->getStyle("{$col}1")->applyFromArray($headerStyle);
            $sheet->getColumnDimension($col)->setWidth($width);
        }
        $sheet->getRowDimension(1)->setRowHeight(22);

        $numFmt  = '#,##0';
        $altFill = ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'EEEEFF']]];

        foreach ($invoices as $i => $inv) {
            $row = $i + 2;

            $sheet->setCellValue("A{$row}", $i + 1);
            $sheet->setCellValue("B{$row}", $inv->invoice_number);
            $sheet->setCellValue("C{$row}", $inv->client?->company_name ?? '-');
            $sheet->setCellValue("D{$row}", $inv->projectCategory?->name ?? '-');
            $sheet->setCellValue("E{$row}", $inv->issue_date->format('d/m/Y'));
            $sheet->setCellValue("F{$row}", $inv->due_date->format('d/m/Y'));
            $sheet->setCellValue("G{$row}", $this->statusLabel($inv));
            $sheet->setCellValue("H{$row}", (int) $inv->subtotal);
            $sheet->setCellValue("I{$row}", (int) $inv->discount_amount);
            $sheet->setCellValue("J{$row}", (int) $inv->dpp_base);
            $sheet->setCellValue("K{$row}", $inv->tax_percentage ?? 0);
            $sheet->setCellValue("L{$row}", (int) $inv->tax_amount);
            $sheet->setCellValue("M{$row}", (int) $inv->total);

            foreach (['H', 'I', 'J', 'L', 'M'] as $numCol) {
                $sheet->getStyle("{$numCol}{$row}")->getNumberFormat()->setFormatCode($numFmt);
            }

            if ($i % 2 !== 0) {
                $sheet->getStyle("A{$row}:M{$row}")->applyFromArray($altFill);
            }
        }

        $sheet->freezePane('A2');
        $lastRow = count($invoices) + 1;
        $sheet->setAutoFilter("A1:M{$lastRow}");
        $sheet->getStyle("M1:M{$lastRow}")->getFont()->setBold(true);

        $filename = 'laporan-invoice-' . $from->format('Y-m') . '-sd-' . $to->format('Y-m') . '.xlsx';
        $tmpPath  = sys_get_temp_dir() . '/' . $filename;

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tmpPath);

        ActivityLogger::log('invoice.exported', null, [
            'from'     => $from->toDateString(),
            'to'       => $to->toDateString(),
            'statuses' => empty($statuses) ? 'all' : implode(',', $statuses),
            'count'    => $invoices->count(),
        ]);

        return response()->download($tmpPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }

    public function updateTax(Request $request, Invoice $invoice)
    {
        $request->validate(['tax_percentage' => 'nullable|numeric|min:0|max:100']);
        $invoice->update(['tax_percentage' => $request->tax_percentage]);
        ActivityLogger::log('invoice.tax_updated', $invoice, ['tax' => $request->tax_percentage]);
        return back()->with('success', 'Pajak diperbarui.');
    }

    public function toggleMark(Invoice $invoice)
    {
        if ($invoice->payment_status === 'paid') {
            return back()->with('error', 'Invoice yang sudah lunas tidak perlu dimasukkan ke antrean kirim.');
        }

        if ($invoice->is_prepay && $invoice->prepay_chain_id !== null) {
            return back()->with('error', 'Invoice anakan prepay tidak bisa masuk antrean kirim. Ditangani oleh kepala rantai.');
        }

        $isVerified    = $invoice->document_status === 'verified';
        $newDocStatus  = $isVerified ? 'draft' : 'verified';
        $invoice->update(['document_status' => $newDocStatus]);
        ActivityLogger::log($isVerified ? 'invoice.unmarked' : 'invoice.marked', $invoice);
        return back()->with('success', $isVerified
            ? 'Invoice dikeluarkan dari antrean.'
            : 'Invoice diverifikasi dan masuk antrean kirim.'
        );
    }

    public function updateInterval(Request $request, Invoice $invoice)
    {
        $request->validate(['interval_months' => 'nullable|integer|min:1|max:12']);
        $invoice->update(['interval_months' => $request->interval_months]);
        ActivityLogger::log('invoice.interval_changed', $invoice, ['interval' => $request->interval_months]);
        return back()->with('success', 'Interval diperbarui.');
    }

    public function updateMeta(Request $request, Invoice $invoice)
    {
        $request->validate([
            'spk_number' => 'nullable|string|max:100',
            'attention'  => 'nullable|string|max:255',
            'notes'      => 'nullable|string',
        ]);
        $invoice->update($request->only('spk_number', 'attention', 'notes'));
        ActivityLogger::log('invoice.meta_updated', $invoice);
        return back()->with('success', 'Disimpan.');
    }

    public function updateStatus(Request $request, Invoice $invoice)
    {
        $request->validate([
            'payment_status'  => 'nullable|in:unpaid,paid',
            'document_status' => 'nullable|in:draft,verified',
        ]);

        // Blokir reversal: invoice yang sudah paid tidak boleh dikembalikan ke unpaid
        if ($request->filled('payment_status') && $request->payment_status === 'unpaid' && $invoice->payment_status === 'paid') {
            return back()->with('error', 'Invoice yang sudah lunas tidak dapat dibatalkan pembayarannya.');
        }

        // Blokir anakan prepay dari tandai lunas individual
        if ($invoice->is_prepay && $invoice->prepay_chain_id !== null) {
            return back()->with('error', 'Invoice anakan prepay tidak bisa ditandai lunas individual. Gunakan invoice kepala rantai.');
        }

        $changes = [];

        if ($request->filled('payment_status')) {
            $oldPayment = $invoice->payment_status;
            $changes['payment_status'] = $request->payment_status;

            $invoice->update(['payment_status' => $request->payment_status]);
            $invoice->refresh();

            ActivityLogger::log('invoice.status_changed', $invoice, [
                'from' => $oldPayment,
                'to'   => $request->payment_status,
                'type' => 'payment',
            ]);

            if ($request->payment_status === 'paid') {
                // Keluarkan dari antrean kirim otomatis saat invoice dilunasi
                if ($invoice->document_status === 'verified') {
                    $invoice->update(['document_status' => 'draft']);
                    $invoice->refresh();
                    ActivityLogger::log('invoice.status_changed', $invoice, [
                        'from' => 'verified',
                        'to'   => 'draft',
                        'type' => 'document',
                        'reason' => 'auto_on_paid',
                    ]);
                }

                $this->payCarriedAncestors($invoice);
                $this->payReaktivasiChain($invoice);
                $this->payPrepayChain($invoice);

                if ($invoice->interval_months) {
                    if ($invoice->is_prepay && !$invoice->prepay_chain_id) {
                        $lastPrepay    = Invoice::where('prepay_chain_id', $invoice->id)->orderByDesc('issue_date')->first();
                        $recurringBase = $lastPrepay ?? $invoice;
                        if ($recurringBase->children()->doesntExist()) {
                            $this->generateRecurring($recurringBase);
                            return back()->with('success', 'Status diperbarui. Invoice perpanjangan setelah periode prepay dibuat sebagai draft.');
                        }
                    } elseif ($invoice->children()->doesntExist()) {
                        $this->generateRecurring($invoice);
                        return back()->with('success', 'Status diperbarui. Invoice perpanjangan ' . $invoice->interval_months . ' bulan ke depan dibuat sebagai draft.');
                    }
                }
            }
        }

        if ($request->filled('document_status')) {
            if ($request->document_status === 'verified' && $invoice->payment_status === 'paid') {
                return back()->with('error', 'Invoice yang sudah lunas tidak bisa dimasukkan ke antrean kirim.');
            }
            $oldDoc = $invoice->document_status;
            $invoice->update(['document_status' => $request->document_status]);
            ActivityLogger::log('invoice.status_changed', $invoice, [
                'from' => $oldDoc,
                'to'   => $request->document_status,
                'type' => 'document',
            ]);
        }

        return back()->with('success', 'Status diperbarui.');
    }

    public function prepay(Request $request, Invoice $invoice)
    {
        if ($invoice->payment_status === 'paid' || in_array($invoice->document_status, ['frozen', 'carried'])) {
            return back()->with('error', 'Hanya invoice aktif yang bisa di-prepay.');
        }
        if (!$invoice->interval_months) {
            return back()->with('error', 'Invoice tidak memiliki interval perpanjangan.');
        }
        if ($invoice->prepay_chain_id) {
            return back()->with('error', 'Prepay hanya bisa dilakukan dari invoice HEAD.');
        }

        $times = max(1, min(24, (int) $request->input('times', 1)));

        $invoice->load('items', 'projectCategory', 'client');

        $created = [];

        DB::transaction(function () use ($invoice, $times, &$created) {
            if (!$invoice->is_prepay) {
                $invoice->update(['is_prepay' => true]);
            }

            $lastInChain = Invoice::where('prepay_chain_id', $invoice->id)
                ->orderByDesc('issue_date')->first();

            for ($i = 0; $i < $times; $i++) {
                $child       = $this->doSinglePrepay($invoice, $lastInChain);
                $created[]   = $child->invoice_number;
                $lastInChain = $child;
            }
        });

        $totalChain = Invoice::where('prepay_chain_id', $invoice->id)->count();

        ActivityLogger::log('invoice.prepay', $invoice, [
            'head_number'        => $invoice->invoice_number,
            'client'             => $invoice->client->company_name ?? '-',
            'added'              => $created,
            'times'              => $times,
            'total_chain'        => $totalChain,
            'interval'           => $invoice->interval_months . ' bulan',
            'jumlah_per_invoice' => 'Rp ' . number_format($invoice->total, 0, ',', '.'),
        ]);

        $msg = $times === 1
            ? "Prepay +{$invoice->interval_months} bulan → {$created[0]}."
            : "Prepay {$times}× sekaligus → s.d. " . end($created) . ".";

        return back()->with('success', $msg);
    }

    private function doSinglePrepay(Invoice $head, ?Invoice $lastInChain): Invoice
    {
        $interval    = $head->interval_months;
        $seqPart     = explode('/', $head->invoice_number)[0];
        $code        = $head->projectCategory->code;
        $romanMonths = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];

        $baseDate        = Carbon::parse($lastInChain ? $lastInChain->issue_date : $head->issue_date);
        $previousInvoice = $lastInChain ?? $head;
        $cursor          = $baseDate->copy()->addMonths($interval);

        $roman   = $romanMonths[(int) $cursor->format('n') - 1];
        $year    = $cursor->format('Y');
        $dueDate = $cursor->copy()->addDays(14);
        $number  = "P-{$seqPart}/{$code}/INV/MVC/{$roman}/{$year}";

        $child = Invoice::create([
            'user_id'                 => $head->user_id,
            'client_id'               => $head->client_id,
            'project_category_id'     => $head->project_category_id,
            'document_issuer_id'      => $head->document_issuer_id,
            'bank_account_id'         => $head->bank_account_id,
            'signature_id'            => $head->signature_id,
            'email_template_group_id' => $head->email_template_group_id,
            'with_signature'          => $head->with_signature,
            'attention'               => $head->attention,
            'notes'                   => $head->notes,
            'invoice_number'          => $number,
            'invoice_type'            => $head->invoice_type,
            'issue_date'              => $cursor->toDateString(),
            'due_date'                => $dueDate->toDateString(),
            'payment_status'          => 'unpaid',
            'document_status'         => 'draft',
            'send_status'             => 'unsent',
            'tax_percentage'          => $head->tax_percentage,
            'discount_type'           => $head->discount_type,
            'discount_value'          => $head->discount_value,
            'is_dpp'                  => $head->is_dpp,
            'interval_months'         => $interval,
            'is_prepay'               => true,
            'prepay_chain_id'         => $head->id,
            'parent_invoice_id'       => $previousInvoice->id,
        ]);

        foreach ($head->items as $item) {
            $child->items()->create([
                'description' => $item->description,
                'amount'      => $item->amount,
                'discount'    => $item->discount ?? 0,
                'sort_order'  => $item->sort_order,
            ]);
        }

        return $child;
    }

    public function carry(Request $request, Invoice $invoice)
    {
        if (preg_match('/^[CRPF]-/', $invoice->invoice_number)) {
            return back()->with('error', 'Carry hanya bisa dilakukan dari invoice utama (head), bukan dari chain member.');
        }
        if ($invoice->payment_status === 'paid' || in_array($invoice->document_status, ['frozen', 'carried'])) {
            return back()->with('error', 'Hanya invoice aktif yang belum lunas yang bisa di-carry.');
        }
        if (!$invoice->interval_months) {
            return back()->with('error', 'Invoice tidak memiliki interval perpanjangan.');
        }
        if ($invoice->children()->exists()) {
            return back()->with('error', 'Invoice sudah memiliki perpanjangan.');
        }

        $times = max(1, min(24, (int) $request->input('times', 1)));

        $invoice->load('items', 'projectCategory', 'client');

        $originalNumber = $invoice->invoice_number;
        $head           = null;

        DB::transaction(function () use ($invoice, $times, &$head) {
            $current = $invoice;
            for ($i = 0; $i < $times; $i++) {
                $current->load('items', 'projectCategory');
                $head = $this->doSingleCarry($current);
                $current = $head;
            }
        });

        $periodLabel = $times === 1
            ? Carbon::parse($head->issue_date)->translatedFormat('M Y')
            : Carbon::parse($head->issue_date)->translatedFormat('M Y') . " (+{$times}x)";

        ActivityLogger::log('invoice.carried', $invoice, [
            'original_number' => $originalNumber,
            'times'           => $times,
            'head_number'     => $head->invoice_number,
            'client'          => $invoice->client->company_name ?? '-',
            'jumlah'          => 'Rp ' . number_format($invoice->total, 0, ',', '.'),
            'periode_baru'    => $periodLabel,
        ]);

        $msg = $times === 1
            ? "Invoice di-carry. Tunggakan masuk ke {$head->invoice_number}."
            : "Invoice di-carry {$times}× sekaligus. HEAD baru: {$head->invoice_number}.";

        return back()->with('success', $msg);
    }

    private function doSingleCarry(Invoice $current): Invoice
    {
        $issueDate    = Carbon::parse($current->issue_date)->addMonths($current->interval_months);
        $dueDate      = $issueDate->copy()->addDays(14);
        $oldNumber    = $current->invoice_number;
        $seqPart      = explode('/', $oldNumber)[0];
        $inheritedSeq = ltrim($seqPart, 'C-');
        $romanMonths  = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
        $roman        = $romanMonths[(int) $issueDate->format('n') - 1];
        $year         = $issueDate->format('Y');
        $categoryCode = $current->projectCategory->code;
        $newNumber    = "{$inheritedSeq}/{$categoryCode}/INV/MVC/{$roman}/{$year}";

        $current->update(['invoice_number' => "C-{$oldNumber}"]);

        $child = Invoice::create([
            'user_id'                 => $current->user_id,
            'client_id'               => $current->client_id,
            'project_category_id'     => $current->project_category_id,
            'document_issuer_id'      => $current->document_issuer_id,
            'bank_account_id'         => $current->bank_account_id,
            'signature_id'            => $current->signature_id,
            'email_template_group_id' => $current->email_template_group_id,
            'with_signature'          => $current->with_signature,
            'attention'               => $current->attention,
            'notes'                   => $current->notes,
            'invoice_number'          => $newNumber,
            'invoice_type'            => $current->invoice_type,
            'issue_date'              => $issueDate,
            'due_date'                => $dueDate,
            'payment_status'          => 'unpaid',
            'document_status'         => 'draft',
            'send_status'             => 'unsent',
            'tax_percentage'          => $current->tax_percentage,
            'discount_type'           => $current->discount_type,
            'discount_value'          => $current->discount_value,
            'is_dpp'                  => $current->is_dpp,
            'interval_months'         => $current->interval_months,
            'spk_id'                  => $current->spk_id,
            'spk_number'              => $current->spk_number,
            'parent_invoice_id'       => $current->id,
            'carried_from_id'         => $current->id,
        ]);

        foreach ($current->items as $item) {
            $child->items()->create([
                'description' => $item->description,
                'amount'      => $item->amount,
                'discount'    => $item->discount ?? 0,
                'sort_order'  => $item->sort_order,
            ]);
        }

        $current->update(['document_status' => 'carried']);

        return $child;
    }

    public function freeze(Invoice $invoice)
    {
        abort_if(preg_match('/^[CRPF]-/', $invoice->invoice_number), 403, 'Freeze hanya bisa dilakukan dari invoice utama (head).');
        abort_if($invoice->payment_status === 'paid' || in_array($invoice->document_status, ['frozen', 'carried']), 403, 'Hanya invoice aktif yang belum lunas yang bisa dibekukan.');
        $invoice->update(['document_status' => 'frozen']);
        ActivityLogger::log('invoice.frozen', $invoice);
        return back()->with('success', 'Invoice dibekukan.');
    }

    public function resume(Request $request, Invoice $invoice)
    {
        if ($invoice->document_status !== 'frozen') {
            return back()->with('error', 'Invoice tidak dalam kondisi frozen.');
        }
        if ($invoice->children()->exists()) {
            return back()->with('error', 'Perpanjangan sudah dilanjutkan sebelumnya.');
        }

        $request->validate([
            'issue_date'      => 'required|date',
            'interval_months' => 'required|integer|min:1|max:36',
        ]);

        $invoice->load('items', 'projectCategory');

        $oldNumber   = $invoice->invoice_number;
        $seqPart     = explode('/', $oldNumber)[0];
        $romanMonths = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
        $issueDate   = Carbon::parse($request->issue_date);
        $dueDate     = $issueDate->copy()->addDays(14);
        $roman       = $romanMonths[(int) $issueDate->format('n') - 1];
        $year        = $issueDate->format('Y');
        $newNumber   = "{$seqPart}/{$invoice->projectCategory->code}/INV/MVC/{$roman}/{$year}";

        $invoice->update(['invoice_number' => "F-{$oldNumber}"]);

        $child = Invoice::create([
            'user_id'             => $invoice->user_id,
            'client_id'           => $invoice->client_id,
            'project_category_id' => $invoice->project_category_id,
            'document_issuer_id'  => $invoice->document_issuer_id,
            'bank_account_id'     => $invoice->bank_account_id,
            'signature_id'        => $invoice->signature_id,
            'email_template_group_id'   => $invoice->email_template_group_id,
            'with_signature'      => $invoice->with_signature,
            'attention'           => $invoice->attention,
            'notes'               => $invoice->notes,
            'invoice_number'      => $newNumber,
            'invoice_type'        => $invoice->invoice_type,
            'issue_date'          => $issueDate,
            'due_date'            => $dueDate,
            'payment_status'      => 'unpaid',
            'document_status'     => 'draft',
            'send_status'         => 'unsent',
            'tax_percentage'      => $invoice->tax_percentage,
            'discount_type'       => $invoice->discount_type,
            'discount_value'      => $invoice->discount_value,
            'is_dpp'              => $invoice->is_dpp,
            'interval_months'     => $request->interval_months,
            'parent_invoice_id'   => $invoice->id,
        ]);

        foreach ($invoice->items as $item) {
            $child->items()->create([
                'description' => $item->description,
                'amount'      => $item->amount,
                'discount'    => $item->discount,
                'sort_order'  => $item->sort_order,
            ]);
        }

        ActivityLogger::log('invoice.resumed', $child, [
            'from_frozen'    => $oldNumber,
            'frozen_renamed' => "F-{$oldNumber}",
            'new_number'     => $newNumber,
        ]);
        return back()->with('success', 'Invoice dilanjutkan. Draft baru telah dibuat.');
    }

    public function deactivate(Invoice $invoice)
    {
        if (!in_array($invoice->document_status, ['verified', 'inactive']) || $invoice->payment_status === 'paid') {
            return back()->with('error', 'Hanya invoice aktif atau overdue yang belum dibayar yang bisa dinonaktifkan.');
        }

        $invoice->update(['document_status' => 'inactive']);

        ActivityLogger::log('invoice.deactivated', $invoice, [
            'note' => 'Invoice dinonaktifkan oleh user',
        ]);

        return back()->with('success', 'Invoice berhasil dinonaktifkan.');
    }

    public function reactivate(Invoice $invoice)
    {
        if (preg_match('/^[CRPF]-/', $invoice->invoice_number)) {
            return back()->with('error', 'Reaktivasi hanya bisa dilakukan dari invoice utama (head).');
        }
        if ($invoice->payment_status !== 'unpaid' || $invoice->document_status !== 'inactive') {
            return back()->with('error', 'Hanya invoice nonaktif yang belum dibayar yang bisa direaktivasi.');
        }
        if (!$invoice->interval_months) {
            return back()->with('error', 'Invoice tidak memiliki interval perpanjangan.');
        }
        if ($invoice->children()->exists()) {
            return back()->with('error', 'Invoice sudah memiliki perpanjangan.');
        }

        $invoice->load('items', 'projectCategory');

        $now          = Carbon::now();
        $originalDate = Carbon::parse($invoice->issue_date);
        $originalDay  = $originalDate->day;
        $interval     = $invoice->interval_months;

        $headDate = Carbon::create($now->year, $now->month, 1);
        $headDate->setDay(min($originalDay, $headDate->daysInMonth));

        $oldNumber       = $invoice->invoice_number;
        $seqPart         = explode('/', $oldNumber)[0];
        $cursor          = $originalDate->copy()->addMonths($interval);
        $previousInvoice = $invoice;
        $generated       = [];

        DB::transaction(function () use ($invoice, $cursor, $headDate, $originalDay, $interval, $originalDate, $oldNumber, $seqPart, &$previousInvoice, &$generated) {
            while ($cursor->lte($headDate)) {
                $cursorDay = min($originalDay, $cursor->daysInMonth);
                $cursor->setDay($cursorDay);

                $dueDate     = $cursor->copy()->addDays(14);
                $isHead      = $cursor->format('Y-m') === $headDate->format('Y-m');
                $romanMonths = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
                $roman       = $romanMonths[(int) $cursor->format('n') - 1];
                $year        = $cursor->format('Y');
                $code        = $invoice->projectCategory->code;

                $number = $isHead
                    ? "{$seqPart}/{$code}/INV/MVC/{$roman}/{$year}"
                    : 'R-TEMP-' . uniqid();

                $child = Invoice::create([
                    'user_id'             => $invoice->user_id,
                    'client_id'           => $invoice->client_id,
                    'project_category_id' => $invoice->project_category_id,
                    'document_issuer_id'  => $invoice->document_issuer_id,
                    'bank_account_id'     => $invoice->bank_account_id,
                    'signature_id'        => $invoice->signature_id,
                    'email_template_group_id'   => $invoice->email_template_group_id,
                    'with_signature'      => $invoice->with_signature,
                    'attention'           => $invoice->attention,
                    'notes'               => $invoice->notes,
                    'invoice_number'      => $number,
                    'invoice_type'        => $invoice->invoice_type,
                    'issue_date'          => $cursor->toDateString(),
                    'due_date'            => $dueDate->toDateString(),
                    'payment_status'      => 'unpaid',
                    'document_status'     => $isHead ? 'draft' : 'verified',
                    'send_status'         => $isHead ? 'unsent' : 'send1',
                    'interval_months'     => $interval,
                    'parent_invoice_id'   => $previousInvoice->id,
                    'tax_percentage'      => $invoice->tax_percentage,
                    'discount_type'       => $invoice->discount_type,
                    'discount_value'      => $invoice->discount_value,
                    'is_dpp'              => $invoice->is_dpp,
                    'is_reaktivasi'       => true,
                ]);

                if (!$isHead) {
                    $child->update(['invoice_number' => "R-{$seqPart}/{$code}/INV/MVC/{$roman}/{$year}"]);
                }

                foreach ($invoice->items as $item) {
                    $child->items()->create([
                        'description' => $item->description,
                        'amount'      => $item->amount,
                        'discount'    => $item->discount ?? 0,
                        'sort_order'  => $item->sort_order,
                    ]);
                }

                $generated[]     = $child;
                $previousInvoice = $child;
                $cursor->addMonths($interval);
            }

            $head   = end($generated);
            $headId = $head->id;

            $invoice->update(['is_reaktivasi' => true, 'reaktivasi_chain_id' => $headId, 'invoice_number' => "R-{$oldNumber}"]);
            $nonHeadIds = collect($generated)->slice(0, -1)->pluck('id')->toArray();
            if (!empty($nonHeadIds)) {
                Invoice::whereIn('id', $nonHeadIds)->update(['reaktivasi_chain_id' => $headId]);
            }
        });

        $head           = end($generated);
        $backlogNumbers = collect($generated)->slice(0, -1)->pluck('invoice_number')->values()->toArray();
        ActivityLogger::log('invoice.reactivated', $invoice, [
            'original_number'    => $oldNumber,
            'renamed_to'         => "R-{$oldNumber}",
            'head_number'        => $head->invoice_number,
            'client'             => $invoice->client->company_name ?? '-',
            'total_dibuat'       => count($generated),
            'jumlah_per_invoice' => 'Rp ' . number_format($invoice->total, 0, ',', '.'),
            'total_semua'        => 'Rp ' . number_format($invoice->total * count($generated), 0, ',', '.'),
            'periode_dari'       => $originalDate->format('M Y'),
            'periode_sampai'     => Carbon::parse($head->issue_date)->format('M Y'),
            'backlog'            => $backlogNumbers,
        ]);

        return back()->with('success', 'Reaktivasi berhasil. ' . count($generated) . ' invoice dibuat — bayar di ' . $head->invoice_number . '.');
    }

    public function rollbackReaktivasi(Invoice $invoice)
    {
        // Hanya HEAD chain yang bisa di-rollback
        if (!$invoice->is_reaktivasi || $invoice->reaktivasi_chain_id !== null) {
            return back()->with('error', 'Rollback hanya bisa dilakukan dari invoice HEAD reaktivasi.');
        }

        $members = Invoice::where('reaktivasi_chain_id', $invoice->id)->get();

        // Blokir rollback kalau ada yang sudah lunas
        $hasPaid = $invoice->payment_status === 'paid'
            || $members->contains('payment_status', 'paid');
        if ($hasPaid) {
            return back()->with('error', 'Tidak bisa di-rollback — ada invoice dalam rantai yang sudah lunas.');
        }

        // Temukan invoice ORIGINAL: member yang parent_invoice_id-nya bukan bagian chain
        $chainIds   = $members->pluck('id')->push($invoice->id)->toArray();
        $original   = $members->first(fn($m) => !in_array($m->parent_invoice_id, $chainIds));
        $middleDels = $members->filter(fn($m) => $m->id !== ($original?->id));

        DB::transaction(function () use ($invoice, $middleDels, $original) {
            // Hapus HEAD
            $invoice->items()->delete();
            $invoice->delete();

            // Hapus middle R- (bukan original)
            foreach ($middleDels as $m) {
                $m->items()->delete();
                $m->delete();
            }

            // Restore original: strip prefix R-, reset flags
            if ($original) {
                $restoredNumber = preg_replace('/^R-/', '', $original->invoice_number);
                $original->update([
                    'invoice_number'      => $restoredNumber,
                    'is_reaktivasi'       => false,
                    'reaktivasi_chain_id' => null,
                ]);
            }
        });

        ActivityLogger::log('invoice.reaktivasi_rollback', $original ?? $invoice, [
            'restored_number' => $original ? preg_replace('/^R-/', '', $original->invoice_number) : null,
        ]);

        return back()->with('success', 'Reaktivasi di-rollback. Invoice dikembalikan ke kondisi semula.');
    }

    private function payCarriedAncestors(Invoice $invoice): void
    {
        if (!$invoice->carried_from_id) return;
        $carried = Invoice::find($invoice->carried_from_id);
        if ($carried && $carried->document_status === 'carried') {
            $carried->update(['payment_status' => 'paid']);
            ActivityLogger::log('invoice.status_changed', $carried, ['from' => 'carried', 'to' => 'paid', 'type' => 'payment']);
            $this->payCarriedAncestors($carried);
        }
    }

    private function payPrepayChain(Invoice $invoice): void
    {
        if (!$invoice->is_prepay || $invoice->prepay_chain_id !== null) return;

        $members = Invoice::where('prepay_chain_id', $invoice->id)->get();
        foreach ($members as $member) {
            if ($member->payment_status !== 'paid') {
                $member->update(['payment_status' => 'paid']);
                ActivityLogger::log('invoice.status_changed', $member, ['to' => 'paid', 'note' => 'cascade prepay']);
            }
        }
    }

    private function payReaktivasiChain(Invoice $invoice): void
    {
        if (!$invoice->is_reaktivasi || $invoice->reaktivasi_chain_id) return;

        $members = Invoice::where('reaktivasi_chain_id', $invoice->id)->get();
        foreach ($members as $member) {
            if ($member->payment_status !== 'paid') {
                $member->update(['payment_status' => 'paid']);
                ActivityLogger::log('invoice.status_changed', $member, ['to' => 'paid', 'note' => 'cascade reaktivasi']);
            }
        }
    }

    private function generateRecurring(Invoice $parent): void
    {
        $parent->load('items', 'projectCategory');

        $issueDate = Carbon::parse($parent->issue_date)->addMonths($parent->interval_months);
        $dueDate   = $issueDate->copy()->addDays(14);
        $number    = Invoice::generateNumber($parent->projectCategory->code, $issueDate, $parent->invoice_type ?? 'monthly');

        $child = Invoice::create([
            'user_id'             => $parent->user_id,
            'client_id'           => $parent->client_id,
            'project_category_id' => $parent->project_category_id,
            'document_issuer_id'  => $parent->document_issuer_id,
            'bank_account_id'     => $parent->bank_account_id,
            'signature_id'        => $parent->signature_id,
            'email_template_group_id'   => $parent->email_template_group_id,
            'with_signature'      => $parent->with_signature,
            'attention'           => $parent->attention,
            'notes'               => $parent->notes,
            'invoice_number'      => $number,
            'invoice_type'        => $parent->invoice_type ?? 'monthly',
            'issue_date'          => $issueDate,
            'due_date'            => $dueDate,
            'payment_status'      => 'unpaid',
            'document_status'     => 'draft',
            'send_status'         => 'unsent',
            'tax_percentage'      => $parent->tax_percentage,
            'discount_type'       => $parent->discount_type,
            'discount_value'      => $parent->discount_value,
            'is_dpp'              => $parent->is_dpp,
            'interval_months'     => $parent->interval_months,
            'parent_invoice_id'   => $parent->id,
            'spk_id'              => $parent->spk_id,
            'spk_number'          => $parent->spk_number,
        ]);

        foreach ($parent->items as $item) {
            $child->items()->create([
                'description' => $item->description,
                'amount'      => $item->amount,
                'discount'    => $item->discount,
                'sort_order'  => $item->sort_order,
            ]);
        }

        ActivityLogger::log('invoice.recurring_created', $child, ['parent' => $parent->invoice_number]);
    }

    private function sendPaidEmail(Invoice $invoice): void
    {
        try {
            $invoice->load([
                'client.emails', 'projectCategory', 'documentIssuer',
                'bankAccount', 'signature', 'items', 'user',
                'carriedFrom.items', 'reaktivasiChain.items', 'prepayChain.items',
                'emailTemplateGroup',
            ]);

            $group = $invoice->emailTemplateGroup
                ?? \App\Models\EmailTemplateGroup::where('is_default', true)->first()
                ?? \App\Models\EmailTemplateGroup::orderBy('id')->first();

            if (! $group) return;

            $tpl = $group->getStage('paid');
            if (empty($tpl['subject']) && empty($tpl['body'])) return;

            $emails = $invoice->client?->emails->pluck('email')->toArray() ?? [];
            if (empty($emails)) return;

            $subject = $this->renderTemplateVars($tpl['subject'], $invoice);
            $body    = $this->renderTemplateVars($tpl['body'],    $invoice);

            $html       = view('invoices.receipt', ['invoice' => $invoice, 'imgB64' => fn($u) => $this->urlToBase64($u)])->render();
            $pdfBase64  = app(\App\Services\PdfService::class)->generate($html);
            $filename   = 'RECEIPT-' . str_replace('/', '-', $invoice->invoice_number) . '.pdf';

            $toList    = array_map(fn($e) => ['email' => $e], $emails);
            $htmlEmail = view('emails.wrapper', ['invoice' => $invoice, 'body' => $body, 'filename' => $filename])->render();

            \Illuminate\Support\Facades\Http::withHeaders([
                'api-key'      => config('services.brevo.key'),
                'Content-Type' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', [
                'sender'      => [
                    'name'  => config('services.brevo.sender_name'),
                    'email' => config('services.brevo.sender_email'),
                ],
                'to'          => $toList,
                'subject'     => $subject,
                'htmlContent' => $htmlEmail,
                'attachment'  => [[
                    'content' => $pdfBase64,
                    'name'    => $filename,
                ]],
            ]);

            ActivityLogger::log('invoice.email_sent', $invoice, ['to' => $emails, 'stage' => 'paid']);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('sendPaidEmail failed', [
                'invoice' => $invoice->invoice_number,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    private function renderTemplateVars(?string $text, Invoice $invoice, string $stage = 'paid'): string
    {
        if (! $text) return '';

        $fmtDate = fn($d) => $d instanceof \Carbon\Carbon
            ? $d->locale('id')->translatedFormat('j F Y')
            : ($d ? \Carbon\Carbon::parse($d)->locale('id')->translatedFormat('j F Y') : '');

        $fmtRp = fn($v) => $v !== null ? 'Rp ' . number_format((float) $v, 0, ',', '.') : '';

        $vars = [
            '{{invoice_number}}'      => $invoice->invoice_number ?? '',
            '{{issue_date}}'          => $fmtDate($invoice->issue_date),
            '{{due_date}}'            => $fmtDate($invoice->due_date),
            '{{amount}}'              => $fmtRp($invoice->total ?? 0),
            '{{project_name}}'        => $invoice->projectCategory?->name ?? '',
            '{{client_name}}'         => $invoice->client?->company_name ?? '',
            '{{director}}'            => $invoice->client?->director ?? '',
            '{{pic}}'                 => $invoice->client?->pic ?? '',
            '{{client_city}}'         => $invoice->client?->city ?? '',
            '{{issuer_name}}'         => $invoice->documentIssuer?->name ?? '',
            '{{bank_name}}'           => $invoice->bankAccount?->bank_name ?? '',
            '{{bank_account_number}}' => $invoice->bankAccount?->account_number ?? '',
            '{{bank_holder}}'         => $invoice->bankAccount?->name ?? '',
            '{{send_stage}}'          => $stage,
        ];

        return str_replace(array_keys($vars), array_values($vars), $text);
    }

    private function urlToBase64(?string $url): ?string
    {
        if (! $url) return null;
        try {
            if (str_starts_with($url, '/storage/')) {
                $filePath = storage_path('app/public/' . substr($url, 9));
                if (file_exists($filePath)) {
                    $content = file_get_contents($filePath);
                    $mime    = (new \finfo(FILEINFO_MIME_TYPE))->buffer($content);
                    return "data:{$mime};base64," . base64_encode($content);
                }
            }
            $appUrl = rtrim(config('app.url'), '/');
            if (str_starts_with($url, $appUrl)) {
                $path     = ltrim(substr($url, strlen($appUrl)), '/');
                $filePath = str_starts_with($path, 'storage/')
                    ? storage_path('app/public/' . substr($path, 8))
                    : public_path($path);
                if (file_exists($filePath)) {
                    $content = file_get_contents($filePath);
                    $mime    = (new \finfo(FILEINFO_MIME_TYPE))->buffer($content);
                    return "data:{$mime};base64," . base64_encode($content);
                }
            }
            $content = @file_get_contents($url);
            if (! $content) return null;
            $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($content);
            return "data:{$mime};base64," . base64_encode($content);
        } catch (\Throwable) {
            return null;
        }
    }
}
