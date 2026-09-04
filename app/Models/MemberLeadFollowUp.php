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
    ];

    protected $casts = [
        'deadline_at' => 'datetime',
        'sponsorkit_sent_at' => 'datetime',
        'first_follow_up_at' => 'datetime',
        'second_follow_up_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Flow: Kirim Sponsorkit → Follow Up 1 → Follow Up 2. Returns the key of
     * whichever step hasn't happened yet, or null once all three are done.
     * Drives both the row's action button label and which timestamp
     * logStep() fills in next.
     */
    public function nextStepKey(): ?string
    {
        if (!$this->sponsorkit_sent_at) {
            return 'sponsorkit';
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
            'sponsorkit'   => 'Kirim Sponsorkit',
            'follow_up_1'  => 'Follow Up 1',
            'follow_up_2'  => 'Follow Up 2',
        ][$key] ?? $key;
    }
}
