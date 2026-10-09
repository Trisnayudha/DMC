<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MemberLeadFollowUp;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * GET /api/lead-followup-reminders — read-only feed the separate wa-gateway app polls to remind
 * the sales WA group about sponsor leads (Admin > Lead Follow-Up, member_lead_follow_ups) whose
 * current step is about to miss, or has missed, its SLA. Only returns leads that need a reminder
 * right now.
 *
 * Reuses the SLA the Lead Follow-Up page already enforces (deadline_at) instead of a second set
 * of rules: Send Sponsor Kit is due 48h after the lead is created, Follow-up 1/2 are due 7 days
 * after the previous step (see MemberLeadFollowUpController::logFollowUp()). Per step:
 *  - stage "due_soon": within 24h of the deadline (for Send Sponsor Kit: 24h after creation)
 *  - stage "overdue":  deadline passed — the hard warning
 * Logging the step moves the lead to its next step (new deadline), so it drops out on its own.
 *
 * Only pending leads of approved members (same approvedMember() scope the page uses); a lead
 * flagged "Do Not Send" has no next step and never shows up. Steps that started before
 * LEAD_REMINDER_SINCE are ignored, so the pre-existing overdue backlog isn't sent.
 *
 * Stateless: wa-gateway records what it sent, keyed on lead_id + trigger_at (the step's
 * deadline) + stage, so re-polling is always safe.
 */
class LeadFollowupReminderController extends Controller
{
    public function index(Request $request)
    {
        $since = config('services.lead_reminder.since');
        if (blank($since)) {
            return response()->json(['message' => 'LEAD_REMINDER_SINCE is not set.'], 503);
        }
        $since = Carbon::parse($since);

        // Stop re-surfacing an overdue step after this long — long enough to cover wa-gateway
        // downtime; past that it's a backlog problem, not something a WA ping fixes.
        $maxOverdueHours = max(24, min(720, (int) $request->query('max_overdue_hours', 168)));

        $now = now();

        $leads = MemberLeadFollowUp::approvedMember()
            ->where('result', MemberLeadFollowUp::RESULT_PENDING)
            // any step that started after the cutoff also touched the row after it
            ->where('member_lead_follow_ups.updated_at', '>=', $since)
            ->with(['user.profile', 'user.company'])
            ->get();

        $items = [];
        foreach ($leads as $lead) {
            $step = $lead->nextStepKey();
            if (!$step) {
                continue;
            }

            if ($step === 'sponsorkit') {
                $startedAt = $lead->created_at;
                $fallbackDeadline = $startedAt->copy()->addHours(48);
            } else {
                $startedAt = $step === 'follow_up_1' ? $lead->sponsorkit_sent_at : $lead->first_follow_up_at;
                $fallbackDeadline = $startedAt->copy()->addDays(7);
            }

            if ($startedAt->lessThan($since)) {
                continue;
            }

            $deadline = $lead->deadline_at ?: $fallbackDeadline;

            if ($now->greaterThanOrEqualTo($deadline)) {
                if ($deadline->diffInHours($now) > $maxOverdueHours) {
                    continue;
                }
                $stage = 'overdue';
            } elseif ($now->greaterThanOrEqualTo($deadline->copy()->subHours(24))) {
                $stage = 'due_soon';
            } else {
                continue;
            }

            $user = $lead->user;
            $profile = optional($user)->profile;
            $phone = optional($profile)->fullphone
                ?: trim((optional($profile)->prefix_phone ?? '') . (optional($profile)->phone ?? ''));
            // fullphone is stored as bare digits (e.g. 6281...) — a leading + makes WhatsApp linkify it
            if ($phone !== '' && ctype_digit($phone)) {
                $phone = '+' . $phone;
            }

            $items[] = [
                'lead_id' => $lead->id,
                'step' => $step,
                'step_label' => MemberLeadFollowUp::stepLabel($step),
                'stage' => $stage,
                'trigger_at' => $deadline->toIso8601String(),
                'deadline_at' => $deadline->toIso8601String(),
                'step_started_at' => $startedAt->toIso8601String(),
                'hours_overdue' => $stage === 'overdue' ? $deadline->diffInHours($now) : 0,
                'hours_left' => $stage === 'due_soon' ? $now->diffInHours($deadline) : 0,
                'name' => optional($user)->name,
                'email' => optional($user)->email,
                'phone' => $phone ?: null,
                'job_title' => optional($profile)->job_title,
                'company' => optional(optional($user)->company)->company_name,
                'pic_name' => $lead->pic_name,
                'url' => route('admin.member_leads.index', ['result' => 'pending', 'search' => optional($user)->email]),
            ];
        }

        usort($items, function ($a, $b) {
            return strcmp($a['deadline_at'], $b['deadline_at']);
        });

        return response()->json([
            'generated_at' => $now->toIso8601String(),
            'since' => $since->toIso8601String(),
            'count' => count($items),
            'data' => $items,
        ]);
    }
}
