<?php

namespace App\Models\Users;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserService
{
    private $user;
    private $role;

    public function __construct(User $user, Role $role)
    {
        $this->user = $user;
        $this->role = $role;
    }

    public function browse()
    {
        return $this->user->orderBy('id', 'DESC')->paginate(5);
    }

    public function show($id)
    {
        return $this->user->find($id);
    }

    public function create()
    {
        return $this->role->pluck('name', 'name')->all();
    }

    public function store($payload)
    {
        return $this->user->create($payload);
    }

    public function update($id, $payload)
    {
        $user = $this->user->find($id);
        return $user->fill($payload)->save();
    }

    public function getRole()
    {
        return $this->role->pluck('name', 'name')->all();
    }

    public function delete($id)
    {
        return $this->user->destroy($id);
    }

    public function setSkpdId($id)
    {
        return $this->user->with('profile')->where('id', $id)->get()->pluck('profile')->pluck('skpd_id')[0];
    }

    public function changePassword($id, $payload)
    {
        $user = $this->user->find($id);
        return $user->fill($payload)->save();
    }
    public function countUser()
    {
        return $this->user->count();
    }

    public function verifikasi()
    {
        $user = $this->user->with('profile')->where('users.verif', '0')->orderby('users.id', 'desc')->get();
        $data = [];
        foreach ($user as $key => $value) {
            $data[] = [
                'no' => $key + 1,
                'id' => $value->id,
                'name' => $value->name,
                'image' => $value->profile->image,
                'verif' => $value->verif,
            ];
        }
        return $data;
    }

    public function profile()
    {
        return User::with('profile')->where('users.restpass', '1')->get();
    }

    public function checkrole($id)
    {
        $user = $this->user->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->select('roles.name')->where('users.id', $id)->get();
        return $user;
    }

    public function listbrowse()
    {
        if (Auth::user()->can('admin-dashboard', App\Model::class))
            $db = DB::table('users')
                ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->leftJoin('profiles', 'profiles.user_id', '=', 'users.id')
                ->leftJoin('profile_usahas', 'profile_usahas.user_id', '=', 'users.id')
                ->select(
                    'users.id',
                    'users.name as nama',
                    'users.email',
                    'model_has_roles.role_id',
                    'roles.name as role',
                    'profiles.phone',
                    'profiles.address',
                    'users.verif as verifikasi'
                )->orderBy('id', 'desc')
                ->get();
        elseif (Auth::user()->can('admin-dashboard-byself', App\Model::class)) {
            $db = DB::table('users')
                ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->leftJoin('profiles', 'profiles.user_id', '=', 'users.id')
                ->leftJoin('profile_usahas', 'profile_usahas.user_id', '=', 'users.id')
                ->select(
                    'users.id',
                    'users.name as nama',
                    'users.email',
                    'model_has_roles.role_id',
                    'roles.name as role',
                    'profiles.phone',
                    'profiles.address',
                    'users.verif as verifikasi'
                )->where('roles.name', 'guest')
                ->orderBy('id', 'desc')
                ->get();
        }
        return $db;
    }
}
