<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\UserNotificationEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class NotificationEmailController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $user->load('notificationEmails');

        $groups = $user->notificationEmails
            ->sortBy('id')
            ->groupBy('group_id')
            ->map(fn($emails, $groupId) => [
                'group_id' => $groupId,
                'emails'   => $emails->values()->map(fn($ne) => [
                    'id'         => $ne->id,
                    'email'      => $ne->is_default ? $user->email : $ne->email,
                    'label'      => $ne->label,
                    'is_active'  => $ne->is_active,
                    'is_default' => $ne->is_default,
                    'send_type'  => $ne->send_type,
                    'group_id'   => $ne->group_id,
                ]),
            ])
            ->values();

        return Inertia::render('Settings/NotificationEmails', [
            'groups' => $groups,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'email'     => 'required|email|max:255',
            'label'     => 'nullable|string|max:100',
            'group_id'  => ['required', function ($attr, $value, $fail) use ($user) {
                if ($value === 'new') return;
                if (! is_numeric($value) || (int) $value < 1) {
                    $fail('Group ID tidak valid.');
                    return;
                }
                // Pastikan group_id memang milik user ini
                $exists = $user->notificationEmails()->where('group_id', (int) $value)->exists();
                if (! $exists) $fail('Grup tidak ditemukan.');
            }],
            'send_type' => 'required|in:to,cc',
        ]);

        if ($request->group_id === 'new') {
            $groupId = ($user->notificationEmails()->max('group_id') ?? 0) + 1;
        } else {
            $groupId = (int) $request->group_id;
        }

        $user->notificationEmails()->create([
            'email'      => trim($request->email),
            'label'      => trim($request->label ?? ''),
            'is_active'  => true,
            'is_default' => false,
            'group_id'   => $groupId,
            'send_type'  => $request->send_type,
        ]);

        return back()->with('success', 'Email berhasil ditambahkan.');
    }

    public function toggle(UserNotificationEmail $notificationEmail)
    {
        abort_if($notificationEmail->user_id !== auth()->id(), 403);
        $notificationEmail->update(['is_active' => !$notificationEmail->is_active]);
        return back();
    }

    public function setType(UserNotificationEmail $notificationEmail, Request $request)
    {
        abort_if($notificationEmail->user_id !== auth()->id(), 403);
        $request->validate(['send_type' => 'required|in:to,cc']);

        if ($request->send_type === 'cc' && $notificationEmail->send_type === 'to') {
            // Pakai lockForUpdate untuk cegah race condition dua request bersamaan
            $updated = DB::transaction(function () use ($notificationEmail) {
                $toCount = auth()->user()->notificationEmails()
                    ->where('group_id', $notificationEmail->group_id)
                    ->where('send_type', 'to')
                    ->lockForUpdate()
                    ->count();

                if ($toCount <= 1) return false;

                $notificationEmail->update(['send_type' => 'cc']);
                return true;
            });

            if (! $updated) {
                return back()->with('error', 'Grup harus punya minimal 1 TO.');
            }

            return back();
        }

        $notificationEmail->update(['send_type' => $request->send_type]);
        return back();
    }

    public function destroy(UserNotificationEmail $notificationEmail)
    {
        abort_if($notificationEmail->user_id !== auth()->id(), 403);
        abort_if($notificationEmail->is_default, 403);

        // Jangan hapus kalau satu-satunya TO di grup ini
        if ($notificationEmail->send_type === 'to') {
            $toCount = auth()->user()->notificationEmails()
                ->where('group_id', $notificationEmail->group_id)
                ->where('send_type', 'to')
                ->count();

            if ($toCount <= 1) {
                return back()->with('error', 'Tidak bisa hapus: grup harus punya minimal 1 TO. Ubah ke CC dulu atau tambah TO lain.');
            }
        }

        $notificationEmail->delete();
        return back()->with('success', 'Email berhasil dihapus.');
    }
}
