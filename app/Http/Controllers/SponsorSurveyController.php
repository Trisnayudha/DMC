<?php

namespace App\Http\Controllers;

use App\Models\DmcSponsorSurvey;
use Illuminate\Http\Request;

class SponsorSurveyController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email'                     => 'required|email',
            'name'                      => 'required|string',
            'company'                   => 'required|string',
            'type_of_sponsor'           => 'required|string',

            'promo_benefit_satisfaction'    => 'required|string',
            'promo_benefit_other'           => 'nullable|string',

            'event_attendance_satisfaction' => 'required|string',
            'event_attendance_other'        => 'nullable|string',

            'live_event_branding_benefit'   => 'required|string',
            'live_event_branding_other'     => 'nullable|string',

            'additional_value_satisfaction' => 'required|string',
            'additional_value_other'        => 'nullable|string',

            'price_alignment'               => 'required|string',
            'price_alignment_other'         => 'nullable|string',

            'brand_visibility'              => 'required|string',
            'brand_visibility_other'        => 'nullable|string',

            'team_responsiveness'           => 'required|string',
            'team_responsiveness_other'     => 'nullable|string',

            'preferred_communication'       => 'required|string',
            'preferred_communication_other' => 'nullable|string',

            'mobile_app_awareness'           => 'required|string',
            'commodity_map_awareness'        => 'required|string',
            'new_program_awareness'          => 'required|string',

            'overall_experience'             => 'required|string',
            'overall_experience_other'       => 'nullable|string',

            'renewal_interest'               => 'required|string',
            'renewal_interest_other'         => 'nullable|string',

            'renewal_reason'                 => 'nullable|string',
            'future_benefit_suggestion'      => 'required|string',
            'overall_experience_suggestion'  => 'required|string',
        ]);

        DmcSponsorSurvey::create($validated);

        return back()->with('ok', true);
    }
}
