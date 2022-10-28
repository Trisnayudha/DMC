<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegisterEvent extends Model
{
    use HasFactory;
    protected $table = 'register_event';
    protected $fillable = [
        'company_name',
        'name',
        'job_title',
        'phone',
        'email',
        'company_website',
        'company_category',
        'address',
        'country',
        'cci',
        'explore',
        'password'
    ];
}
