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

        $user = $request->user();

        if (!Hash::check($request->input('password'), $user->password)) {
            return response()->json(['message' => 'Password salah.'], 422);
        }

        if (!$user->google2fa_secret) {
            return response()->json(['message' => '2FA belum diaktifkan. Setup Google Authenticator terlebih dahulu.'], 422);
        }

        $google2fa = new Google2FA();
        if (!$google2fa->verifyKey($user->google2fa_secret, $request->input('otp'))) {
            return response()->json(['message' => 'Kode 2FA tidak valid atau sudah kedaluwarsa.'], 422);
        }

        $expiresAt = now()->addMinutes((int) $request->input('minutes'))->timestamp;
        session(['security_bypass_expires_at' => $expiresAt]);

        return response()->json(['success' => true]);
    }

    public function verifyGate(Request $request)
    {
        $request->validate([
            'method'     => 'required|in:password,2fa',
            'credential' => 'required|string',
        ]);

        $user     = $request->user();
        $method   = $request->input('method');
        $cred     = $request->input('credential');

        if ($method === 'password') {
            if (!Hash::check($cred, $user->password)) {
                return response()->json(['message' => 'Password salah, coba lagi.'], 422);
            }
        } else {
            if (!$user->google2fa_secret) {
                return response()->json(['message' => '2FA belum diaktifkan.'], 422);
            }
            $google2fa = new Google2FA();
            if (!$google2fa->verifyKey($user->google2fa_secret, $cred)) {
                return response()->json(['message' => 'Kode 2FA tidak valid atau sudah kedaluwarsa.'], 422);
            }
        }

        return response()->json(['success' => true]);
    }

    public function deactivateBypass()
    {
        session()->forget('security_bypass_expires_at');
        return response()->json(['success' => true]);
    }
}
