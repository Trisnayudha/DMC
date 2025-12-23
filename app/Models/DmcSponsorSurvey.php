<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DmcSponsorSurvey extends Model
{
    protected $table = 'dmc_sponsor_surveys';

    protected $fillable = [
        'email',
        'name',
        'company',
        'program_familiarity',
        'branding_value',
        'brand_visibility',
        'team_support',
        'renewal_interest',
        'improvement_suggestion',
    ];
}
