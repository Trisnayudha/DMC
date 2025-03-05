<?php

namespace App\Models\Sponsors;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialMediaEngagement extends Model
{
    use HasFactory;

    protected $fillable = [
        'sponsor_id',
        'activity_type',
        'platform',
        'activity_date',
        'screenshot',
    ];

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class);
    }
}
