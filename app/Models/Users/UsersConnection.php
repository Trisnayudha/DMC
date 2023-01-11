<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersConnection extends Model
{
    use HasFactory;

    protected $table = 'users_connection';
    protected $casts = [
        'id' => 'int',
        'users_id' => 'int',
        'users_id_target' => 'int',
    ];
    protected $fillable = [
        'users_id',
        'users_id_target'
    ];
}
