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
        'type_of_sponsor',

        'promo_benefit_satisfaction',
        'promo_benefit_other',

        'event_attendance_satisfaction',
        'event_attendance_other',

        'live_event_branding_benefit',
        'live_event_branding_other',

        'additional_value_satisfaction',
        'additional_value_other',

        'price_alignment',
        'price_alignment_other',

        'brand_visibility',
        'brand_visibility_other',

        'team_responsiveness',
        'team_responsiveness_other',

        'preferred_communication',
        'preferred_communication_other',

        'mobile_app_awareness',
        'commodity_map_awareness',
        'new_program_awareness',

        'overall_experience',
        'overall_experience_other',

        'renewal_interest',
        'renewal_interest_other',

        'renewal_reason',
        'future_benefit_suggestion',
        'overall_experience_suggestion',
    ];
}
