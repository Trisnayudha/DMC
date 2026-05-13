<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserEditLog extends Model
{
    protected $table = 'user_edit_logs';

    protected $fillable = ['user_id', 'admin_id', 'admin_name', 'changes'];

    protected $casts = ['changes' => 'array'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
