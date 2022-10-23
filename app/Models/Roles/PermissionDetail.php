<?php

namespace App\Models\Roles;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class PermissionDetail extends Model
{
    use HasFactory;
    protected $table = 'permission_details';

    protected $fillable = [
        'alias', 'permission_id', 'group_id'
    ];

    public function group()
    {
        return $this->belongsTo(PermissionGroup::class);
    }

    public function permission()
    {
        return $this->hasOne(Permission::class, 'id', 'permission_id');
    }
}
