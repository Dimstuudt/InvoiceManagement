<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\UserNotificationEmail;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationEmailController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $user->load('notificationEmails');

        return Inertia::render('Settings/NotificationEmails', [
            'emails' => $user->notificationEmails->map(fn($ne) => [
                'id'         => $ne->id,
                'email'      => $ne->is_default ? $user->email : $ne->email,
                'label'      => $ne->label,
                'is_active'  => $ne->is_active,
                'is_default' => $ne->is_default,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'label' => 'nullable|string|max:100',
        ]);

        auth()->user()->notificationEmails()->create([
            'email'      => trim($request->email),
            'label'      => trim($request->label ?? ''),
            'is_active'  => true,
            'is_default' => false,
        ]);

        return back()->with('success', 'Email berhasil ditambahkan.');
    }

    public function toggle(UserNotificationEmail $notificationEmail)
    {
        abort_if($notificationEmail->user_id !== auth()->id(), 403);
        $notificationEmail->update(['is_active' => !$notificationEmail->is_active]);
        return back();
    }

    public function destroy(UserNotificationEmail $notificationEmail)
    {
        abort_if($notificationEmail->user_id !== auth()->id(), 403);
        abort_if($notificationEmail->is_default, 403);
        $notificationEmail->delete();
        return back()->with('success', 'Email berhasil dihapus.');
    }
}
