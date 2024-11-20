<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'contact_id',
        'display_name',
        'avatar_url',
        'bio',
        'country_name',
        'flourish_text',
        'job_title',
        'company_display_name',
    ];
}
