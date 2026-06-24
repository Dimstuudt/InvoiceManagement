<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class BankAccountController extends Controller
{
    public function index()
    {
        return Inertia::render('Master/BankAccounts/Index', [
            'accounts' => BankAccount::latest()->get()->map(fn($a) => [
                ...$a->toArray(),
                'bank_logo_url' => $a->bank_logo ? Storage::url($a->bank_logo) : null,
            ]),
        ]);
    }

    public function create()
    {
        return Inertia::render('Master/BankAccounts/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'bank_name'      => 'required|string|max:255',
            'bank_logo'      => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        $data = $request->only('name', 'account_number', 'bank_name');

        if ($request->hasFile('bank_logo')) {
            $data['bank_logo'] = $request->file('bank_logo')->store('banks', 'public');
        }

        BankAccount::create($data);

        return redirect()->route('master.bank-accounts.index')->with('success', 'Bank account berhasil ditambahkan.');
    }

    public function edit(BankAccount $bankAccount)
    {
        return Inertia::render('Master/BankAccounts/Edit', [
            'account' => [
                ...$bankAccount->toArray(),
                'bank_logo_url' => $bankAccount->bank_logo ? Storage::url($bankAccount->bank_logo) : null,
            ],
        ]);
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'bank_name'      => 'required|string|max:255',
            'bank_logo'      => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        $data = $request->only('name', 'account_number', 'bank_name');

        if ($request->hasFile('bank_logo')) {
            if ($bankAccount->bank_logo) {
                Storage::disk('public')->delete($bankAccount->bank_logo);
            }
            $data['bank_logo'] = $request->file('bank_logo')->store('banks', 'public');
        }

        $bankAccount->update($data);

        return redirect()->route('master.bank-accounts.index')->with('success', 'Bank account berhasil diupdate.');
    }

    public function destroy(BankAccount $bankAccount)
    {
        if ($bankAccount->bank_logo) {
            Storage::disk('public')->delete($bankAccount->bank_logo);
        }

        $bankAccount->delete();

        return redirect()->route('master.bank-accounts.index')->with('success', 'Bank account berhasil dihapus.');
    }
}
