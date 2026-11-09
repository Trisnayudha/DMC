<?php

namespace App\Services;

use App\Models\DmcMemberSurvey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Autosave for the member survey.
 *
 * Each respondent gets a random token in a cookie. While they fill the form,
 * every change is saved to one dmc_member_surveys row (status in_progress)
 * keyed by that token, so an unfinished survey is backed up in the database
 * and can be resumed later from the same browser. Submitting the survey turns
 * that same row into status completed.
 */
class MemberSurveyDraftService
{
    public const COOKIE = 'dmc_member_survey_draft';
    private const COOKIE_MINUTES = 60 * 24 * 60; // 60 days

    private const SHORT_TEXT = ['full_name', 'company', 'position', 'email', 'linkedin', 'event_types_other', 'event_improvement_other', 'nominee_name', 'nominee_company', 'nominee_reason', 'email_primary_goal_other'];
    private const LONG_TEXT = ['topics_interest', 'speaker_wishlist', 'communication_feedback', 'additional_feedback'];

    // Columns that are NOT NULL in the table, so a half-filled draft stores '' instead of null
    private const REQUIRED_COLUMNS = ['full_name', 'company', 'email'];

    private static $enabled = null;

    /**
     * Autosave needs the columns from the 2026_11_09 migration. Until it runs,
     * drafts are skipped and submit falls back to the old behaviour.
     */
    public function enabled(): bool
    {
        if (self::$enabled === null) {
            self::$enabled = Schema::hasColumn('dmc_member_surveys', 'draft_token');
        }

        return self::$enabled;
    }

    /**
     * Read the respondent's token from the cookie, or issue a new one.
     */
    public function resolveToken(Request $request): string
    {
        $token = $this->tokenFrom($request) ?: Str::random(40);
        Cookie::queue(self::COOKIE, $token, self::COOKIE_MINUTES);

        return $token;
    }

    public function tokenFrom(Request $request): ?string
    {
        $token = $request->cookie(self::COOKIE);

        return is_string($token) && preg_match('/^[A-Za-z0-9]{40}$/', $token) ? $token : null;
    }

    public function forgetToken(): void
    {
        Cookie::queue(Cookie::forget(self::COOKIE));
    }

    public function find(?string $token): ?DmcMemberSurvey
    {
        if (!$token || !$this->enabled()) {
            return null;
        }

        return DmcMemberSurvey::inProgress()->where('draft_token', $token)->first();
    }

    /**
     * Lenient rules: nothing is required, but values must still be valid options.
     */
    public function rules(): array
    {
        $rules = ['last_step' => 'nullable|integer|min:1|max:' . DmcMemberSurvey::TOTAL_STEPS, 'phone' => 'nullable|string|max:50'];

        foreach (self::SHORT_TEXT as $field) {
            $rules[$field] = 'nullable|string|max:255';
        }
        foreach (self::LONG_TEXT as $field) {
            $rules[$field] = 'nullable|string|max:5000';
        }
        foreach (DmcMemberSurvey::OPTIONS as $field => $options) {
            $rules[$field] = 'nullable|array';
            $rules[$field . '.*'] = ['string', Rule::in($options)];
        }

        return $rules;
    }

    /**
     * Create or update the respondent's in-progress row. Returns null when the
     * token already belongs to a submitted survey (nothing left to autosave).
     */
    public function save(string $token, array $input, Request $request): ?DmcMemberSurvey
    {
        if (!$this->enabled()) {
            return null;
        }

        $draft = $this->find($token);

        if (!$draft) {
            if (DmcMemberSurvey::where('draft_token', $token)->exists()) {
                return null;
            }
            $draft = new DmcMemberSurvey(['draft_token' => $token, 'status' => DmcMemberSurvey::STATUS_IN_PROGRESS]);
        }

        $draft->fill($this->normalize($input));
        $draft->last_step = max((int) $draft->last_step, (int) ($input['last_step'] ?? 1));
        $draft->ip_address = $request->ip();
        $draft->user_agent = substr($request->userAgent() ?? '', 0, 255);
        $draft->save();

        return $draft;
    }

    /**
     * Turn the respondent's draft (if any) into the submitted survey.
     */
    public function complete(?string $token, array $data): DmcMemberSurvey
    {
        if (!$this->enabled()) {
            return DmcMemberSurvey::create($data);
        }

        $survey = $this->find($token) ?: new DmcMemberSurvey();

        $survey->fill($data);
        $survey->status = DmcMemberSurvey::STATUS_COMPLETED;
        $survey->last_step = DmcMemberSurvey::TOTAL_STEPS;
        $survey->completed_at = now();
        $survey->save();

        return $survey;
    }

    /**
     * Field values in the shape the form uses (multiple-choice as arrays),
     * so the page can restore them.
     */
    public function toFormState(DmcMemberSurvey $draft): array
    {
        $fields = [];

        foreach (array_merge(self::SHORT_TEXT, self::LONG_TEXT, ['phone']) as $field) {
            $fields[$field] = (string) $draft->{$field};
        }
        foreach (array_keys(DmcMemberSurvey::OPTIONS) as $field) {
            $fields[$field] = array_values(array_filter((array) $draft->{$field}));
        }

        return ['fields' => $fields, 'last_step' => (int) $draft->last_step ?: 1];
    }

    /**
     * Map the posted form onto columns. The whole form is sent on every save,
     * so a missing multiple-choice field means nothing is ticked.
     */
    private function normalize(array $input): array
    {
        $data = [];

        foreach (array_merge(self::SHORT_TEXT, self::LONG_TEXT, ['phone']) as $field) {
            $value = trim((string) ($input[$field] ?? ''));
            $data[$field] = $value === '' && !in_array($field, self::REQUIRED_COLUMNS, true) ? null : $value;
        }
        foreach (array_keys(DmcMemberSurvey::OPTIONS) as $field) {
            $data[$field] = array_values((array) ($input[$field] ?? []));
        }

        // edm_received is a single string column
        $data['edm_received'] = $data['edm_received'][0] ?? null;

        return $data;
    }
}
