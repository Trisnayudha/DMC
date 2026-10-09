<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Guards GET /api/lead-followup-reminders — the read-only feed the separate wa-gateway app polls
 * to remind the sales WA group about sponsor leads (member_lead_follow_ups). A bare shared-secret
 * bearer token: LEAD_REMINDER_API_KEY here, DMC_REMINDER_API_KEY on wa-gateway.
 */
class VerifyLeadReminderKey
{
    public function handle(Request $request, Closure $next)
    {
        $expected = config('services.lead_reminder.key');

        if (blank($expected)) {
            // Refuse to run open — an unset key must never mean "anyone can read lead contacts".
            return response()->json(['message' => 'Lead reminder feed is not configured.'], 503);
        }

        $token = $request->bearerToken();

        if (!$token || !hash_equals($expected, $token)) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        return $next($request);
    }
}
