<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ProjectCategory;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyController extends Controller
{
    public function index()
    {
        return Inertia::render('Master/Companies/Index', [
            'companies'  => Company::with('projectCategories')->latest()->get(),
            'categories' => ProjectCategory::orderBy('name')->get(['id', 'name', 'code', 'company_id']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:20|unique:companies,code',
            'description' => 'nullable|string',
        ]);

        $company = Company::create($request->only('name', 'code', 'description'));
        ActivityLogger::log('company.created', $company);

        return back()->with('success', 'Perusahaan berhasil ditambahkan.');
    }

    public function update(Request $request, Company $company)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:20|unique:companies,code,' . $company->id,
            'description' => 'nullable|string',
        ]);

        $company->update($request->only('name', 'code', 'description'));
        ActivityLogger::log('company.updated', $company);

        return back()->with('success', 'Perusahaan berhasil diupdate.');
    }

    public function destroy(Company $company)
    {
        $company->projectCategories()->update(['company_id' => null]);
        ActivityLogger::log('company.deleted', $company);
        $company->delete();

        return back()->with('success', 'Perusahaan berhasil dihapus.');
    }

    public function assign(Request $request, Company $company)
    {
        $request->validate([
            'category_ids'   => 'array',
            'category_ids.*' => 'integer|exists:project_categories,id',
        ]);

        // Lepas semua yang sebelumnya milik company ini
        $company->projectCategories()->update(['company_id' => null]);

        // Assign yang baru dipilih
        if (!empty($request->category_ids)) {
            ProjectCategory::whereIn('id', $request->category_ids)
                ->update(['company_id' => $company->id]);
        }

        ActivityLogger::log('company.assign', $company, ['category_ids' => $request->category_ids ?? []]);

        return back()->with('success', 'Kategori berhasil disimpan.');
    }
}
