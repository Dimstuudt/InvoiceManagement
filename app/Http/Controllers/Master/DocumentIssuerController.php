<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\DocumentIssuer;
use App\Models\SenderDomain;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DocumentIssuerController extends Controller
{
    public function index()
    {
        return Inertia::render('Master/DocumentIssuers/Index', [
            'issuers' => DocumentIssuer::with('senderDomain')->latest()->get()->map(fn($i) => [
                ...$i->toArray(),
                'header_image_url' => $i->header_image ? Storage::url($i->header_image) : null,
                'sender_email'     => $i->senderDomain?->sender_email,
                'sender_name'      => $i->senderDomain?->display_name,
            ]),
        ]);
    }

    public function create()
    {
        return Inertia::render('Master/DocumentIssuers/Create', [
            'senderDomains' => SenderDomain::orderBy('display_name')->get()->map(fn($s) => [
                'id'           => $s->id,
                'display_name' => $s->display_name,
                'sender_email' => $s->sender_email,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'header_image'     => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'tax_id_name'      => 'required|string|max:255',
            'tax_id_address'   => 'required|string',
            'tax_id_number'    => 'required|string|max:50',
            'sender_domain_id' => 'nullable|exists:sender_domains,id',
        ]);

        $data = $request->only('name', 'tax_id_name', 'tax_id_address', 'tax_id_number', 'sender_domain_id');

        if ($request->hasFile('header_image')) {
            $data['header_image'] = $request->file('header_image')->store('issuers', 'public');
        }

        $issuer = DocumentIssuer::create($data);
        ActivityLogger::log('document_issuer.created', $issuer);

        return redirect()->route('master.document-issuers.index')->with('success', 'Document issuer berhasil ditambahkan.');
    }

    public function edit(DocumentIssuer $documentIssuer)
    {
        return Inertia::render('Master/DocumentIssuers/Edit', [
            'issuer' => [
                ...$documentIssuer->toArray(),
                'header_image_url' => $documentIssuer->header_image ? Storage::url($documentIssuer->header_image) : null,
            ],
            'senderDomains' => SenderDomain::orderBy('display_name')->get()->map(fn($s) => [
                'id'           => $s->id,
                'display_name' => $s->display_name,
                'sender_email' => $s->sender_email,
            ]),
        ]);
    }

    public function update(Request $request, DocumentIssuer $documentIssuer)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'header_image'     => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'tax_id_name'      => 'required|string|max:255',
            'tax_id_address'   => 'required|string',
            'tax_id_number'    => 'required|string|max:50',
            'sender_domain_id' => 'nullable|exists:sender_domains,id',
        ]);

        $data = $request->only('name', 'tax_id_name', 'tax_id_address', 'tax_id_number', 'sender_domain_id');

        if ($request->hasFile('header_image')) {
            if ($documentIssuer->header_image) {
                Storage::disk('public')->delete($documentIssuer->header_image);
            }
            $data['header_image'] = $request->file('header_image')->store('issuers', 'public');
        }

        $documentIssuer->update($data);
        ActivityLogger::log('document_issuer.updated', $documentIssuer);

        return redirect()->route('master.document-issuers.index')->with('success', 'Document issuer berhasil diupdate.');
    }

    public function destroy(DocumentIssuer $documentIssuer)
    {
        ActivityLogger::log('document_issuer.deleted', $documentIssuer);

        if ($documentIssuer->header_image) {
            Storage::disk('public')->delete($documentIssuer->header_image);
        }

        $documentIssuer->delete();

        return redirect()->route('master.document-issuers.index')->with('success', 'Document issuer berhasil dihapus.');
    }
}
