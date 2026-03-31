<?php

namespace App\Models\Sponsors;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'image',
        'description',
        'address',
        'office_number',
        'company_website',
        'email',
        'package',
        'slug',
        'status',
        'founded',
        'location_office',
        'employees',
        'company_category',
        'instagram',
        'facebook',
        'linkedin',
        'video',
        'contract_start',
        'contract_end'

    ];

    // Di App\Models\Sponsor.php
    public function pics()
    {
        return $this->hasMany(SponsorPic::class);
    }
    public function news()
    {
        return $this->hasMany(\App\Models\News\News::class, 'sponsors_id');
    }

    public function renewals()
    {
        return $this->hasMany(\App\Models\Sponsors\SponsorRenewal::class, 'sponsor_id');
    }

    public function currentRenewal()
    {
        return $this->hasOne(\App\Models\Sponsors\SponsorRenewal::class, 'sponsor_id')
            ->where('is_current', 1);
    }
}
