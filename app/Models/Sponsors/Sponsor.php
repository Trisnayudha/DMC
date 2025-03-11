<?php

namespace App\Models\Sponsors;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'image',
        'description',
        'address',
        'office_number',
        'company_website',
        'email',
        'package',
        'slug',
        'status',
        'founded',
        'location_office',
        'employees',
        'company_category',
        'instagram',
        'facebook',
        'linkedin',
        'video',
        'contract_start',
        'contract_end'

    ];
}
