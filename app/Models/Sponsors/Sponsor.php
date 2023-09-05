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
        'status',

    ];
}
