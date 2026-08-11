<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationLog extends Model
{
    protected $table = 'verification_logs';

    const RESULT_ACTIVE = 'active';
    const RESULT_DECLINED = 'declined';

    protected $fillable = [
        'user_id',
        'started_at',
        'started_by_id',
        'started_by_name',
        'finished_at',
        'finished_by_id',
        'finished_by_name',
        'result',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Open = started but not yet finished (admin has this member's review in
     * progress right now).
     */
    public function scopeOpen($query)
    {
        return $query->whereNull('finished_at');
    }
}
