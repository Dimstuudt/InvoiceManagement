<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\ClientCategory;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientCategoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Master/ClientCategories/Index', [
            'categories' => ClientCategory::latest()->get(),
            'trashed'    => ClientCategory::onlyTrashed()->latest('deleted_at')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Master/ClientCategories/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category = ClientCategory::create($request->only('name', 'description'));
        ActivityLogger::log('client_category.created', $category);

        return redirect()->route('master.client-categories.index')->with('success', 'Kategori klien berhasil ditambahkan.');
    }

    public function edit(ClientCategory $clientCategory)
    {
        return Inertia::render('Master/ClientCategories/Edit', [
            'category' => $clientCategory,
        ]);
    }

    public function update(Request $request, ClientCategory $clientCategory)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $clientCategory->update($request->only('name', 'description'));
        ActivityLogger::log('client_category.updated', $clientCategory);

        return redirect()->route('master.client-categories.index')->with('success', 'Kategori klien berhasil diupdate.');
    }

    public function destroy(ClientCategory $clientCategory)
    {
        ActivityLogger::log('client_category.deleted', $clientCategory);
        $clientCategory->delete();

        return redirect()->route('master.client-categories.index')->with('success', 'Kategori klien berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        $count = 0;
        ClientCategory::whereIn('id', $request->ids)->each(function ($item) use (&$count) {
            ActivityLogger::log('client_category.deleted', $item);
            $item->delete();
            $count++;
        });
        return back()->with('success', "{$count} data berhasil dihapus ke trash.");
    }
}
