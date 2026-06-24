<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index()
    {
        return Inertia::render('Clients/Index', [
            'clients' => Client::with('category', 'phones')
                ->latest()
                ->get()
                ->map(fn($c) => [
                    'id'             => $c->id,
                    'company_name'   => $c->company_name,
                    'category'       => $c->category?->name,
                    'city'           => $c->city,
                    'pic'            => $c->pic,
                    'phones'         => $c->phones->pluck('phone_number'),
                    'client_status'  => $c->client_status,
                    'is_active'      => $c->is_active,
                ]),
        ]);
    }

    public function create()
    {
        return Inertia::render('Clients/Create', [
            'categories' => ClientCategory::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_category_id' => 'required|exists:client_categories,id',
            'company_name'       => 'required|string|max:255',
            'address'            => 'required|string',
            'city'               => 'required|string|max:100',
            'director'           => 'required|string|max:255',
            'pic'                => 'required|string|max:255',
            'npwp_image'         => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'client_status'      => 'required|in:prospect,active_client',
            'is_active'          => 'boolean',
            'phones'             => 'nullable|array',
            'phones.*'           => 'nullable|string|max:20',
            'social_media'       => 'nullable|array',
            'social_media.*'     => 'nullable|string|max:255',
        ]);

        $data = $request->only('client_category_id', 'company_name', 'address', 'city', 'director', 'pic', 'client_status', 'is_active');

        if ($request->hasFile('npwp_image')) {
            $data['npwp_image'] = $request->file('npwp_image')->store('clients/npwp', 'public');
        }

        $client = Client::create($data);

        $this->syncPhones($client, $request->phones ?? []);
        $this->syncSocialMedia($client, $request->social_media ?? []);

        return redirect()->route('clients.index')->with('success', 'Client berhasil ditambahkan.');
    }

    public function edit(Client $client)
    {
        $client->load('phones', 'socialMedia');

        return Inertia::render('Clients/Edit', [
            'client'     => [
                ...$client->toArray(),
                'npwp_image_url' => $client->npwp_image ? Storage::url($client->npwp_image) : null,
                'phones'         => $client->phones->pluck('phone_number')->toArray(),
                'social_media'   => $client->socialMedia->pluck('url')->toArray(),
            ],
            'categories' => ClientCategory::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'client_category_id' => 'required|exists:client_categories,id',
            'company_name'       => 'required|string|max:255',
            'address'            => 'required|string',
            'city'               => 'required|string|max:100',
            'director'           => 'required|string|max:255',
            'pic'                => 'required|string|max:255',
            'npwp_image'         => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'client_status'      => 'required|in:prospect,active_client',
            'is_active'          => 'boolean',
            'phones'             => 'nullable|array',
            'phones.*'           => 'nullable|string|max:20',
            'social_media'       => 'nullable|array',
            'social_media.*'     => 'nullable|string|max:255',
        ]);

        $data = $request->only('client_category_id', 'company_name', 'address', 'city', 'director', 'pic', 'client_status', 'is_active');

        if ($request->hasFile('npwp_image')) {
            if ($client->npwp_image) {
                Storage::disk('public')->delete($client->npwp_image);
            }
            $data['npwp_image'] = $request->file('npwp_image')->store('clients/npwp', 'public');
        }

        $client->update($data);

        $this->syncPhones($client, $request->phones ?? []);
        $this->syncSocialMedia($client, $request->social_media ?? []);

        return redirect()->route('clients.index')->with('success', 'Client berhasil diupdate.');
    }

    public function destroy(Client $client)
    {
        if ($client->npwp_image) {
            Storage::disk('public')->delete($client->npwp_image);
        }

        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client berhasil dihapus.');
    }

    private function syncPhones(Client $client, array $phones): void
    {
        $client->phones()->delete();
        $filtered = array_values(array_filter($phones, fn($p) => trim($p) !== ''));
        foreach ($filtered as $phone) {
            $client->phones()->create(['phone_number' => trim($phone)]);
        }
    }

    private function syncSocialMedia(Client $client, array $socials): void
    {
        $client->socialMedia()->delete();
        $filtered = array_values(array_filter($socials, fn($s) => trim($s) !== ''));
        foreach ($filtered as $url) {
            $client->socialMedia()->create(['url' => trim($url)]);
        }
    }
}
