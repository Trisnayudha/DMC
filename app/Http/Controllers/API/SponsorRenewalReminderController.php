<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sponsors\Sponsor;
use App\Models\Sponsors\SponsorRenewal;
use App\Models\Sponsors\SponsorRenewalForm;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * GET /api/sponsor-renewal-reminders — read-only feed the separate wa-gateway app polls to remind
 * the finance WA group which sponsors still need their renewal form generated (Step 1 of the
 * Renewal Process on Sponsors Annual Report).
 *
 * A sponsor is listed when:
 *  - it is published (a Not Renewed sponsor is auto-drafted, so it drops out)
 *  - its current contract ends this month or next month, or ended up to `max_overdue_months`
 *    ago (default 3) without a decision
 *  - no renewal form has been generated during the current contract period (a form from the
 *    previous cycle predates contract_start, so it doesn't count)
 *  - no not_renewed record exists for the contract's end year
 * Confirming the renewal moves sponsors.contract_end a year ahead, so it drops out on its own,
 * as does generating the form.
 *
 * Stateless: wa-gateway records what it sent, keyed on sponsor_id + contract_end + stage, so
 * re-polling is always safe. Same bearer key as the lead follow-up reminder feed.
 */
class SponsorRenewalReminderController extends Controller
{
    public function index(Request $request)
    {
        $maxOverdueMonths = max(0, min(12, (int) $request->query('max_overdue_months', 3)));

        $now       = now();
        $thisMonth = $now->format('Y-m');
        $nextMonth = $now->copy()->startOfMonth()->addMonth()->format('Y-m');
        $oldest    = $now->copy()->startOfMonth()->subMonths($maxOverdueMonths)->format('Y-m');

        $sponsors = Sponsor::with(['firstPic', 'currentRenewal'])
            ->where('status', 'publish')
            ->whereNotNull('contract_end')
            ->whereBetween('contract_end', [$oldest, $nextMonth])
            ->orderBy('contract_end')
            ->orderBy('name')
            ->get();

        $items = [];
        foreach ($sponsors as $sponsor) {
            if ($this->hasFormForCurrentContract($sponsor) || $this->isMarkedNotRenewed($sponsor)) {
                continue;
            }

            $endsAt = Carbon::createFromFormat('Y-m', $sponsor->contract_end)->endOfMonth()->startOfDay();
            $stage  = $sponsor->contract_end < $thisMonth
                ? 'overdue'
                : ($sponsor->contract_end === $thisMonth ? 'this_month' : 'next_month');

            $pic     = $sponsor->firstPic;
            $renewal = $sponsor->currentRenewal;

            $items[] = [
                'sponsor_id'     => $sponsor->id,
                'name'           => $sponsor->name,
                'branding_name'  => $sponsor->branding_name,
                'package'        => $sponsor->package,
                'package_label'  => sponsor_package_label($sponsor->package),
                'stage'          => $stage,
                'contract_start' => $sponsor->contract_start,
                'contract_end'   => $sponsor->contract_end,
                'contract_ends_at' => $endsAt->toDateString(),
                'days_left'      => (int) $now->copy()->startOfDay()->diffInDays($endsAt, false),
                'last_amount_usd' => $renewal && $renewal->amount_usd !== null ? (float) $renewal->amount_usd : null,
                'last_amount_idr' => $renewal && $renewal->amount_idr !== null ? (float) $renewal->amount_idr : null,
                'pic_name'       => optional($pic)->name,
                'pic_email'      => optional($pic)->email,
                'pic_phone'      => optional($pic)->phone,
                'url'            => route('sponsors.annual-report', ['year' => (int) substr($sponsor->contract_end, 0, 4)]),
            ];
        }

        return response()->json([
            'generated_at' => $now->toIso8601String(),
            'month'        => $thisMonth,
            'window'       => ['from' => $oldest, 'to' => $nextMonth],
            'count'        => count($items),
            'data'         => $items,
        ]);
    }

    private function hasFormForCurrentContract(Sponsor $sponsor): bool
    {
        $query = SponsorRenewalForm::where('sponsor_id', $sponsor->id);

        if ($sponsor->contract_start) {
            $periodStart = Carbon::createFromFormat('Y-m', $sponsor->contract_start)->startOfMonth()->toDateString();
            $query->where(function ($q) use ($periodStart) {
                $q->where('generated_at', '>=', $periodStart)
                    ->orWhere(function ($q2) use ($periodStart) {
                        $q2->whereNull('generated_at')->where('created_at', '>=', $periodStart);
                    });
            });
        }

        return $query->exists();
    }

    private function isMarkedNotRenewed(Sponsor $sponsor): bool
    {
        return SponsorRenewal::where('sponsor_id', $sponsor->id)
            ->where('renewal_status', 'not_renewed')
            ->where('renewal_year', '>=', (int) substr($sponsor->contract_end, 0, 4))
            ->exists();
    }
}
