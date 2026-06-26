<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Client;
use App\Models\DocumentIssuer;
use App\Models\EmailTemplate;
use App\Models\Invoice;
use App\Models\ProjectCategory;
use App\Models\Signature;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function index()
    {
        $clients = Client::where('is_active', true)
            ->with('category')
            ->withCount([
                'invoices as draft_count'   => fn($q) => $q->where('status', 'draft'),
                'invoices as sent_count'    => fn($q) => $q->where('status', 'sent'),
                'invoices as paid_count'    => fn($q) => $q->where('status', 'paid'),
                'invoices as unpaid_count'  => fn($q) => $q->where('status', 'unpaid'),
                'invoices as overdue_count' => fn($q) => $q->whereNotIn('status', ['paid'])->where('due_date', '<', now()),
            ])
            ->withMax('invoices', 'issue_date')
            ->orderBy('company_name')
            ->get();

        $productCounts = Invoice::select('client_id', DB::raw('COUNT(DISTINCT project_category_id) as count'))
            ->groupBy('client_id')
            ->pluck('count', 'client_id');

        $unpaidAmounts = Invoice::select('client_id', DB::raw('SUM(invoice_items.amount) as total'))
            ->join('invoice_items', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->where('status', 'unpaid')
            ->groupBy('client_id')
            ->pluck('total', 'client_id');

        $totalAmounts = Invoice::select('client_id', DB::raw('SUM(invoice_items.amount) as total'))
            ->join('invoice_items', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->groupBy('client_id')
            ->pluck('total', 'client_id');

        $clients->each(function ($c) use ($productCounts, $unpaidAmounts, $totalAmounts) {
            $c->product_type_count = $productCounts[$c->id] ?? 0;
            $c->unpaid_amount      = (float) ($unpaidAmounts[$c->id] ?? 0);
            $c->total_amount       = (float) ($totalAmounts[$c->id] ?? 0);
        });

        $summary = [
            'total_outstanding' => $unpaidAmounts->sum(),
            'total_overdue'     => $clients->sum('overdue_count'),
            'total_unpaid'      => $clients->sum('unpaid_count'),
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
            ->whereYear('issue_date',  $next->year)
            ->whereMonth('issue_date', $next->month)
            ->orderBy('due_date')
            ->get();

        $otherInvoices = Invoice::with($with)
            ->withSum('items', 'amount')
            ->where('user_id', auth()->id())
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

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice dibuat. Silakan tambahkan item.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load([
            'client.emails', 'projectCategory', 'documentIssuer',
            'bankAccount', 'signature', 'items', 'user', 'emailTemplate',
            'parent', 'children',
        ]);

        return Inertia::render('Invoices/Show', [
            'invoice' => array_merge($invoice->toArray(), [
                'subtotal'     => $invoice->subtotal,
                'tax_amount'   => $invoice->tax_amount,
                'total'        => $invoice->total,
                'client_emails'=> $invoice->client?->emails->pluck('email')->toArray() ?? [],
            ]),
            'emailTemplates' => EmailTemplate::orderBy('name')->get(['id', 'name', 'subject', 'body', 'is_default']),
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

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice berhasil diperbarui.');
    }

    public function destroy(Invoice $invoice)
    {
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
                        'bankAccount', 'signature', 'items', 'user']);

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

        $emailList = implode(', ', $validated['emails']);

        return back()->with('success', "Invoice berhasil dikirim ke {$emailList}.");
    }

    public function download(Invoice $invoice)
    {
        $invoice->load([
            'client', 'projectCategory', 'documentIssuer',
            'bankAccount', 'signature', 'items', 'user',
        ]);

        $html      = view('invoices.pdf', ['invoice' => $invoice, 'imgB64' => fn($url) => $this->urlToBase64($url)])->render();
        $pdf       = base64_decode(app(\App\Services\PdfService::class)->generate($html));
        $filename  = str_replace('/', '-', $invoice->invoice_number) . '.pdf';

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function printView(Invoice $invoice)
    {
        $invoice->load([
            'client', 'projectCategory', 'documentIssuer',
            'bankAccount', 'signature', 'items', 'user',
        ]);

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
            'bankAccount', 'signature', 'items', 'user',
        ]);

        return view('invoices.receipt', [
            'invoice' => $invoice,
            'imgB64'  => fn($url) => $this->urlToBase64($url),
        ]);
    }

    public function updateTax(Request $request, Invoice $invoice)
    {
        $request->validate(['tax_percentage' => 'nullable|numeric|min:0|max:100']);
        $invoice->update(['tax_percentage' => $request->tax_percentage]);
        return back()->with('success', 'Pajak diperbarui.');
    }

    public function toggleMark(Invoice $invoice)
    {
        $invoice->update(['is_marked' => ! $invoice->is_marked]);
        return back()->with('success', $invoice->is_marked ? 'Invoice ditandai.' : 'Tanda dihapus.');
    }

    public function updateInterval(Request $request, Invoice $invoice)
    {
        $request->validate(['interval_months' => 'nullable|integer|min:1|max:12']);
        $invoice->update(['interval_months' => $request->interval_months]);
        return back()->with('success', 'Interval diperbarui.');
    }

    public function updateMeta(Request $request, Invoice $invoice)
    {
        $request->validate([
            'attention' => 'nullable|string|max:255',
            'notes'     => 'nullable|string',
        ]);
        $invoice->update($request->only('attention', 'notes'));
        return back()->with('success', 'Disimpan.');
    }

    public function updateStatus(Request $request, Invoice $invoice)
    {
        $request->validate(['status' => 'required|in:draft,sent,paid,unpaid']);
        $invoice->update(['status' => $request->status]);
        $invoice->refresh();

        if ($request->status === 'paid' && $invoice->interval_months && $invoice->children()->doesntExist()) {
            $this->generateRecurring($invoice);
            return back()->with('success', 'Invoice lunas. Invoice perpanjangan dibuat sebagai draft.');
        }

        return back()->with('success', 'Status diperbarui.');
    }

    private function generateRecurring(Invoice $parent): void
    {
        $parent->load('items', 'projectCategory');

        $issueDate = Carbon::parse($parent->due_date)->addDay();
        $dueDate   = $issueDate->copy()->addMonths($parent->interval_months);

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
            'interval_months'     => $parent->interval_months,
            'parent_invoice_id'   => $parent->id,
        ]);

        foreach ($parent->items as $item) {
            $child->items()->create([
                'description' => $item->description,
                'amount'      => $item->amount,
                'sort_order'  => $item->sort_order,
            ]);
        }
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
