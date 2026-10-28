<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsRole extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'permissions'];
    protected $casts = ['permissions' => 'array'];
}
