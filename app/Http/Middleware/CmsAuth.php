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
            return redirect()->route('cms.login');
        }

        if (!Auth::user()->is_active) {
            Auth::logout();
            return redirect()->route('cms.login')->with('error', 'Account is deactivated.');
        }

        return $next($request);
    }
}
