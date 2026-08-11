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
        'first_follow_up_at' => 'datetime',
        'second_follow_up_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
