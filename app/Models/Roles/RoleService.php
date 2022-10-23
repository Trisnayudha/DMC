<?php

namespace App\Models\Roles;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService
{
    use HasFactory;
    private $role;
    private $permission;
    private $permission_detail;
    private $permission_group;

    public function __construct(Role $role, Permission $permission, PermissionDetail $permission_detail, PermissionGroup $permission_group)
    {
        $this->role = $role;
        $this->permission = $permission;
        $this->permission_detail = $permission_detail;
        $this->permission_group = $permission_group;
    }

    public function browse()
    {
        return $this->role->orderBy('id', 'ASC')->paginate(5);
    }

    public function show($id)
    {
        return $this->role->find($id);
    }

    public function getPermission()
    {
        return $this->permission_group->with('permissionDetails')->with('permissionDetails.permission')->get();
    }

    public function createRole($payload = [])
    {
        return $this->role->create($payload);
    }

    public function getRolePermission($id)
    {
        return $this->permission->join("role_has_permissions", "role_has_permissions.permission_id", "=", "permissions.id")
            ->where("role_has_permissions.role_id", $id)->get();
    }

    public function setRolePermission($id)
    {
        return DB::table("role_has_permissions")->where("role_has_permissions.role_id", $id)
            ->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')
            ->all();
    }

    public function delete($id)
    {
        return $this->role->destroy($id);
    }
}
