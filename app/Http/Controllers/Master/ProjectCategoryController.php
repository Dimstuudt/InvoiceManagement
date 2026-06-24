<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\ProjectCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectCategoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Master/ProjectCategories/Index', [
            'categories' => ProjectCategory::latest()->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Master/ProjectCategories/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:50|unique:project_categories,code',
            'description' => 'nullable|string',
        ]);

        ProjectCategory::create($request->only('name', 'code', 'description'));

        return redirect()->route('master.project-categories.index')->with('success', 'Kategori proyek berhasil ditambahkan.');
    }

    public function edit(ProjectCategory $projectCategory)
    {
        return Inertia::render('Master/ProjectCategories/Edit', [
            'category' => $projectCategory,
        ]);
    }

    public function update(Request $request, ProjectCategory $projectCategory)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:50|unique:project_categories,code,' . $projectCategory->id,
            'description' => 'nullable|string',
        ]);

        $projectCategory->update($request->only('name', 'code', 'description'));

        return redirect()->route('master.project-categories.index')->with('success', 'Kategori proyek berhasil diupdate.');
    }

    public function destroy(ProjectCategory $projectCategory)
    {
        $projectCategory->delete();

        return redirect()->route('master.project-categories.index')->with('success', 'Kategori proyek berhasil dihapus.');
    }
}
