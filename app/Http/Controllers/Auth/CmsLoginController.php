<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CmsUser;
use App\Support\CmsLoginOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

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
        $credentials = $request->validate(['email' => 'required|email', 'password' => 'required|string']);
        $key = 'cms-login:' . Str::lower($credentials['email']) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['email' => 'Too many attempts. Try again in ' . RateLimiter::availableIn($key) . ' seconds.'])->withInput($request->only('email', 'remember'));
        }
        $guard = Auth::guard('cms');
        if (!$guard->validate($credentials)) {
            RateLimiter::hit($key, 60);
            return back()->withErrors(['email' => 'Email atau password salah.'])->withInput($request->only('email', 'remember'));
        }
        RateLimiter::clear($key);
        $user = $guard->getLastAttempted();
        if (!$user->is_active) {
            $request->session()->forget('cms_login_otp');
            return back()->withErrors(['email' => 'Account is deactivated.'])->withInput($request->only('email', 'remember'));
        }
        if (!$user->otp_enabled) {
            return $this->completeLogin($request, $user, $request->boolean('remember'));
        }
        $pending = $request->session()->get('cms_login_otp', []);
        if (($pending['user_id'] ?? null) !== $user->id || !CmsLoginOtp::pendingUser($pending)) {
            $request->session()->forget('cms_login_otp');
            $signature = CmsLoginOtp::send($user);
            $request->session()->regenerate(true);
            $pending = ['user_id' => $user->id, 'signature' => $signature];
        }
        $pending['remember'] = $request->boolean('remember');
        $request->session()->put('cms_login_otp', $pending);
        return redirect()->route('cms.login.otp');
    }

    public function showOtp(Request $request)
    {
        if (Auth::guard('cms')->check()) {
            return redirect('/admin/home');
        }
        $pending = $request->session()->get('cms_login_otp', []);
        $user = CmsLoginOtp::pendingUser($pending);
        if (!$user) {
            return $this->restartLogin($request);
        }
        return view('auth.cms-login-otp', ['email' => $user->email, 'minutes' => CmsLoginOtp::LIFETIME_MINUTES, 'remember' => $pending['remember'] ?? false]);
    }

    public function resendOtp(Request $request)
    {
        $pending = $request->session()->get('cms_login_otp', []);
        $user = CmsLoginOtp::pendingUser($pending);
        if (!$user || Auth::guard('cms')->check()) {
            return $this->restartLogin($request);
        }
        $pending['signature'] = CmsLoginOtp::send($user);
        $request->session()->put('cms_login_otp', $pending);
        return redirect()->route('cms.login.otp')->with('status', 'A new code has been sent to your email.');
    }

    public function verifyOtp(Request $request)
    {
        $pending = $request->session()->get('cms_login_otp', []);
        $request->validate(['otp' => 'required|string|regex:/^[0-9]{6}$/D']);
        if (Auth::guard('cms')->check()) {
            return redirect('/admin/home');
        }
        $user = CmsLoginOtp::verify($pending, $request->input('otp'));
        if ($user) {
            $remember = $request->has('remember') ? $request->boolean('remember') : ($pending['remember'] ?? false);
            return $this->completeLogin($request, $user, $remember);
        }
        if (!CmsLoginOtp::pendingUser($pending)) {
            return $this->restartLogin($request);
        }
        return redirect()->route('cms.login.otp')->withErrors(['otp' => 'Incorrect code. Enter the latest code sent to your email.'])->withInput($request->only('remember'));
    }

    private function restartLogin(Request $request)
    {
        $request->session()->forget('cms_login_otp');
        return redirect()->route('cms.login')->withErrors(['email' => 'This code is no longer valid. Please sign in again.']);
    }

    private function completeLogin(Request $request, CmsUser $user, bool $remember)
    {
        $request->session()->forget('cms_login_otp');
        Auth::guard('cms')->login($user, $remember);
        $request->session()->regenerateToken();
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
