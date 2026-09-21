<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberLeadFollowUp extends Model
{
    protected $table = 'member_lead_follow_ups';

    const RESULT_PENDING = 'pending';
    const RESULT_WIN = 'win';
    const RESULT_LOSS = 'loss';

    protected $fillable = [
        'user_id',
        'pic_id',
        'pic_name',
        'deadline_at',
        'sponsorkit_sent_at',
        'channel',
        'notes',
        'result',
        'first_follow_up_at',
        'second_follow_up_at',
        'created_by_id',
        'created_by_name',
        'do_not_send',
        'do_not_send_reason',
        'do_not_send_by_id',
        'do_not_send_by_name',
        'do_not_send_at',
    ];

    protected $casts = [
        'deadline_at' => 'datetime',
        'sponsorkit_sent_at' => 'datetime',
        'first_follow_up_at' => 'datetime',
        'second_follow_up_at' => 'datetime',
        'do_not_send' => 'boolean',
        'do_not_send_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Flow: Send Sponsor Kit → Follow-up 1 → Follow-up 2. Returns the key of
     * whichever step hasn't happened yet, or null once all three are done.
     * Drives both the row's action button label and which timestamp
     * logStep() fills in next.
     */
    public function nextStepKey(): ?string
    {
        if (!$this->sponsorkit_sent_at) {
            // Flagged as "do not send" (competitor/unqualified) — never
            // surface the Send Sponsor Kit step, even if it's technically next.
            return $this->do_not_send ? null : 'sponsorkit';
        }
        if (!$this->first_follow_up_at) {
            return 'follow_up_1';
        }
        if (!$this->second_follow_up_at) {
            return 'follow_up_2';
        }
        return null;
    }

    public static function stepLabel(string $key): string
    {
        return [
            'sponsorkit'   => 'Send Sponsor Kit',
            'follow_up_1'  => 'Follow-up 1',
            'follow_up_2'  => 'Follow-up 2',
        ][$key] ?? $key;
    }
}
