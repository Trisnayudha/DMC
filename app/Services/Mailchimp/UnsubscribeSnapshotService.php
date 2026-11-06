<?php

namespace App\Services\Mailchimp;

use App\Http\Controllers\Admin\MailchimpUnsubscribeController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UnsubscribeSnapshotService
{
    private function table()
    {
        return DB::table('mailchimp_unsubscribe_snapshots');
    }

    public function get(string $listId)
    {
        return $this->table()->where('list_id', $listId)->first();
    }

    public function requestSync(string $listId): void
    {
        $this->ensureRow($listId);
        $this->table()->where('list_id', $listId)->update(['requested_at' => now(), 'updated_at' => now()]);
    }

    private function ensureRow(string $listId): void
    {
        $this->table()->insertOrIgnore(['list_id' => $listId, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function removeResubscribed(string $listId, string $email): void
    {
        $this->ensureRow($listId);
        DB::transaction(function () use ($listId, $email) {
            $row = $this->table()->where('list_id', $listId)->lockForUpdate()->first();
            $members = array_values(array_filter(json_decode($row->members ?: '[]', true) ?: [], function ($member) use ($email) {
                return strtolower(trim($member['email_address'] ?? '')) !== $email;
            }));
            $activities = json_decode($row->activities ?: '{}', true) ?: [];
            unset($activities[$email]);
            $resubscribes = json_decode($row->local_resubscribes ?: '{}', true) ?: [];
            $resubscribes[$email] = now()->toIso8601String();
            $this->table()->where('list_id', $listId)->update([
                'members' => json_encode($members), 'activities' => json_encode($activities),
                'local_resubscribes' => json_encode($resubscribes), 'requested_at' => now(), 'updated_at' => now(),
            ]);
        });
    }

    public function syncIfDue(array $config, bool $force = false): bool
    {
        $id = $config['listId'];
        $this->ensureRow($id);
        $token = (string) Str::uuid();
        $started = now()->startOfSecond();
        $claimed = $this->table()->where('list_id', $id)
            ->where(function ($q) { $q->whereNull('started_at')->orWhere('started_at', '<', now()->subHours(2)); })
            ->when(!$force, function ($q) {
                $q->where(function ($q) {
                    $q->whereNotNull('requested_at')->orWhereNull('last_synced_at')->orWhere('last_synced_at', '<=', now()->subMinutes(15));
                });
            })->update(['started_at' => $started, 'sync_token' => $token, 'requested_at' => null, 'updated_at' => now()]);
        if (!$claimed) return false;

        try {
            $controller = app(MailchimpUnsubscribeController::class);
            $old = $this->get($id);
            $oldMembers = collect(json_decode($old->members ?: '[]', true) ?: [])->keyBy(function ($m) {
                return strtolower(trim($m['email_address'] ?? ''));
            });
            $oldActivities = json_decode($old->activities ?: '{}', true) ?: [];
            $retry = json_decode($old->activity_retry ?: '[]', true) ?: [];
            $members = $controller->fetchUnsubscribedMembersFromMailchimp($config);
            $changed = [];
            foreach ($members as $member) {
                $email = strtolower(trim($member['email_address'] ?? ''));
                $previous = $oldMembers->get($email);
                if (!$previous || ($previous['last_changed'] ?? null) !== ($member['last_changed'] ?? null) || !array_key_exists($email, $oldActivities) || in_array($email, $retry, true)) {
                    $changed[] = $member;
                }
            }
            $freshActivities = $controller->fetchActivitiesForMembersFromMailchimp($config, $changed);
            $failedActivities = array_values(array_diff(array_map(function ($m) { return strtolower(trim($m['email_address'] ?? '')); }, $changed), array_keys($freshActivities)));
            $stats = $controller->fetchListStatsFromMailchimp($config);
            DB::transaction(function () use ($id, $token, $started, $members, $oldActivities, $freshActivities, $failedActivities, $stats) {
                $row = $this->table()->where('list_id', $id)->lockForUpdate()->first();
                if (!$row || $row->sync_token !== $token) return;
                $resubscribes = json_decode($row->local_resubscribes ?: '{}', true) ?: [];
                $members = array_values(array_filter($members, function ($member) use ($resubscribes, $started) {
                    $email = strtolower(trim($member['email_address'] ?? ''));
                    return !isset($resubscribes[$email]) || \Carbon\Carbon::parse($resubscribes[$email])->lt($started);
                }));
                $emails = array_flip(array_map(function ($m) { return strtolower(trim($m['email_address'] ?? '')); }, $members));
                $activities = array_intersect_key(array_replace($oldActivities, $freshActivities), $emails);
                $this->table()->where('list_id', $id)->update([
                    'members' => json_encode($members), 'activities' => json_encode($activities),
                    'activity_retry' => json_encode(array_values(array_intersect($failedActivities, array_keys($emails)))),
                    'stats' => json_encode($stats ?: (json_decode($row->stats ?: '{}', true) ?: [])),
                    'local_resubscribes' => json_encode(array_filter($resubscribes, function ($time) use ($started) { return \Carbon\Carbon::parse($time)->gte($started); })),
                    'last_synced_at' => now(), 'started_at' => null, 'sync_token' => null, 'last_error' => null, 'updated_at' => now(),
                ]);
            });
            return true;
        } catch (\Throwable $e) {
            Log::warning('Mailchimp unsubscribe sync failed: ' . $e->getMessage());
            $this->table()->where('list_id', $id)->where('sync_token', $token)->update([
                'started_at' => null, 'sync_token' => null, 'last_error' => mb_substr($e->getMessage(), 0, 1000), 'updated_at' => now(),
            ]);
            return false;
        }
    }
}
