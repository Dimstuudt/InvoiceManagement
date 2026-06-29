<?php

namespace App\Http\Controllers\Auth;

use Anhskohbo\NoCaptcha\Facades\NoCaptcha;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/Login', [
            'captchaSiteKey' => config('captcha.sitekey'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email'                => 'required|string|email',
            'password'             => 'required|string',
            'g_recaptcha_response' => 'required',
        ], [
            'g_recaptcha_response.required' => 'Harap centang verifikasi reCAPTCHA.',
        ]);

        if (!NoCaptcha::verifyResponse($request->g_recaptcha_response)) {
            return back()->withErrors([
                'g_recaptcha_response' => 'Verifikasi reCAPTCHA gagal, coba lagi.',
            ]);
        }

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ]);
        }

        $request->session()->regenerate();

        ActivityLogger::log('user.login');

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request)
    {
        ActivityLogger::log('user.logout');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
