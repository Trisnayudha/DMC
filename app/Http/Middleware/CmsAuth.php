<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CmsAuth
{
    public function handle(Request $request, Closure $next)
    {
        Auth::shouldUse('cms');

        if (!Auth::check()) {
            // guest() simpan URL ini ke session (url.intended) supaya setelah
            // login CmsLoginController::login() bisa redirect()->intended() ke sini.
            return redirect()->guest(route('cms.login'));
        }

        if (!Auth::user()->is_active) {
            Auth::logout();
            return redirect()->route('cms.login')->with('error', 'Account is deactivated.');
        }

        abort_unless(\App\Support\CmsAccess::allowsRoute(Auth::user(), $request->route()), 403, 'Anda tidak memiliki akses ke menu atau aksi ini.');

        return $next($request);
    }
}
