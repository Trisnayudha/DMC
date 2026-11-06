<?php

namespace App\Services\Mailchimp;

use Illuminate\Support\Collection;

class UnsubscribeCampaignStatistics
{
    public function calculate(Collection $contacts): array
    {
        $total = $contacts->count();
        $campaigns = [];
        $admin = 0;
        $unknown = 0;

        foreach ($contacts as $contact) {
            if ($contact->is_admin_unsub) {
                $admin++;
                continue;
            }
            $id = trim((string) $contact->unsub_campaign_id);
            $title = trim((string) $contact->unsub_campaign_title);
            if ($id === '' && $title === '') {
                $unknown++;
                continue;
            }
            $key = $id !== '' ? 'id:' . $id : 'title:' . $title;
            if (!isset($campaigns[$key])) {
                $campaigns[$key] = ['id' => $id, 'title' => $title ?: 'Campaign ' . $id, 'count' => 0];
            }
            $campaigns[$key]['count']++;
        }

        $ranked = collect(array_values($campaigns))->sort(function ($a, $b) {
            return ($b['count'] <=> $a['count']) ?: strcasecmp($a['title'], $b['title']);
        })->values()->map(function ($campaign) use ($total) {
            $campaign['percentage'] = $total ? round($campaign['count'] * 100 / $total, 1) : 0;
            return $campaign;
        });

        return [
            'total' => $total,
            'attributed' => $total - $admin - $unknown,
            'admin' => $admin,
            'unknown' => $unknown,
            'campaigns' => $ranked->take(5),
        ];
    }
}
