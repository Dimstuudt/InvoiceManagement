<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\SenderDomain;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class SenderDomainController extends Controller
{
    public function index()
    {
        return Inertia::render('Master/SenderDomains/Index', [
            'senderDomains' => SenderDomain::latest()->get()->map(fn($s) => [
                ...$s->toArray(),
                'sender_email' => $s->sender_email,
            ]),
            'trashed' => SenderDomain::onlyTrashed()->latest('deleted_at')->get()->map(fn($s) => [
                ...$s->toArray(),
                'sender_email' => $s->sender_email,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'display_name' => 'required|string|max:255',
            'prefix'       => 'required|string|max:100|regex:/^[a-zA-Z0-9._+-]+$/',
            'domain'       => 'required|string|max:255',
        ]);

        $sd = SenderDomain::create($request->only('display_name', 'prefix', 'domain'));
        ActivityLogger::log('sender_domain.created', $sd);

        return back()->with('success', "Sender {$sd->sender_email} berhasil ditambahkan.");
    }

    public function update(Request $request, SenderDomain $senderDomain)
    {
        $request->validate([
            'display_name' => 'required|string|max:255',
            'prefix'       => 'required|string|max:100|regex:/^[a-zA-Z0-9._+-]+$/',
            'domain'       => 'required|string|max:255',
        ]);

        $senderDomain->update($request->only('display_name', 'prefix', 'domain'));
        ActivityLogger::log('sender_domain.updated', $senderDomain);

        return back()->with('success', 'Sender domain berhasil diupdate.');
    }

    public function destroy(SenderDomain $senderDomain)
    {
        ActivityLogger::log('sender_domain.deleted', $senderDomain);
        $senderDomain->delete();

        return back()->with('success', 'Sender domain berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        $count = 0;
        SenderDomain::whereIn('id', $request->ids)->each(function ($item) use (&$count) {
            ActivityLogger::log('sender_domain.deleted', $item);
            $item->delete();
            $count++;
        });
        return back()->with('success', "{$count} data berhasil dihapus ke trash.");
    }
}
