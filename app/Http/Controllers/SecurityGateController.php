<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

class SecurityGateController extends Controller
{
    public function activateBypass(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'otp'      => 'required|string|size:6',
            'minutes'  => 'required|integer|in:15,30,60,240',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Password salah.'], 422);
        }

        if (!$user->google2fa_secret) {
            return response()->json(['message' => '2FA belum diaktifkan. Setup Google Authenticator terlebih dahulu.'], 422);
        }

        $google2fa = new Google2FA();
        if (!$google2fa->verifyKey($user->google2fa_secret, $request->otp)) {
            return response()->json(['message' => 'Kode 2FA tidak valid atau sudah kedaluwarsa.'], 422);
        }

        $expiresAt = now()->addMinutes((int) $request->minutes)->timestamp;
        session(['security_bypass_expires_at' => $expiresAt]);

        return response()->json(['success' => true]);
    }

    public function deactivateBypass()
    {
        session()->forget('security_bypass_expires_at');
        return response()->json(['success' => true]);
    }
}
