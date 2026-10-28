<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsRole;
use App\Models\CmsUser;
use App\Support\CmsAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CmsUsersController extends Controller
{
    public function index()
    {
        return view('admin.cms_users.index', [
            'admins' => CmsUser::orderBy('name')->get(),
            'roles' => CmsUser::ROLES,
            'teamRoles' => array_diff_key(CmsUser::ROLES, ['superadmin' => true]),
            'rolePermissions' => CmsRole::all()->keyBy('key'),
            'permissionModules' => collect(CmsAccess::configurableModules())->groupBy(function ($module) { return $module[1]; }, true),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:cms_users,email',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(array_keys(CmsUser::ROLES))],
        ]);
        CmsUser::create([
            'name' => $request->name, 'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => true, 'role' => $request->role,
        ]);
        return back()->with('success', 'CMS user berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('cms_users', 'email')->ignore($id)],
            'password' => 'nullable|string|min:6',
            'role' => ['required', Rule::in(array_keys(CmsUser::ROLES))],
        ]);
        DB::transaction(function () use ($request, $id) {
            $superadmins = $this->lockSuperadmins();
            $user = CmsUser::lockForUpdate()->findOrFail($id);
            if ($user->role !== $request->role) {
                if ((int) $user->id === (int) auth()->id()) {
                    throw ValidationException::withMessages(['role' => 'Tidak bisa mengubah level akun sendiri. Gunakan Superadmin lain.']);
                }
                $this->protectLastSuperadmin($user, $superadmins->count());
            }
            $user->fill($request->only('name', 'email', 'role'));
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->save();
        });
        return back()->with('success', 'CMS user berhasil diupdate.');
    }

    public function toggleActive($id)
    {
        if ((int) $id === (int) auth()->id()) {
            return back()->with('error', 'Tidak bisa deactivate akun sendiri.');
        }
        DB::transaction(function () use ($id) {
            $superadmins = $this->lockSuperadmins();
            $user = CmsUser::lockForUpdate()->findOrFail($id);
            if ($user->is_active) {
                $this->protectLastSuperadmin($user, $superadmins->count());
            }
            $user->is_active = !$user->is_active;
            $user->save();
        });
        return back()->with('success', 'Status CMS user berhasil diubah.');
    }

    public function destroy($id)
    {
        if ((int) $id === (int) auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }
        DB::transaction(function () use ($id) {
            $superadmins = $this->lockSuperadmins();
            $user = CmsUser::lockForUpdate()->findOrFail($id);
            $this->protectLastSuperadmin($user, $superadmins->count());
            $user->delete();
        });
        return back()->with('success', 'CMS user berhasil dihapus.');
    }

    public function updatePermissions(Request $request)
    {
        $teams = array_diff(array_keys(CmsUser::ROLES), ['superadmin']);
        $rules = ['permissions' => ['sometimes', 'array:' . implode(',', $teams)]];
        foreach ($teams as $team) {
            $rules['permissions.' . $team] = 'sometimes|array';
            $rules['permissions.' . $team . '.*'] = [Rule::in(array_keys(CmsAccess::configurableModules()))];
        }
        $request->validate($rules);
        DB::transaction(function () use ($request, $teams) {
            foreach (CmsRole::whereIn('key', $teams)->orderBy('key')->lockForUpdate()->get() as $role) {
                $role->permissions = array_values(array_unique($request->input('permissions.' . $role->key, [])));
                $role->save();
            }
        });
        return back()->with('success', 'Hak akses menu tiap tim berhasil disimpan. Berlaku pada request berikutnya.');
    }

    private function lockSuperadmins()
    {
        return CmsUser::where('role', 'superadmin')->where('is_active', true)->orderBy('id')->lockForUpdate()->get();
    }

    private function protectLastSuperadmin(CmsUser $user, int $count): void
    {
        if ($user->isSuperadmin() && $user->is_active && $count <= 1) {
            throw ValidationException::withMessages(['role' => 'Minimal harus ada satu Superadmin aktif.']);
        }
    }
}
