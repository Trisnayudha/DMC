<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CmsUsersController extends Controller
{
    public function index()
    {
        $admins = CmsUser::orderBy('name')->get();
        return view('admin.cms_users.index', compact('admins'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:cms_users,email',
            'password' => 'required|string|min:6',
        ]);

        CmsUser::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'is_active' => true,
        ]);

        return back()->with('success', 'Admin user berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $user = CmsUser::findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:cms_users,email,' . $id,
            'password' => 'nullable|string|min:6',
        ]);

        $user->name  = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        return back()->with('success', 'Admin user berhasil diupdate.');
    }

    public function toggleActive($id)
    {
        $user = CmsUser::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa deactivate akun sendiri.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', $user->name . ' berhasil di-' . $status . '.');
    }

    public function destroy($id)
    {
        $user = CmsUser::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $user->delete();
        return back()->with('success', $user->name . ' berhasil dihapus.');
    }
}
