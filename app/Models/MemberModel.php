<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberModel extends Model
{
    use HasFactory;
    protected $table = 'xtwp_users_dmc';

    protected $fillable = [
        'prefix',
        'company_name',
        'name',
        'job_title',
        'prefix_phone',
        'phone',
        'fullphone',
        'email',
        'company_website',
        'company_category',
        'company_other',
        'address',
        'country',
        'cci',
        'explore',
        'password',
        'prefix_office_number',
        'office_number',
        'full_office_number',
        'city',
        'portal_code',
        'sponsor_id'
    ];
}
