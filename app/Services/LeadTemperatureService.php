<?php

namespace App\Services;

use App\Models\MemberLeadFollowUp;
use Illuminate\Support\Facades\DB;

/**
 * Lead temperature (hot/warm/cold) from the SOP scoring checklist — six
 * weighted on/off categories summing to 100, ported from IM-DMU's
 * LeadScoringService:
 *
 *   score_company_title         30  manual — PIC judges company & title fit
 *   score_attendance_history    15  auto   — attended a DMC event (users_event.present)
 *   score_digital_engagement    15  auto   — opened/clicked a Postmark email
 *   score_followup_response     15  manual — prospect replied to sponsor kit / follow-up
 *   score_price_agenda_inquiry  15  manual — asked about price or agenda
 *   score_budget_timeline       10  manual — confirmed budget / timeline / authority
 *
 * HOT 70-100, WARM 40-69, COLD 0-39.
 *
 * The auto categories are append-only facts (an attendance or an email open
 * never un-happens), so recompute() only raises them false → true, and a box
 * the data proves can't be unchecked by hand.
 */
class LeadTemperatureService
{
    const HOT_THRESHOLD = 70;
    const WARM_THRESHOLD = 40;

    const POINTS = [
        'score_company_title'        => 30,
        'score_attendance_history'   => 15,
        'score_digital_engagement'   => 15,
        'score_followup_response'    => 15,
        'score_price_agenda_inquiry' => 15,
        'score_budget_timeline'      => 10,
    ];

    const LABELS = [
        'score_company_title'        => 'Company and title fit',
        'score_attendance_history'   => 'Attendance history',
        'score_digital_engagement'   => 'Digital engagement',
        'score_followup_response'    => 'Follow-up response',
        'score_price_agenda_inquiry' => 'Price or agenda inquiry',
        'score_budget_timeline'      => 'Budget and timeline',
    ];

    const AUTO_FIELDS = [
        'score_attendance_history',
        'score_digital_engagement',
    ];

    public static function recompute(MemberLeadFollowUp $lead): void
    {
        foreach (self::AUTO_FIELDS as $field) {
            if (!$lead->{$field} && self::signalExists($lead, $field)) {
                $lead->{$field} = true;
            }
        }

        $score = 0;
        foreach (self::POINTS as $field => $points) {
            if ($lead->{$field}) {
                $score += $points;
            }
        }

        $lead->temperature_score = $score;
        $lead->temperature = self::levelFromScore($score);
        $lead->save();
    }

    /**
     * Save the checklist from the modal. Auto boxes backed by data stay
     * checked no matter what was sent.
     */
    public static function setFlags(MemberLeadFollowUp $lead, array $checked): void
    {
        foreach (array_keys(self::POINTS) as $field) {
            $lead->{$field} = in_array($field, $checked, true);
        }

        self::recompute($lead);
    }

    /**
     * Bulk update: only the categories in $changes ([field => bool]) are
     * touched, the rest keep their current value. A proven auto box that's
     * set to false is re-checked by recompute().
     */
    public static function applyChanges(MemberLeadFollowUp $lead, array $changes): void
    {
        foreach ($changes as $field => $value) {
            if (array_key_exists($field, self::POINTS)) {
                $lead->{$field} = (bool) $value;
            }
        }

        self::recompute($lead);
    }

    /** Auto fields the lead's data already proves (shown locked in the modal). */
    public static function provenFields(MemberLeadFollowUp $lead): array
    {
        return array_values(array_filter(self::AUTO_FIELDS, function ($field) use ($lead) {
            return self::signalExists($lead, $field);
        }));
    }

    public static function signalExists(MemberLeadFollowUp $lead, string $field): bool
    {
        switch ($field) {
            case 'score_attendance_history':
                return DB::table('users_event')
                    ->where('users_id', $lead->user_id)
                    ->whereNotNull('present')
                    ->exists();

            case 'score_digital_engagement':
                $email = optional($lead->user)->email;
                return $email && DB::table('postmark_callbacks')
                    ->where('recipient', $email)
                    ->whereIn('record_type', ['Open', 'Click'])
                    ->exists();
        }

        return false;
    }

    public static function levelFromScore(int $score): string
    {
        if ($score >= self::HOT_THRESHOLD) {
            return 'hot';
        }

        if ($score >= self::WARM_THRESHOLD) {
            return 'warm';
        }

        return 'cold';
    }
}
