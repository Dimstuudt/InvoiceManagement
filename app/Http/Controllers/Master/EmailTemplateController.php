<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmailTemplateController extends Controller
{
    public function index()
    {
        return Inertia::render('Master/EmailTemplates/Index', [
            'templates' => EmailTemplate::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Master/EmailTemplates/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'subject'    => 'required|string|max:255',
            'body'       => 'required|string',
            'is_default' => 'boolean',
        ]);

        if ($data['is_default'] ?? false) {
            EmailTemplate::where('is_default', true)->update(['is_default' => false]);
        }

        EmailTemplate::create($data);

        return redirect()->route('master.email-templates.index')
            ->with('success', 'Template email berhasil ditambahkan.');
    }

    public function edit(EmailTemplate $emailTemplate)
    {
        return Inertia::render('Master/EmailTemplates/Edit', [
            'template' => $emailTemplate,
        ]);
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'subject'    => 'required|string|max:255',
            'body'       => 'required|string',
            'is_default' => 'boolean',
        ]);

        if ($data['is_default'] ?? false) {
            EmailTemplate::where('is_default', true)
                ->where('id', '!=', $emailTemplate->id)
                ->update(['is_default' => false]);
        }

        $emailTemplate->update($data);

        return redirect()->route('master.email-templates.index')
            ->with('success', 'Template email berhasil diperbarui.');
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();

        return redirect()->route('master.email-templates.index')
            ->with('success', 'Template email berhasil dihapus.');
    }
}
