<?php

namespace App\Models\Roles;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionGroup extends Model
{
    use HasFactory;
    protected $table = 'permissions_group';

    protected $fillable = [
        'group_name'
    ];

    public function permissionDetails()
    {
        return $this->hasMany(PermissionDetail::class, 'group_id', 'id');
    }
}
