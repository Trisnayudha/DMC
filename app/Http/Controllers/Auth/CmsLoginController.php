<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CmsLoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('cms')->check()) {
            return redirect('/admin/home');
        }

        return view('auth.cms-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->boolean('remember');

        if (!Auth::guard('cms')->attempt($credentials, $remember)) {
            return back()->withErrors(['email' => 'Email atau password salah.'])->withInput();
        }

        $user = Auth::guard('cms')->user();

        if (!$user->is_active) {
            Auth::guard('cms')->logout();
            return back()->withErrors(['email' => 'Account is deactivated.'])->withInput();
        }

        $request->session()->regenerate();

        return redirect()->intended('/admin/home');
    }

    public function logout(Request $request)
    {
        Auth::guard('cms')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('cms.login');
    }
}
