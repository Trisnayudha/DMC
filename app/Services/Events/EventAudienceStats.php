<?php

namespace App\Services\Events;

use App\Models\Company\CompanyModel;
use App\Models\Profiles\ProfileModel;
use App\Support\CompanyCategoryTier;
use App\Support\JobTierClassifier;
use App\Support\JobTierRole;
use Illuminate\Support\Facades\DB;

/**
 * Company Category Tier & Job Tier of an event's approved registrants —
 * same grouping as the Members page charts (CompanyCategoryTier, JobTierClassifier).
 */
class EventAudienceStats
{
    /** Same palette as the Company Category Tier chart on the Members page. */
    const CATEGORY_PALETTE = ['#1d1d1f', '#8e0f15', '#4b5058', '#3c1016', '#a8896c', '#c4c6ca'];

    public static function forEvent(int $eventId): array
    {
        $rows = self::registrants($eventId);

        return [
            'total'      => $rows->count(),
            'categories' => self::categoryBreakdown($rows),
            'tiers'      => self::tierBreakdown($rows),
        ];
    }

    /** One row per registrant (Paid Off), with their current profile & company. */
    private static function registrants(int $eventId)
    {
        $hasTierColumn = ProfileModel::hasJobTierColumn();

        return DB::table('payment')
            ->join('users', 'users.id', 'payment.member_id')
            ->leftJoin('profiles', 'profiles.id', DB::raw('(SELECT MAX(p2.id) FROM profiles p2 WHERE p2.users_id = users.id)'))
            ->leftJoin('company', 'company.id', 'profiles.company_id')
            ->where('payment.events_id', $eventId)
            ->where('payment.status_registration', 'Paid Off')
            ->get(array_merge(
                ['users.id', 'profiles.job_title', 'company.company_category'],
                $hasTierColumn ? ['profiles.job_tier'] : []
            ))
            ->unique('id')
            ->values();
    }

    private static function categoryBreakdown($rows): array
    {
        $groups = [];
        foreach (CompanyCategoryTier::labels() as $label => $name) {
            $groups[$name] = ['label' => $label, 'name' => $name, 'count' => 0, 'categories' => []];
        }

        foreach ($rows as $row) {
            $category = CompanyModel::categoryLabel($row->company_category);
            $group = CompanyCategoryTier::tierFor($category);
            $groups[$group]['count']++;
            $groups[$group]['categories'][$category] = ($groups[$group]['categories'][$category] ?? 0) + 1;
        }

        $result = [];
        foreach (array_values($groups) as $i => $group) {
            arsort($group['categories']);
            $group['color'] = self::CATEGORY_PALETTE[$i] ?? '#c4c6ca';
            $result[] = $group;
        }

        return $result;
    }

    /** Every registrant gets a tier from their job title (not only active members). */
    private static function tierBreakdown($rows): array
    {
        $tiers = [];
        foreach (JobTierClassifier::TIERS as $tierNo => $meta) {
            $tiers[$tierNo] = [
                'tier'       => $tierNo,
                'label'      => 'T' . $tierNo,
                'roles'      => $meta['roles'],
                'action'     => $meta['action'],
                'color'      => $meta['color'],
                'count'      => 0,
                'roleCounts' => JobTierRole::emptyCounts($tierNo),
            ];
        }

        foreach ($rows as $row) {
            $tier = isset($row->job_tier) ? (int) $row->job_tier : JobTierClassifier::classify($row->job_title);
            $tiers[$tier]['count']++;
            $role = JobTierRole::roleFor($tier, $row->job_title);
            $tiers[$tier]['roleCounts'][$role] = ($tiers[$tier]['roleCounts'][$role] ?? 0) + 1;
        }

        return array_values($tiers);
    }
}
