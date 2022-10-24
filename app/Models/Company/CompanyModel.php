<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyModel extends Model
{
    use HasFactory;
    protected $table = 'company';
    protected $fillable = [
        'company_name',
        'company_website',
        'company_category',
        'address',
        'city',
        'portal_code',
        'office_number',
        'country',
        'cci',
        'explore'
    ];
}
