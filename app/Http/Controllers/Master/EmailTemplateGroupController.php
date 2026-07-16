<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\DocumentIssuer;
use App\Models\EmailTemplateGroup;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EmailTemplateGroupController extends Controller
{
    public function index()
    {
        return Inertia::render('Master/EmailTemplateGroups/Index', [
            'groups'  => EmailTemplateGroup::orderBy('name')->get(),
            'trashed' => EmailTemplateGroup::onlyTrashed()->latest('deleted_at')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Master/EmailTemplateGroups/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'is_default'     => 'boolean',
            'subject_send1'  => 'nullable|string|max:255',
            'body_send1'     => 'nullable|string',
            'subject_send2'  => 'nullable|string|max:255',
            'body_send2'     => 'nullable|string',
            'subject_send3'  => 'nullable|string|max:255',
            'body_send3'     => 'nullable|string',
            'subject_send4'  => 'nullable|string|max:255',
            'body_send4'     => 'nullable|string',
            'subject_send5'  => 'nullable|string|max:255',
            'body_send5'     => 'nullable|string',
            'subject_paid'   => 'nullable|string|max:255',
            'body_paid'      => 'nullable|string',
        ]);

        DB::transaction(function () use ($data, &$group) {
            if ($data['is_default'] ?? false) {
                EmailTemplateGroup::where('is_default', true)->update(['is_default' => false]);
            }
            $group = EmailTemplateGroup::create($data);
        });

        ActivityLogger::log('email_template_group.created', $group);

        return redirect()->route('master.email-template-groups.index')
            ->with('success', 'Grup template email berhasil ditambahkan.');
    }

    public function edit(EmailTemplateGroup $emailTemplateGroup)
    {
        return Inertia::render('Master/EmailTemplateGroups/Edit', [
            'group' => $emailTemplateGroup,
        ]);
    }

    public function update(Request $request, EmailTemplateGroup $emailTemplateGroup)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'is_default'     => 'boolean',
            'subject_send1'  => 'nullable|string|max:255',
            'body_send1'     => 'nullable|string',
            'subject_send2'  => 'nullable|string|max:255',
            'body_send2'     => 'nullable|string',
            'subject_send3'  => 'nullable|string|max:255',
            'body_send3'     => 'nullable|string',
            'subject_send4'  => 'nullable|string|max:255',
            'body_send4'     => 'nullable|string',
            'subject_send5'  => 'nullable|string|max:255',
            'body_send5'     => 'nullable|string',
            'subject_paid'   => 'nullable|string|max:255',
            'body_paid'      => 'nullable|string',
        ]);

        DB::transaction(function () use ($data, $emailTemplateGroup) {
            if ($data['is_default'] ?? false) {
                EmailTemplateGroup::where('is_default', true)
                    ->where('id', '!=', $emailTemplateGroup->id)
                    ->update(['is_default' => false]);
            }
            $emailTemplateGroup->update($data);
        });

        ActivityLogger::log('email_template_group.updated', $emailTemplateGroup);

        return redirect()->route('master.email-template-groups.index')
            ->with('success', 'Grup template email berhasil diperbarui.');
    }

    public function previewWrapper(string $type)
    {
        abort_if(!in_array($type, ['invoice', 'receipt']), 404);

        $issuerModel = DocumentIssuer::first();
        $issuer = $issuerModel ?? (object)[
            'name'           => config('app.name'),
            'tax_id_address' => null,
        ];

        $invoice = (object)[
            'invoice_number'  => 'INV/2025/VII/001',
            'documentIssuer'  => $issuer,
        ];

        $isReceipt = $type === 'receipt';
        $filename  = $isReceipt ? 'RCP-INV-2025-VII-001.pdf' : 'INV-2025-VII-001.pdf';
        $body      = $isReceipt
            ? "Yth. Bapak/Ibu,\n\nTerima kasih atas kepercayaan Bapak/Ibu kepada kami.\n\nPembayaran invoice INV/2025/VII/001 senilai Rp 15.000.000 telah kami terima dengan baik pada 13 Juli 2025. Terlampir bukti penerimaan pembayaran sebagai arsip Bapak/Ibu.\n\nKami berharap dapat terus melayani kebutuhan Bapak/Ibu di masa mendatang.\n\nHormat kami,\nTim Penagihan"
            : "Yth. Bapak/Ibu,\n\nBersama email ini kami sampaikan invoice INV/2025/VII/001 dengan nilai Rp 15.000.000 dan jatuh tempo pada 30 Juli 2025.\n\nMohon kiranya dapat melakukan pembayaran sesuai informasi rekening yang tertera pada lampiran sebelum tanggal jatuh tempo.\n\nApabila Bapak/Ibu memiliki pertanyaan terkait invoice ini, jangan ragu untuk menghubungi kami.\n\nTerima kasih atas kepercayaan Bapak/Ibu.\n\nHormat kami,\nTim Penagihan";

        return response(
            view('emails.wrapper', compact('invoice', 'filename', 'body'))->render()
        )->header('Content-Type', 'text/html');
    }

    public function destroy(EmailTemplateGroup $emailTemplateGroup)
    {
        ActivityLogger::log('email_template_group.deleted', $emailTemplateGroup);
        $emailTemplateGroup->delete();

        return redirect()->route('master.email-template-groups.index')
            ->with('success', 'Grup template email berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        $count = 0;
        EmailTemplateGroup::whereIn('id', $request->ids)->each(function ($item) use (&$count) {
            ActivityLogger::log('email_template_group.deleted', $item);
            $item->delete();
            $count++;
        });
        return back()->with('success', "{$count} data berhasil dihapus ke trash.");
    }
}
