<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorAuthenticated
{
    private const EXCLUDED_ROUTES = [
        '2fa.setup',
        '2fa.enable',
        '2fa.challenge',
        '2fa.verify',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->route()?->getName(), self::EXCLUDED_ROUTES)) {
            return $next($request);
        }

        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if (!$user->google2fa_secret) {
            return redirect()->route('2fa.setup');
        }

        if (!$request->session()->get('2fa_verified')) {
            return redirect()->route('2fa.challenge');
        }

        return $next($request);
    }
}
