<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\ClientCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientCategoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Master/ClientCategories/Index', [
            'categories' => ClientCategory::latest()->get(),
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

        ClientCategory::create($request->only('name', 'description'));

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

        return redirect()->route('master.client-categories.index')->with('success', 'Kategori klien berhasil diupdate.');
    }

    public function destroy(ClientCategory $clientCategory)
    {
        $clientCategory->delete();

        return redirect()->route('master.client-categories.index')->with('success', 'Kategori klien berhasil dihapus.');
    }
}
