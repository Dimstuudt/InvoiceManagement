<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use PragmaRX\Google2FA\Google2FA;
use PragmaRX\Google2FAQRCode\Google2FA as Google2FAQR;

class TwoFactorController extends Controller
{
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function setup(Request $request)
    {
        $user = $request->user();

        if ($user->google2fa_secret) {
            return redirect()->route('dashboard');
        }

        $secret = $request->session()->get('2fa_setup_secret')
            ?? $this->google2fa->generateSecretKey();

        $request->session()->put('2fa_setup_secret', $secret);

        $qr = new Google2FAQR();
        $svg    = $qr->getQRCodeInline(config('app.name'), $user->email, $secret);
        $qrCode = 'data:image/svg+xml;base64,' . base64_encode($svg);

        return Inertia::render('TwoFactor/Setup', [
            'qrCode' => $qrCode,
            'secret' => $secret,
        ]);
    }

    public function enable(Request $request)
    {
        $request->validate(['code' => 'required|string|min:6|max:8']);

        $secret = $request->session()->get('2fa_setup_secret');

        if (!$secret) {
            return back()->withErrors(['code' => 'Sesi habis, muat ulang halaman.']);
        }

        $newTimestamp = $this->google2fa->verifyKeyNewer($secret, $request->code, null);

        if ($newTimestamp === false) {
            return back()->withErrors(['code' => 'Kode salah atau sudah kedaluwarsa, coba lagi.']);
        }

        $request->user()->update(['google2fa_secret' => $secret]);
        $request->session()->forget('2fa_setup_secret');
        $request->session()->put('2fa_verified', true);

        return redirect()->route('dashboard');
    }

    public function challenge(Request $request)
    {
        if ($request->session()->get('2fa_verified')) {
            return redirect()->route('dashboard');
        }

        if (!$request->user()->google2fa_secret) {
            return redirect()->route('2fa.setup');
        }

        return Inertia::render('TwoFactor/Challenge');
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|string|min:6|max:8']);

        $user   = $request->user();
        $secret = $user->google2fa_secret;

        $cacheKey     = '2fa_ts_' . $user->id;
        $oldTimestamp = Cache::get($cacheKey);

        $newTimestamp = $this->google2fa->verifyKeyNewer($secret, $request->code, $oldTimestamp);

        if ($newTimestamp === false) {
            return back()->withErrors(['code' => 'Kode salah atau sudah kedaluwarsa, coba lagi.']);
        }

        // Store last-used OTP timestamp to block replay within the same window
        Cache::put($cacheKey, $newTimestamp, now()->addMinutes(5));

        $request->session()->put('2fa_verified', true);

        return redirect()->intended(route('dashboard'));
    }

    public function disable(Request $request)
    {
        $request->validate(['code' => 'required|string|min:6|max:8']);

        $user   = $request->user();
        $secret = $user->google2fa_secret;

        if (!$secret) {
            return back()->withErrors(['2fa_code' => 'Kode salah atau sudah kedaluwarsa.']);
        }

        $cacheKey     = '2fa_ts_' . $user->id;
        $oldTimestamp = Cache::get($cacheKey);

        $newTimestamp = $this->google2fa->verifyKeyNewer($secret, $request->code, $oldTimestamp);

        if ($newTimestamp === false) {
            return back()->withErrors(['2fa_code' => 'Kode salah atau sudah kedaluwarsa.']);
        }

        Cache::put($cacheKey, $newTimestamp, now()->addMinutes(5));

        $user->update(['google2fa_secret' => null]);
        $request->session()->forget('2fa_verified');
        Cache::forget($cacheKey);

        return redirect()->route('2fa.setup');
    }
}
