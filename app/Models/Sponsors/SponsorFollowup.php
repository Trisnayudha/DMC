<?php

namespace App\Models\Sponsors;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SponsorFollowup extends Model
{
    protected $table = 'sponsor_followups';

    protected $fillable = [
        'sponsor_id',
        'renewal_year',
        'followed_up_at',
        'channel',
        'notes',
        'kmk_rate',
        'proof_path',
        'created_by',
    ];

    protected $casts = [
        'followed_up_at' => 'date',
    ];

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class, 'sponsor_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
