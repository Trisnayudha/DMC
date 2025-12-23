<?php

namespace App\Http\Controllers;

use App\Models\DmcSponsorSurvey;
use Illuminate\Http\Request;

class SponsorSurveyController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            // === Basic Identity (tetap dipakai) ===
            'email'    => 'required|email',
            'name'     => 'required|string|max:255',
            'company'  => 'required|string|max:255',

            // === Quick Feedback Questions ===
            'program_familiarity'    => 'required|string',
            'branding_value'         => 'required|string',
            'brand_visibility'       => 'required|string',
            'team_support'           => 'required|string',
            'renewal_interest'       => 'required|string',
            'improvement_suggestion' => 'required|string',
        ]);

        DmcSponsorSurvey::create($validated);

        return back()->with('ok', true);
    }
}
