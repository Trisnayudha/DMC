<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\DmcMemberSurvey;

class DmcMemberSurveyController extends Controller
{
    /**
     * Show DMC Member Survey Form
     */
    public function index()
    {
        return view('survey.index'); // blade yg barusan kita rapihin
    }

    /**
     * Store DMC Member Survey Response
     */
    public function store(Request $request)
    {
        // ===== MASTER OPTIONS (BIAR AMAN & KONSISTEN) =====
        $eventTypes = [
            'Workshops',
            'Seminars',
            'Networking Events',
            'Webinars',
            'Other',
        ];

        $socialPlatforms = [
            'LinkedIn',
            'Instagram',
            'Facebook',
            'Twitter',
            'YouTube',
            'WhatsApp',
            'Other',
        ];

        $preferredChannels = [
            'Email',
            'Social media',
            'WhatsApp',
            'Mobile app',
            'Website',
            'Other',
        ];

        // ===== VALIDATION =====
        $data = $request->validate([
            /* =========================
             * 1. MEMBER INFORMATION
             * ========================= */
            'full_name'   => 'required|string|max:255',
            'company'     => 'required|string|max:255',
            'position'    => 'nullable|string|max:255',
            'email'       => 'required|email|max:255',
            'phone'       => 'nullable|string|max:50',
            'linkedin'    => 'nullable|string|max:255',

            /* =========================
             * 2. PROGRAM EXPECTATION
             * ========================= */
            'event_types'   => 'required|array|min:1',
            'event_types.*' => ['string', Rule::in($eventTypes)],

            'topics_interest'   => 'nullable|string',
            'speaker_wishlist'  => 'nullable|string',

            'nominee_name'      => 'nullable|string|max:255',
            'nominee_company'   => 'nullable|string|max:255',

            'email_primary_goal'        => 'nullable|array',
            'email_primary_goal.*'      => 'string',
            'email_primary_goal_other'  => 'nullable|string|max:255',

            'email_best_day'            => 'nullable|array',
            'email_best_day.*'          => 'string',


            'event_improvement' => 'nullable|string',

            /* =========================
             * 3. MARKETING & COMMUNICATION
             * ========================= */
            'social_familiarity'   => 'required|array|min:1',
            'social_familiarity.*' => [
                'string',
                Rule::in([
                    'Very familiar',
                    'Somewhat familiar',
                    'Not familiar',
                ]),
            ],

            'platforms'   => 'nullable|array',
            'platforms.*' => ['string', Rule::in($socialPlatforms)],

            'app_awareness'   => 'required|array|min:1',
            'app_awareness.*' => [
                'string',
                Rule::in([
                    'Yes, both',
                    'Yes, mobile app only',
                    'Yes, website only',
                    'No',
                ]),
            ],


            'usage_frequency'   => 'nullable|array',
            'usage_frequency.*' => [
                'string',
                Rule::in([
                    'Frequently',
                    'Occasionally',
                    'Rarely',
                ]),
            ],


            'preferred_channels'   => 'nullable|array',
            'preferred_channels.*' => ['string', Rule::in($preferredChannels)],

            'communication_feedback' => 'nullable|string',

            /* =========================
             * 4. ADDITIONAL
             * ========================= */
            'additional_feedback' => 'nullable|string',
        ], [
            'event_types.required' => 'Please select at least one event type.',
        ]);

        // ===== METADATA =====
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = substr($request->userAgent() ?? '', 0, 255);

        // ===== SAVE =====
        DmcMemberSurvey::create($data);

        return back()
            ->with('ok', true)
            ->withInput([]);
    }
}
