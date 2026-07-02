<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Client;
use App\Models\DocumentIssuer;
use App\Models\EmailTemplate;
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
    public function index()
    {
        $clients = Client::where('is_active', true)
            ->with(['category', 'invoices' => fn($q) => $q->with('items')])
            ->withCount([
                'invoices as draft_count'   => fn($q) => $q->where('status', 'draft'),
                'invoices as sent_count'    => fn($q) => $q->where('status', 'sent'),
                'invoices as paid_count'    => fn($q) => $q->where('status', 'paid'),
                'invoices as unpaid_count'  => fn($q) => $q->where('status', 'unpaid'),
                'invoices as overdue_count' => fn($q) => $q->whereNotIn('status', ['paid', 'frozen'])->where('due_date', '<', now()),
            ])
            ->withMax('invoices', 'issue_date')
            ->orderBy('company_name')
            ->get();

        $clients->each(function ($c) {
            $c->product_type_count = $c->invoices->pluck('project_category_id')->unique()->filter()->count();
            $c->unpaid_amount      = (float) $c->invoices->whereIn('status', ['sent', 'unpaid'])->sum(fn($inv) => $inv->total);
            $c->total_amount       = (float) $c->invoices->sum(fn($inv) => $inv->total);
        });

        $summary = [
            'total_outstanding' => $clients->sum('unpaid_amount'),
            'total_overdue'     => $clients->sum('overdue_count'),
            'total_unpaid'      => $clients->sum('sent_count'),
            'total_invoices'    => $clients->sum(fn($c) => ($c->draft_count + $c->sent_count + $c->paid_count + $c->unpaid_count)),
        ];

        return Inertia::render('Invoices/Index', compact('clients', 'summary'));
    }

    public function schedule(Request $request)
    {
        $now  = Carbon::now();
        $month = $request->integer('month', $now->month);
        $year  = $request->integer('year',  $now->year);

        // Invoice yang issue_date = bulan berikutnya dari bulan referensi
        $next = Carbon::create($year, $month, 1)->addMonth();

        $with = ['client.emails', 'projectCategory', 'user', 'bankAccount', 'documentIssuer', 'emailTemplate'];

        $priorityInvoices = Invoice::with($with)
            ->withSum('items', 'amount')
            ->where('user_id', auth()->id())
            ->where('status', '!=', 'frozen')
            ->whereYear('issue_date',  $next->year)
            ->whereMonth('issue_date', $next->month)
            ->orderBy('due_date')
            ->get();

        $otherInvoices = Invoice::with($with)
            ->withSum('items', 'amount')
            ->where('user_id', auth()->id())
            ->where('status', '!=', 'frozen')
            ->where(fn($q) => $q
                ->whereYear('issue_date', '!=', $next->year)
                ->orWhereMonth('issue_date', '!=', $next->month)
            )
            ->orderByDesc('issue_date')
            ->get();

        return Inertia::render('Invoices/ScheduleInvoice', [
            'priorityInvoices' => $priorityInvoices,
            'otherInvoices'    => $otherInvoices,
            'nextMonthLabel'   => $next->translatedFormat('F Y'),
            'filters'          => ['month' => $month, 'year' => $year],
            'emailTemplates'   => EmailTemplate::orderBy('name')->get(['id', 'name', 'subject', 'body', 'is_default']),
        ]);
    }

    public function clientInvoices(Client $client)
    {
        $client->load('category', 'emails');

        $invoices = Invoice::with(['projectCategory', 'documentIssuer', 'user', 'emailTemplate'])
            ->withSum('items', 'amount')
            ->withSum('items', 'discount')
            ->where('client_id', $client->id)
            ->orderByDesc('issue_date')
            ->get();

        return Inertia::render('Invoices/ClientInvoices', [
            'client'   => $client->toArray(),
            'invoices' => $invoices,
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
            'emailTemplates'   => EmailTemplate::orderBy('name')->get(['id', 'name', 'is_default']),
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
            'email_template_id'   => 'nullable|exists:email_templates,id',
            'with_signature'      => 'boolean',
            'spk_number'          => 'nullable|string|max:100',
            'issue_date'          => 'required|date',
            'due_date'            => 'required|date|after_or_equal:issue_date',
            'attention'           => 'nullable|string|max:255',
            'notes'               => 'nullable|string',
            'status'              => 'required|in:draft,sent,paid,unpaid',
            'tax_percentage'      => 'nullable|numeric|min:0|max:100',
            'interval_months'     => 'nullable|integer|min:1|max:12',
        ]);

        $category      = ProjectCategory::findOrFail($validated['project_category_id']);
        $issueDate     = Carbon::parse($validated['issue_date']);
        $invoiceNumber = Invoice::generateNumber($category->code, $issueDate);

        $invoice = Invoice::create([
            ...$validated,
            'user_id'        => auth()->id(),
            'invoice_number' => $invoiceNumber,
            'with_signature' => $request->boolean('with_signature'),
        ]);

        ActivityLogger::log('invoice.created', $invoice, ['client' => $invoice->client?->company_name]);

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice dibuat. Silakan tambahkan item.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load([
            'client.emails', 'projectCategory', 'documentIssuer',
            'bankAccount', 'signature', 'items', 'user', 'emailTemplate',
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
            'emailTemplates' => EmailTemplate::orderBy('name')->get(['id', 'name', 'subject', 'body', 'is_default']),
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
            'tax_percentage'      => 'nullable|numeric|min:0|max:100',
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

        ActivityLogger::log('invoice.updated', $invoice);

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice berhasil diperbarui.');
    }

    public function destroy(Invoice $invoice)
    {
        ActivityLogger::log('invoice.deleted', $invoice);
        $invoice->delete();
        return back()->with('success', 'Invoice berhasil dihapus.');
    }

    public function sendEmail(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'emails'  => 'required|array|min:1',
            'emails.*'=> 'required|email',
            'subject' => 'required|string|max:255',
            'body'    => 'required|string',
        ]);

        $invoice->load(['client', 'projectCategory', 'documentIssuer',
                        'bankAccount', 'signature', 'items', 'user', 'carriedFrom.items']);

        $html = view('invoices.pdf', [
            'invoice' => $invoice,
            'imgB64'  => fn($url) => $this->urlToBase64($url),
        ])->render();

        $pdfBase64 = app(\App\Services\PdfService::class)->generate($html);
        $filename  = str_replace('/', '-', $invoice->invoice_number) . '.pdf';

        $toList = array_map(fn($e) => ['email' => $e], $validated['emails']);

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'api-key'      => config('services.brevo.key'),
            'Content-Type' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender'      => [
                'name'  => config('services.brevo.sender_name'),
                'email' => config('services.brevo.sender_email'),
            ],
            'to'          => $toList,
            'subject'     => $validated['subject'],
            'textContent' => $validated['body'],
            'attachment'  => [[
                'content' => $pdfBase64,
                'name'    => $filename,
            ]],
        ]);

        if (! $response->successful()) {
            return back()->with('error', 'Gagal mengirim email: ' . $response->json('message', 'Unknown error'));
        }

        $invoice->update(['status' => 'sent', 'is_marked' => false]);

        ActivityLogger::log('invoice.email_sent', $invoice, ['to' => $validated['emails']]);

        $emailList = implode(', ', $validated['emails']);

        return back()->with('success', "Invoice berhasil dikirim ke {$emailList}.");
    }

    public function download(Invoice $invoice)
    {
        $invoice->load([
            'client', 'projectCategory', 'documentIssuer',
            'bankAccount', 'signature', 'items', 'user', 'carriedFrom.items',
            'reaktivasiChain.items',
        ]);

        $html      = view('invoices.pdf', ['invoice' => $invoice, 'imgB64' => fn($url) => $this->urlToBase64($url)])->render();
        $pdf       = base64_decode(app(\App\Services\PdfService::class)->generate($html));
        $filename  = str_replace('/', '-', $invoice->invoice_number) . '.pdf';

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
            'reaktivasiChain.items',
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
            'reaktivasiChain.items',
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

        $validStatuses = ['draft', 'sent', 'paid', 'unpaid', 'frozen', 'carried'];
        $statuses = array_values(array_filter(
            (array) $request->input('status', []),
            fn($s) => in_array($s, $validStatuses)
        ));

        $query = Invoice::with(['client', 'projectCategory', 'items'])
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()]);

        if (!empty($statuses)) {
            $query->whereIn('status', $statuses);
        }

        $invoices = $query->orderBy('issue_date')->orderBy('invoice_number')->get();

        $statusLabels = [
            'draft'   => 'Draft',
            'sent'    => 'Terkirim',
            'paid'    => 'Lunas',
            'unpaid'  => 'Belum Dibayar',
            'frozen'  => 'Dibekukan',
            'carried' => 'Carry',
        ];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Invoice');

        // Header style
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
            'G' => ['Status',           14],
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

        // Number format
        $numFmt = '#,##0';
        $altFill = ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'EEEEFF']]];

        foreach ($invoices as $i => $inv) {
            $row  = $i + 2;
            $odd  = $i % 2 !== 0;

            $sheet->setCellValue("A{$row}", $i + 1);
            $sheet->setCellValue("B{$row}", $inv->invoice_number);
            $sheet->setCellValue("C{$row}", $inv->client?->company_name ?? '-');
            $sheet->setCellValue("D{$row}", $inv->projectCategory?->name ?? '-');
            $sheet->setCellValue("E{$row}", $inv->issue_date->format('d/m/Y'));
            $sheet->setCellValue("F{$row}", $inv->due_date->format('d/m/Y'));
            $sheet->setCellValue("G{$row}", $statusLabels[$inv->status] ?? $inv->status);
            $sheet->setCellValue("H{$row}", (int) $inv->subtotal);
            $sheet->setCellValue("I{$row}", (int) $inv->discount_amount);
            $sheet->setCellValue("J{$row}", (int) $inv->dpp_base);
            $sheet->setCellValue("K{$row}", $inv->tax_percentage ?? 0);
            $sheet->setCellValue("L{$row}", (int) $inv->tax_amount);
            $sheet->setCellValue("M{$row}", (int) $inv->total);

            foreach (['H', 'I', 'J', 'L', 'M'] as $numCol) {
                $sheet->getStyle("{$numCol}{$row}")->getNumberFormat()->setFormatCode($numFmt);
            }

            if ($odd) {
                $sheet->getStyle("A{$row}:M{$row}")->applyFromArray($altFill);
            }
        }

        // Freeze header row
        $sheet->freezePane('A2');

        // Auto filter
        $lastRow = count($invoices) + 1;
        $sheet->setAutoFilter("A1:M{$lastRow}");

        // Bold total column
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
        $invoice->update(['is_marked' => ! $invoice->is_marked]);
        ActivityLogger::log($invoice->is_marked ? 'invoice.marked' : 'invoice.unmarked', $invoice);
        return back()->with('success', $invoice->is_marked ? 'Invoice ditandai.' : 'Tanda dihapus.');
    }

    public function updateInterval(Request $request, Invoice $invoice)
    {
        $request->validate(['interval_months' => 'nullable|integer|min:1|max:12']);
        $invoice->update(['interval_months' => $request->interval_months]);
        ActivityLogger::log('invoice.interval_changed', $invoice, [
            'interval' => $request->interval_months,
        ]);
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
        $request->validate(['status' => 'required|in:draft,sent,paid,unpaid']);
        $oldStatus = $invoice->status;
        $invoice->update(['status' => $request->status]);
        $invoice->refresh();

        ActivityLogger::log('invoice.status_changed', $invoice, [
            'from' => $oldStatus,
            'to'   => $request->status,
        ]);

        if ($request->status === 'paid') {
            $this->payCarriedAncestors($invoice);
            $this->payReaktivasiChain($invoice);

            if ($invoice->interval_months && $invoice->children()->doesntExist()) {
                $this->generateRecurring($invoice);
                return back()->with('success', 'Status diperbarui. Invoice perpanjangan ' . $invoice->interval_months . ' bulan ke depan dibuat sebagai draft.');
            }
        }

        return back()->with('success', 'Status diperbarui.');
    }

    public function carry(Invoice $invoice)
    {
        if (!in_array($invoice->status, ['sent', 'unpaid'])) {
            return back()->with('error', 'Hanya invoice sent atau unpaid yang bisa di-carry.');
        }
        if (!$invoice->interval_months) {
            return back()->with('error', 'Invoice tidak memiliki interval perpanjangan.');
        }
        if ($invoice->children()->exists()) {
            return back()->with('error', 'Invoice sudah memiliki perpanjangan.');
        }

        $invoice->load('items', 'projectCategory');

        $issueDate = Carbon::parse($invoice->issue_date)->addMonths($invoice->interval_months);
        $dueDate   = $issueDate->copy()->addDays(14);
        $number    = Invoice::generateNumber($invoice->projectCategory->code, $issueDate);

        $child = Invoice::create([
            'user_id'             => $invoice->user_id,
            'client_id'           => $invoice->client_id,
            'project_category_id' => $invoice->project_category_id,
            'document_issuer_id'  => $invoice->document_issuer_id,
            'bank_account_id'     => $invoice->bank_account_id,
            'signature_id'        => $invoice->signature_id,
            'email_template_id'   => $invoice->email_template_id,
            'with_signature'      => $invoice->with_signature,
            'attention'           => $invoice->attention,
            'notes'               => $invoice->notes,
            'invoice_number'      => $number,
            'issue_date'          => $issueDate,
            'due_date'            => $dueDate,
            'status'              => 'draft',
            'tax_percentage'      => $invoice->tax_percentage,
            'discount_type'       => $invoice->discount_type,
            'discount_value'      => $invoice->discount_value,
            'is_dpp'              => $invoice->is_dpp,
            'interval_months'     => $invoice->interval_months,
            'parent_invoice_id'   => $invoice->id,
            'carried_from_id'     => $invoice->id,
        ]);

        foreach ($invoice->items as $item) {
            $child->items()->create([
                'description' => $item->description,
                'quantity'    => $item->quantity,
                'unit'        => $item->unit,
                'unit_price'  => $item->unit_price,
                'amount'      => $item->amount,
                'discount'    => $item->discount ?? 0,
                'sort_order'  => $item->sort_order,
            ]);
        }

        $invoice->update(['status' => 'carried']);

        ActivityLogger::log('invoice.carried', $invoice, ['carried_to' => $child->invoice_number]);
        return back()->with('success', 'Invoice di-carry. Tunggakan akan masuk ke invoice berikutnya.');
    }

    public function freeze(Invoice $invoice)
    {
        abort_if(!in_array($invoice->status, ['draft', 'sent']), 403, 'Hanya draft atau sent yang bisa dibekukan.');
        $invoice->update(['status' => 'frozen']);
        ActivityLogger::log('invoice.frozen', $invoice);
        return back()->with('success', 'Invoice dibekukan.');
    }

    public function resume(Request $request, Invoice $invoice)
    {
        if ($invoice->status !== 'frozen') {
            return back()->with('error', 'Invoice tidak dalam status frozen.');
        }

        if ($invoice->children()->exists()) {
            return back()->with('error', 'Perpanjangan sudah dilanjutkan sebelumnya.');
        }

        $request->validate([
            'issue_date'      => 'required|date',
            'interval_months' => 'required|integer|min:1|max:36',
        ]);

        $invoice->load('items', 'projectCategory');

        $issueDate = Carbon::parse($request->issue_date);
        $dueDate   = $issueDate->copy()->addDays(14);
        $number    = Invoice::generateNumber($invoice->projectCategory->code, $issueDate);

        $child = Invoice::create([
            'user_id'             => $invoice->user_id,
            'client_id'           => $invoice->client_id,
            'project_category_id' => $invoice->project_category_id,
            'document_issuer_id'  => $invoice->document_issuer_id,
            'bank_account_id'     => $invoice->bank_account_id,
            'signature_id'        => $invoice->signature_id,
            'email_template_id'   => $invoice->email_template_id,
            'with_signature'      => $invoice->with_signature,
            'attention'           => $invoice->attention,
            'notes'               => $invoice->notes,
            'invoice_number'      => $number,
            'issue_date'          => $issueDate,
            'due_date'            => $dueDate,
            'status'              => 'draft',
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

        ActivityLogger::log('invoice.resumed', $child, ['from_frozen' => $invoice->invoice_number]);
        return back()->with('success', 'Invoice dilanjutkan. Draft baru telah dibuat.');
    }

    public function reactivate(Invoice $invoice)
    {
        if ($invoice->status !== 'unpaid') {
            return back()->with('error', 'Hanya invoice unpaid yang bisa direaktivasi.');
        }
        if (!$invoice->interval_months) {
            return back()->with('error', 'Invoice tidak memiliki interval perpanjangan.');
        }
        if ($invoice->children()->exists()) {
            return back()->with('error', 'Invoice sudah memiliki perpanjangan.');
        }

        $invoice->load('items', 'projectCategory');

        $now              = Carbon::now();
        $originalDate     = Carbon::parse($invoice->issue_date);
        $originalDay      = $originalDate->day;
        $interval         = $invoice->interval_months;

        // Head = bulan ini, pakai hari yang sama dengan invoice asli
        $headDate = Carbon::create($now->year, $now->month, 1);
        $headDate->setDay(min($originalDay, $headDate->daysInMonth));

        $cursor          = $originalDate->copy()->addMonths($interval);
        $previousInvoice = $invoice;
        $generated       = [];

        DB::transaction(function () use ($invoice, $cursor, $headDate, $originalDay, $interval, $originalDate, &$previousInvoice, &$generated) {
        while ($cursor->lte($headDate)) {
            $cursorDay = min($originalDay, $cursor->daysInMonth);
            $cursor->setDay($cursorDay);

            $dueDate = $cursor->copy()->addDays(14);
            $number  = Invoice::generateNumber($invoice->projectCategory->code, $cursor);
            $isHead  = $cursor->format('Y-m') === $headDate->format('Y-m');

            $child = Invoice::create([
                'user_id'             => $invoice->user_id,
                'client_id'           => $invoice->client_id,
                'project_category_id' => $invoice->project_category_id,
                'document_issuer_id'  => $invoice->document_issuer_id,
                'bank_account_id'     => $invoice->bank_account_id,
                'signature_id'        => $invoice->signature_id,
                'email_template_id'   => $invoice->email_template_id,
                'with_signature'      => $invoice->with_signature,
                'attention'           => $invoice->attention,
                'notes'               => $invoice->notes,
                'invoice_number'      => $number,
                'issue_date'          => $cursor->toDateString(),
                'due_date'            => $dueDate->toDateString(),
                'status'              => $isHead ? 'draft' : 'unpaid',
                'interval_months'     => $interval,
                'parent_invoice_id'   => $previousInvoice->id,
                'tax_percentage'      => $invoice->tax_percentage,
                'discount_type'       => $invoice->discount_type,
                'discount_value'      => $invoice->discount_value,
                'is_dpp'              => $invoice->is_dpp,
                'is_reaktivasi'       => true,
            ]);

            foreach ($invoice->items as $item) {
                $child->items()->create([
                    'description' => $item->description,
                    'quantity'    => $item->quantity,
                    'unit'        => $item->unit,
                    'unit_price'  => $item->unit_price,
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

        // Mark original + all non-head generated invoices with reaktivasi_chain_id
        $invoice->update(['is_reaktivasi' => true, 'reaktivasi_chain_id' => $headId]);
        $nonHeadIds = collect($generated)->slice(0, -1)->pluck('id')->toArray();
        if (!empty($nonHeadIds)) {
            Invoice::whereIn('id', $nonHeadIds)->update(['reaktivasi_chain_id' => $headId]);
        }
        }); // end DB::transaction

        $head = end($generated);
        ActivityLogger::log('invoice.reactivated', $invoice, [
            'chain_head'  => $head->invoice_number,
            'chain_count' => count($generated),
            'from'        => $originalDate->format('Y-m'),
            'to'          => Carbon::parse($head->issue_date)->format('Y-m'),
        ]);

        return back()->with('success', 'Reaktivasi berhasil. ' . count($generated) . ' invoice dibuat — bayar di ' . $head->invoice_number . '.');
    }

    private function payCarriedAncestors(Invoice $invoice): void
    {
        if (!$invoice->carried_from_id) return;
        $carried = Invoice::find($invoice->carried_from_id);
        if ($carried && $carried->status === 'carried') {
            $carried->update(['status' => 'paid']);
            ActivityLogger::log('invoice.status_changed', $carried, ['from' => 'carried', 'to' => 'paid']);
            $this->payCarriedAncestors($carried);
        }
    }

    private function payReaktivasiChain(Invoice $invoice): void
    {
        // Only cascade if this is the reaktivasi head (is_reaktivasi = true, no chain_id = it IS the head)
        if (!$invoice->is_reaktivasi || $invoice->reaktivasi_chain_id) return;

        $members = Invoice::where('reaktivasi_chain_id', $invoice->id)->get();
        foreach ($members as $member) {
            if ($member->status !== 'paid') {
                $oldStatus = $member->status;
                $member->update(['status' => 'paid']);
                ActivityLogger::log('invoice.status_changed', $member, ['from' => $oldStatus, 'to' => 'paid', 'note' => 'cascade reaktivasi']);
            }
        }
    }

    private function generateRecurring(Invoice $parent): void
    {
        $parent->load('items', 'projectCategory');

        // issue = issue_date parent + interval (maju N bulan dari tanggal mulai)
        $issueDate = Carbon::parse($parent->issue_date)->addMonths($parent->interval_months);
        // due = issue baru + interval - 1 hari
        $dueDate = $issueDate->copy()->addDays(14);

        $number = Invoice::generateNumber($parent->projectCategory->code, $issueDate);

        $child = Invoice::create([
            'user_id'             => $parent->user_id,
            'client_id'           => $parent->client_id,
            'project_category_id' => $parent->project_category_id,
            'document_issuer_id'  => $parent->document_issuer_id,
            'bank_account_id'     => $parent->bank_account_id,
            'signature_id'        => $parent->signature_id,
            'email_template_id'   => $parent->email_template_id,
            'with_signature'      => $parent->with_signature,
            'attention'           => $parent->attention,
            'notes'               => $parent->notes,
            'invoice_number'      => $number,
            'issue_date'          => $issueDate,
            'due_date'            => $dueDate,
            'status'              => 'draft',
            'tax_percentage'      => $parent->tax_percentage,
            'discount_type'       => $parent->discount_type,
            'discount_value'      => $parent->discount_value,
            'is_dpp'              => $parent->is_dpp,
            'interval_months'     => $parent->interval_months,
            'parent_invoice_id'   => $parent->id,
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

    private function urlToBase64(?string $url): ?string
    {
        if (! $url) return null;
        try {
            // Path relatif /storage/xxx → filesystem langsung
            if (str_starts_with($url, '/storage/')) {
                $filePath = storage_path('app/public/' . substr($url, 9));
                if (file_exists($filePath)) {
                    $content = file_get_contents($filePath);
                    $mime    = (new \finfo(FILEINFO_MIME_TYPE))->buffer($content);
                    return "data:{$mime};base64," . base64_encode($content);
                }
            }
            // URL absolut lokal
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
            // External URL
            $content = @file_get_contents($url);
            if (! $content) return null;
            $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($content);
            return "data:{$mime};base64," . base64_encode($content);
        } catch (\Throwable) {
            return null;
        }
    }
}
