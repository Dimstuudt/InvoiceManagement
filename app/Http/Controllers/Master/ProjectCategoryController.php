<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ProjectCategory;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectCategoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Master/ProjectCategories/Index', [
            'categories' => ProjectCategory::latest()->get(),
            'trashed'    => ProjectCategory::onlyTrashed()->latest('deleted_at')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Master/ProjectCategories/Create', [
            'companies' => Company::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:50|unique:project_categories,code',
            'description' => 'nullable|string',
            'company_id'  => 'nullable|exists:companies,id',
        ]);

        $category = ProjectCategory::create($request->only('name', 'code', 'description', 'company_id'));
        ActivityLogger::log('project_category.created', $category);

        return redirect()->route('master.project-categories.index')->with('success', 'Kategori proyek berhasil ditambahkan.');
    }

    public function edit(ProjectCategory $projectCategory)
    {
        return Inertia::render('Master/ProjectCategories/Edit', [
            'category'  => $projectCategory,
            'companies' => Company::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, ProjectCategory $projectCategory)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:50|unique:project_categories,code,' . $projectCategory->id,
            'description' => 'nullable|string',
            'company_id'  => 'nullable|exists:companies,id',
        ]);

        $projectCategory->update($request->only('name', 'code', 'description', 'company_id'));
        ActivityLogger::log('project_category.updated', $projectCategory);

        return redirect()->route('master.project-categories.index')->with('success', 'Kategori proyek berhasil diupdate.');
    }

    public function destroy(ProjectCategory $projectCategory)
    {
        ActivityLogger::log('project_category.deleted', $projectCategory);
        $projectCategory->delete();

        return redirect()->route('master.project-categories.index')->with('success', 'Kategori proyek berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        $count = 0;
        ProjectCategory::whereIn('id', $request->ids)->each(function ($item) use (&$count) {
            ActivityLogger::log('project_category.deleted', $item);
            $item->delete();
            $count++;
        });
        return back()->with('success', "{$count} data berhasil dihapus ke trash.");
    }
}
