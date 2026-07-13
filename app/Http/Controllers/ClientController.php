<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientCategory;
use App\Models\ClientEmail;
use App\Support\ActivityLogger;
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
            'emails'             => 'nullable|array',
            'emails.*'           => 'nullable|email|max:255',
            'social_media'       => 'nullable|array',
            'social_media.*'     => 'nullable|string|max:255',
        ]);

        $data = $request->only('client_category_id', 'company_name', 'address', 'city', 'director', 'pic', 'client_status', 'is_active');

        if ($request->hasFile('npwp_image')) {
            $data['npwp_image'] = $request->file('npwp_image')->store('clients/npwp', 'public');
        }

        $client = Client::create($data);

        $this->syncPhones($client, $request->phones ?? []);
        $this->syncEmails($client, $request->emails ?? []);
        $this->syncSocialMedia($client, $request->social_media ?? []);

        ActivityLogger::log('client.created', $client);

        return redirect()->route('clients.index')->with('success', 'Client berhasil ditambahkan.');
    }

    public function edit(Client $client)
    {
        $client->load('phones', 'emails', 'socialMedia');

        return Inertia::render('Clients/Edit', [
            'client'     => [
                ...$client->toArray(),
                'npwp_image_url' => $client->npwp_image ? Storage::url($client->npwp_image) : null,
                'phones'         => $client->phones->pluck('phone_number')->toArray(),
                'emails'         => $client->emails->pluck('email')->toArray(),
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
            'emails'             => 'nullable|array',
            'emails.*'           => 'nullable|email|max:255',
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
        $this->syncEmails($client, $request->emails ?? []);
        $this->syncSocialMedia($client, $request->social_media ?? []);

        ActivityLogger::log('client.updated', $client);

        return redirect()->route('clients.index')->with('success', 'Client berhasil diupdate.');
    }

    public function toggleEmail(Client $client, ClientEmail $email)
    {
        abort_if($email->client_id !== $client->id, 403);
        $email->update(['is_active' => !$email->is_active]);
        return back();
    }

    public function destroy(Client $client)
    {
        ActivityLogger::log('client.deleted', $client);

        if ($client->npwp_image) {
            Storage::disk('public')->delete($client->npwp_image);
        }

        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client berhasil dihapus.');
    }

    private function normalizePhone(string $raw): string
    {
        $digits = preg_replace('/[^\d]/', '', $raw);
        if ($digits === '') return '';
        if (str_starts_with($digits, '62'))  return $digits;
        if (str_starts_with($digits, '0'))   return '62' . substr($digits, 1);
        if (str_starts_with($digits, '8'))   return '62' . $digits;
        return $digits;
    }

    private function syncPhones(Client $client, array $phones): void
    {
        $client->phones()->delete();
        $filtered = array_values(array_filter($phones, fn($p) => trim($p) !== ''));
        foreach ($filtered as $phone) {
            $normalized = $this->normalizePhone(trim($phone));
            if ($normalized !== '') {
                $client->phones()->create(['phone_number' => $normalized]);
            }
        }
    }

    private function syncEmails(Client $client, array $emails): void
    {
        $client->emails()->delete();
        $filtered = array_values(array_filter($emails, fn($e) => trim($e) !== ''));
        foreach ($filtered as $email) {
            $client->emails()->create(['email' => trim($email)]);
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
