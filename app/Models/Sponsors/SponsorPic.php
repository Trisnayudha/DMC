<?php

namespace App\Models\Sponsors;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SponsorPic extends Model
{
    protected $table = 'sponsors_pic';

    protected $fillable = [
        'sponsor_id',
        'name',
        'title',
        'email',
        'phone',
    ];

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class);
    }
}
