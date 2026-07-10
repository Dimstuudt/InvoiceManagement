<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
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
            'groups' => EmailTemplateGroup::orderBy('name')->get(),
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

    public function destroy(EmailTemplateGroup $emailTemplateGroup)
    {
        ActivityLogger::log('email_template_group.deleted', $emailTemplateGroup);
        $emailTemplateGroup->delete();

        return redirect()->route('master.email-template-groups.index')
            ->with('success', 'Grup template email berhasil dihapus.');
    }
}
