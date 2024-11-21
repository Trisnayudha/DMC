<?php

namespace App\Models\Sponsors;

use App\Models\Sponsors\Sponsor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SponsorPhotoVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'sponsor_id',
        'type',
        'path',
        'description',
    ];

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class);
    }
}
