<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MemberLeadFollowUp;
use App\Services\LeadTemperatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Bulk actions on the Lead Follow-Up page for leads ticked in the table:
 * log the sponsor kit / follow-up for many leads at once, and set
 * temperature categories for many leads at once.
 */
class MemberLeadBulkController extends Controller
{
    public function followUp(Request $request)
    {
        $request->validate([
            'ids'     => 'required|array|min:1',
            'ids.*'   => 'integer',
            'step'    => 'nullable|in:sponsorkit,follow_up_1,follow_up_2',
            'date'    => 'nullable|date',
            'channel' => 'nullable|in:call,whatsapp,email,other',
            'notes'   => 'nullable|string|max:2000',
        ]);

        $date  = $request->filled('date') ? Carbon::parse($request->date) : now();
        $admin = auth()->user();

        $logged  = [];
        $skipped = 0;

        foreach (MemberLeadFollowUp::whereIn('id', $request->ids)->get() as $lead) {
            if (!$this->canLog($lead, $request->step)) {
                $skipped++;
                continue;
            }

            $stepKey = $lead->logNextStep($date, $request->channel, $request->notes, $admin);
            $label = MemberLeadFollowUp::stepLabel($stepKey);
            $logged[$label] = ($logged[$label] ?? 0) + 1;
        }

        return response()->json([
            'success' => count($logged) > 0,
            'message' => $this->summary($logged, $skipped),
        ], count($logged) > 0 ? 200 : 422);
    }

    public function temperature(Request $request)
    {
        $fields = array_keys(LeadTemperatureService::POINTS);

        $request->validate([
            'ids'       => 'required|array|min:1',
            'ids.*'     => 'integer',
            'changes'   => 'required|array|min:1',
            'changes.*' => 'boolean',
        ]);

        $changes = array_intersect_key($request->input('changes'), array_flip($fields));
        if (!$changes) {
            return response()->json(['success' => false, 'message' => 'Pick at least one category to change.'], 422);
        }

        $leads = MemberLeadFollowUp::with('user')->whereIn('id', $request->ids)->get();
        foreach ($leads as $lead) {
            LeadTemperatureService::applyChanges($lead, $changes);
        }

        return response()->json([
            'success' => true,
            'message' => 'Temperature updated for ' . $leads->count() . ' lead(s).',
        ]);
    }

    /**
     * Pending leads with a step left. When a specific step was picked, only
     * leads whose next step is that one — so "Send Sponsor Kit" never
     * accidentally logs Follow-up 1 on a lead that already got the kit.
     */
    private function canLog(MemberLeadFollowUp $lead, ?string $step): bool
    {
        $next = $lead->nextStepKey();

        return $lead->result === MemberLeadFollowUp::RESULT_PENDING
            && $next
            && (!$step || $next === $step);
    }

    private function summary(array $logged, int $skipped): string
    {
        $parts = [];
        foreach ($logged as $label => $count) {
            $parts[] = $label . ': ' . $count;
        }

        $message = $parts ? 'Logged — ' . implode(', ', $parts) . '.' : 'Nothing logged.';
        if ($skipped) {
            $message .= ' ' . $skipped . ' lead(s) skipped (not pending, Do Not Send, already done, or next step differs).';
        }

        return $message;
    }
}
