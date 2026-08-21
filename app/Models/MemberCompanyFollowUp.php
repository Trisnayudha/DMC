<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberCompanyFollowUp extends Model
{
    protected $table = 'member_company_follow_ups';

    const STATUS_NEEDS_FOLLOW_UP = 'needs_follow_up';
    const STATUS_VERIFIED = 'verified';

    protected $fillable = [
        'user_id',
        'previous_company_name',
        'new_company_name',
        'previous_job_title',
        'new_job_title',
        'notes',
        'status',
        'flagged_by_id',
        'flagged_by_name',
        'verified_by_id',
        'verified_by_name',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
