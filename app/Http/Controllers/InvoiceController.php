<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Client;
use App\Models\DocumentIssuer;
use App\Models\Invoice;
use App\Models\ProjectCategory;
use App\Models\Signature;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $now      = Carbon::now();
        $month    = $request->integer('month', $now->month);
        $year     = $request->integer('year',  $now->year);
        $status   = $request->input('status');
        $clientId = $request->input('client_id');

        $query = Invoice::with(['client', 'projectCategory', 'user'])
            ->withSum('items', 'amount')
            ->where('user_id', auth()->id())
            ->whereYear('issue_date',  $year)
            ->whereMonth('issue_date', $month)
            ->orderBy('due_date');

        if ($status)   $query->where('status', $status);
        if ($clientId) $query->where('client_id', $clientId);

        return Inertia::render('Invoices/Index', [
            'invoices' => $query->get(),
            'clients'  => Client::where('is_active', true)->get(['id', 'company_name']),
            'filters'  => [
                'month'     => $month,
                'year'      => $year,
                'status'    => $status ?? '',
                'client_id' => $clientId ?? '',
            ],
        ]);
    }

    public function create(Request $request)
    {
        $fromInvoice = null;
        if ($request->filled('from')) {
            $fromInvoice = Invoice::with('items')->findOrFail($request->from);

            if ($fromInvoice->status !== 'paid') {
                return redirect()->route('invoices.show', $fromInvoice->id)
                    ->with('error', "Invoice {$fromInvoice->invoice_number} belum lunas. Lunasi dulu sebelum perpanjang.");
            }
        }

        return Inertia::render('Invoices/Create', [
            'clients'          => Client::where('is_active', true)->get(['id', 'company_name', 'address', 'city', 'pic']),
            'projectCategories'=> ProjectCategory::all(['id', 'name', 'code']),
            'documentIssuers'  => DocumentIssuer::all(['id', 'name']),
            'bankAccounts'     => BankAccount::all(['id', 'name', 'bank_name', 'account_number']),
            'signatures'       => Signature::all(['id', 'name', 'position']),
            'fromInvoice'      => $fromInvoice,
        ]);
    }

    public function store(Request $request)
    {
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
            'status'              => 'required|in:draft,sent,paid,unpaid',
            'items'               => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.amount'      => 'required|numeric|min:0',
        ]);

        $category    = ProjectCategory::findOrFail($validated['project_category_id']);
        $issueDate   = Carbon::parse($validated['issue_date']);
        $invoiceNumber = Invoice::generateNumber($category->code, $issueDate);

        $invoice = Invoice::create([
            ...$validated,
            'user_id'        => auth()->id(),
            'invoice_number' => $invoiceNumber,
            'with_signature' => $request->boolean('with_signature'),
        ]);

        foreach ($validated['items'] as $i => $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'amount'      => $item['amount'],
                'sort_order'  => $i,
            ]);
        }

        return redirect()->route('invoices.index')->with('success', 'Invoice berhasil dibuat.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load([
            'client', 'projectCategory', 'documentIssuer',
            'bankAccount', 'signature', 'items', 'user',
        ]);

        return Inertia::render('Invoices/Show', [
            'invoice' => array_merge($invoice->toArray(), ['total' => $invoice->total]),
        ]);
    }

    public function updateItems(Request $request, Invoice $invoice)
    {
        $request->validate([
            'items'               => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.amount'      => 'required|numeric|min:0',
        ]);

        $invoice->items()->delete();
        foreach ($request->items as $i => $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'amount'      => $item['amount'],
                'sort_order'  => $i,
            ]);
        }

        return back()->with('success', 'Item invoice diperbarui.');
    }

    public function edit(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Invoice yang sudah lunas tidak dapat diedit.');
        }

        $invoice->load('items', 'projectCategory');

        return Inertia::render('Invoices/Edit', [
            'invoice'          => array_merge($invoice->toArray(), [
                'items' => $invoice->items->toArray(),
                'total' => $invoice->total,
            ]),
            'clients'          => Client::where('is_active', true)->get(['id', 'company_name', 'address', 'city', 'pic']),
            'projectCategories'=> ProjectCategory::all(['id', 'name', 'code']),
            'documentIssuers'  => DocumentIssuer::all(['id', 'name']),
            'bankAccounts'     => BankAccount::all(['id', 'name', 'bank_name', 'account_number']),
            'signatures'       => Signature::all(['id', 'name', 'position']),
        ]);
    }

    public function update(Request $request, Invoice $invoice)
    {
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
            'status'              => 'required|in:draft,sent,paid,unpaid',
        ]);

        // Regenerate invoice number jika issue_date atau project_category berubah
        $dateChanged     = $invoice->issue_date->format('Y-m') !== Carbon::parse($validated['issue_date'])->format('Y-m');
        $categoryChanged = $invoice->project_category_id != $validated['project_category_id'];

        if ($dateChanged || $categoryChanged) {
            $category    = ProjectCategory::findOrFail($validated['project_category_id']);
            $issueDate   = Carbon::parse($validated['issue_date']);
            // Exclude invoice ini dari hitungan supaya tidak loncat nomor
            $count = Invoice::whereYear('issue_date', $issueDate->year)
                ->whereMonth('issue_date', $issueDate->month)
                ->where('id', '!=', $invoice->id)
                ->count();
            $romanMonths = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
            $seq = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
            $validated['invoice_number'] = "{$seq}/{$category->code}/INV/MVC/{$romanMonths[$issueDate->month - 1]}/{$issueDate->year}";
        }

        $invoice->update([
            ...$validated,
            'with_signature' => $request->boolean('with_signature'),
        ]);

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice berhasil diperbarui.');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return back()->with('success', 'Invoice berhasil dihapus.');
    }

    public function updateStatus(Request $request, Invoice $invoice)
    {
        $request->validate(['status' => 'required|in:draft,sent,paid,unpaid']);
        $invoice->update(['status' => $request->status]);
        return back()->with('success', 'Status diperbarui.');
    }
}
