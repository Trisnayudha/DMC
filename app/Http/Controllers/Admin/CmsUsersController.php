<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class CmsUsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $admins = User::role('admin')->orderBy('name')->get(['id', 'name', 'email', 'created_at']);

        $roleSummary = Role::withCount('users')->orderBy('name')->get(['id', 'name']);

        return view('admin.cms_users.index', compact('admins', 'roleSummary'));
    }

    public function assign(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'Email tidak ditemukan di database users. Pastikan user sudah terdaftar.',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        if ($user->hasRole('admin')) {
            return back()->with('error', $user->name . ' (' . $user->email . ') sudah memiliki role admin.');
        }

        $user->assignRole('admin');

        return back()->with('success', 'Role admin berhasil diberikan ke ' . ($user->name ?: $user->email) . '.');
    }

    public function revoke(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa mencabut role admin dari akun Anda sendiri.');
        }

        if (!$user->hasRole('admin')) {
            return back()->with('error', 'User ini tidak memiliki role admin.');
        }

        $user->removeRole('admin');

        return back()->with('success', 'Role admin berhasil dicabut dari ' . ($user->name ?: $user->email) . '.');
    }
}
