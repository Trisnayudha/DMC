<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Helpers\WhatsappApi;
use App\Models\DmcMemberSurvey;
use App\Services\MemberSurveyDraftService;

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
     * Member survey form. Restores the respondent's autosaved draft (via cookie)
     * unless we are showing the thank-you page or re-rendering validation errors.
     */
    public function show(Request $request, MemberSurveyDraftService $drafts)
    {
        $token = $drafts->resolveToken($request);
        $draft = session('ok') || $request->session()->hasOldInput() ? null : $drafts->find($token);

        return view('survey.member', [
            'draftState' => $draft ? $drafts->toFormState($draft) : null,
        ]);
    }

    /**
     * Store DMC Member Survey Response
     */
    public function store(Request $request, MemberSurveyDraftService $drafts)
    {
        // ===== MASTER OPTIONS (BIAR AMAN & KONSISTEN) =====
        $eventTypes = DmcMemberSurvey::OPTIONS['event_types'];
        $socialPlatforms = DmcMemberSurvey::OPTIONS['platforms'];

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
            'event_types_other' => [Rule::requiredIf(in_array('Other', (array) $request->input('event_types', []), true)), 'nullable', 'string', 'max:255'],
            'nominee_reason' => 'nullable|string|max:255',
            'event_improvement_aspects' => 'nullable|array',
            'event_improvement_aspects.*' => ['string', Rule::in(['Program Topics & Content', 'Speakers & Presentation Quality', 'Program Format & Duration', 'Networking Opportunities', 'Venue & Facilities', 'Other'])],
            'event_improvement_other' => [Rule::requiredIf(in_array('Other', (array) $request->input('event_improvement_aspects', []), true)), 'nullable', 'string', 'max:255'],
            'email_frequency' => 'nullable|array|max:1',
            'email_frequency.*' => ['string', Rule::in(['1 email per week', '2 emails per week', '3 emails per week', 'Only for important announcements and event updates'])],

            'topics_interest'   => 'nullable|string',
            'speaker_wishlist'  => 'nullable|string',

            'nominee_name'      => 'nullable|string|max:255',
            'nominee_company'   => 'nullable|string|max:255',

            'email_primary_goal'        => 'nullable|array',
            'email_primary_goal.*'      => 'string',
            'email_primary_goal_other'  => [Rule::requiredIf(in_array('Other', (array) $request->input('email_primary_goal', []), true)), 'nullable', 'string', 'max:255'],

            'email_best_day'            => 'nullable|array',
            'email_best_day.*'          => 'string',


            'event_improvement' => 'nullable|string',

            /* =========================
             * 3. MARKETING & COMMUNICATION
             * ========================= */
            'edm_received' => 'required|array|size:1',
            'edm_received.*' => ['string', Rule::in(['Yes', 'No', 'Not sure'])],

            'platforms' => ['nullable', 'array', function ($attribute, $value, $fail) {
                if (is_array($value) && in_array("I don't follow any", $value, true) && count($value) > 1) {
                    $fail("I don't follow any cannot be selected with other platforms.");
                }
            }],
            'platforms.*' => ['string', Rule::in($socialPlatforms)],

            'app_awareness'   => 'required|array|min:1',
            'app_awareness.*' => ['string', Rule::in(DmcMemberSurvey::OPTIONS['app_awareness'])],


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
        $data['edm_received'] = $data['edm_received'][0];
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = substr($request->userAgent() ?? '', 0, 255);

        // ===== SAVE =====
        // Completes the respondent's autosaved draft row, or creates a new one
        $survey = $drafts->complete($drafts->tokenFrom($request), $data);
        $drafts->forgetToken();

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
                '*2. Program Expectations & Feedback*',
                'Program types: ' . $list($survey->event_types) . ($survey->event_types_other ? ' (' . $text($survey->event_types_other) . ')' : ''),
                'Topics of interest: ' . $text($survey->topics_interest),
                'Speaker wishlist: ' . $text($survey->speaker_wishlist),
                'Award nominee: ' . $text($survey->nominee_name) . ' / ' . $text($survey->nominee_company),
                'Reason for nomination: ' . $text($survey->nominee_reason),
                'Event improvements: ' . $list($survey->event_improvement_aspects) . ($survey->event_improvement_other ? ' (' . $text($survey->event_improvement_other) . ')' : ''),
                '',
                '*3. Marketing & Communications Awareness*',
                'Still receives DMC emails (EDM): ' . $text($survey->edm_received),
                'Preferred email frequency: ' . $list($survey->email_frequency),
                'Email primary goal: ' . $emailGoal,
                'App & website awareness / usage: ' . $list($survey->app_awareness),
                'Platforms followed: ' . $list($survey->platforms),
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
