<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberSource extends Model
{
    use HasFactory;

    protected $table = 'member_sources';

    protected $fillable = [
        'category',
        'code',
        'name',
        'form_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
