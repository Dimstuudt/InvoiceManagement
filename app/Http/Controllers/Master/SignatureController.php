<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Signature;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SignatureController extends Controller
{
    public function index()
    {
        return Inertia::render('Master/Signatures/Index', [
            'signatures' => Signature::latest()->get()->map(fn($s) => [
                ...$s->toArray(),
                'signature_image_url' => $s->signature_image ? Storage::url($s->signature_image) : null,
            ]),
            'trashed' => Signature::onlyTrashed()->latest('deleted_at')->get()->map(fn($s) => [
                ...$s->toArray(),
                'signature_image_url' => $s->signature_image ? Storage::url($s->signature_image) : null,
            ]),
        ]);
    }

    public function create()
    {
        return Inertia::render('Master/Signatures/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'position'        => 'required|string|max:255',
            'signature_image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        $data = $request->only('name', 'position');

        if ($request->hasFile('signature_image')) {
            $data['signature_image'] = $request->file('signature_image')->store('signatures', 'public');
        }

        $signature = Signature::create($data);
        ActivityLogger::log('signature.created', $signature);

        return redirect()->route('master.signatures.index')->with('success', 'Signature berhasil ditambahkan.');
    }

    public function edit(Signature $signature)
    {
        return Inertia::render('Master/Signatures/Edit', [
            'signature' => [
                ...$signature->toArray(),
                'signature_image_url' => $signature->signature_image ? Storage::url($signature->signature_image) : null,
            ],
        ]);
    }

    public function update(Request $request, Signature $signature)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'position'        => 'required|string|max:255',
            'signature_image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        $data = $request->only('name', 'position');

        if ($request->hasFile('signature_image')) {
            if ($signature->signature_image) {
                Storage::disk('public')->delete($signature->signature_image);
            }
            $data['signature_image'] = $request->file('signature_image')->store('signatures', 'public');
        }

        $signature->update($data);
        ActivityLogger::log('signature.updated', $signature);

        return redirect()->route('master.signatures.index')->with('success', 'Signature berhasil diupdate.');
    }

    public function destroy(Signature $signature)
    {
        ActivityLogger::log('signature.deleted', $signature);
        $signature->delete();

        return redirect()->route('master.signatures.index')->with('success', 'Signature berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        $count = 0;
        Signature::whereIn('id', $request->ids)->each(function ($item) use (&$count) {
            ActivityLogger::log('signature.deleted', $item);
            $item->delete();
            $count++;
        });
        return back()->with('success', "{$count} data berhasil dihapus ke trash.");
    }
}
