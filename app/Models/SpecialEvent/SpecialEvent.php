<?php

namespace App\Models\SpecialEvent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialEvent extends Model
{
    use HasFactory;
    protected $table = 'special_event';
    protected $fillable = [
        'name',
        'phone',
        'email',
        'company',
        'job_title',
        'status',
        'code_booking',
    ];
}
