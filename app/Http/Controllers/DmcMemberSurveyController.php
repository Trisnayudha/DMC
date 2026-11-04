<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Helpers\WhatsappApi;
use App\Models\DmcMemberSurvey;

class DmcMemberSurveyController extends Controller
{
    private const WA_GROUP = '120363429875185608@g.us';

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
            'YouTube',
            'WhatsApp Channel',
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
            'event_types_other' => 'nullable|string|max:255',
            'nominee_reason' => 'nullable|string|max:255',
            'event_improvement_aspects' => 'nullable|array',
            'event_improvement_aspects.*' => ['string', Rule::in(['Event Topics & Content', 'Speakers & Presentations', 'Event Format & Duration', 'Networking Opportunities', 'Venue & Facilities', 'Other'])],
            'event_improvement_other' => 'nullable|string|max:255',
            'email_frequency' => 'nullable|array|max:1',
            'email_frequency.*' => ['string', Rule::in(['1 email per week', '2 emails per week', '3 emails per week', 'Only for important announcements and event updates'])],

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
        $survey = DmcMemberSurvey::create($data);

        $this->notifySurveySubmitted($survey);

        return back()
            ->with('ok', true)
            ->withInput([]);
    }

    /**
     * Kirim isi survey ke group WA supaya tim bisa langsung cek jawabannya.
     * Gagal kirim tidak boleh menggagalkan submit.
     */
    private function notifySurveySubmitted(DmcMemberSurvey $survey): void
    {
        try {
            $list = function ($values) {
                $values = array_filter((array) $values);
                return $values ? implode(', ', $values) : '-';
            };
            $text = function ($value) {
                $value = trim((string) $value);
                return $value !== '' ? $value : '-';
            };

            $emailGoal = $list($survey->email_primary_goal);
            if ($survey->email_primary_goal_other) {
                $emailGoal .= ' (' . $survey->email_primary_goal_other . ')';
            }

            $lines = [
                '📝 *NEW MEMBER SURVEY 2027*',
                '',
                '*1. Member Information Update*',
                'Full Name: *' . $text($survey->full_name) . '*',
                'Company / Organization: ' . $text($survey->company),
                'Position / Title: ' . $text($survey->position),
                'Email: ' . $text($survey->email),
                'Mobile Number: ' . $text($survey->phone),
                'LinkedIn / Professional Profile: ' . $text($survey->linkedin),
                '',
                '*2. Program Expectations & Input*',
                'Event types: ' . $list($survey->event_types) . ($survey->event_types_other ? ' (' . $text($survey->event_types_other) . ')' : ''),
                'Topics of interest: ' . $text($survey->topics_interest),
                'Speaker wishlist: ' . $text($survey->speaker_wishlist),
                'Award nominee: ' . $text($survey->nominee_name) . ' / ' . $text($survey->nominee_company),
                'Reason for nomination: ' . $text($survey->nominee_reason),
                'Event improvements: ' . $list($survey->event_improvement_aspects) . ($survey->event_improvement_other ? ' (' . $text($survey->event_improvement_other) . ')' : ''),
                '',
                '*3. Marketing & Communication Awareness*',
                'Social media familiarity: ' . $list($survey->social_familiarity),
                'Platforms followed: ' . $list($survey->platforms),
                'App & website awareness: ' . $list($survey->app_awareness),
                'Usage frequency: ' . $list($survey->usage_frequency),
                'Email primary goal: ' . $emailGoal,
                'Preferred email frequency: ' . $list($survey->email_frequency),
                'Communication suggestions: ' . $text($survey->communication_feedback),
                '',
                '*4. Additional Feedback*',
                $text($survey->additional_feedback),
                '',
                '_Survey ID #' . $survey->id . ' · ' . $survey->created_at->format('d M Y H:i') . '_',
            ];

            $wa = new WhatsappApi();
            $wa->phone   = self::WA_GROUP;
            $wa->message = implode("\n", $lines);
            $wa->WhatsappMessageGroup();
        } catch (\Exception $e) {
            Log::warning('WA member survey notification failed: ' . $e->getMessage());
        }
    }
}
