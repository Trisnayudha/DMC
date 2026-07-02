<?php

namespace App\Models\Sponsors;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'branding_name',
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

    public function renewalForms()
    {
        return $this->hasMany(\App\Models\Sponsors\SponsorRenewalForm::class, 'sponsor_id');
    }

    public function currentRenewal()
    {
        return $this->hasOne(\App\Models\Sponsors\SponsorRenewal::class, 'sponsor_id')
            ->where('is_current', 1);
    }

    public function followups()
    {
        return $this->hasMany(\App\Models\Sponsors\SponsorFollowup::class, 'sponsor_id')
            ->orderBy('followed_up_at');
    }

    public function firstPic()
    {
        return $this->hasOne(SponsorPic::class)->oldest('id');
    }

    public function representatives()
    {
        return $this->hasMany(SponsorRepresentative::class, 'sponsor_id');
    }

    public function billings()
    {
        return $this->hasMany(SponsorBilling::class, 'sponsor_id');
    }

    public function members()
    {
        return $this->belongsToMany(\App\Models\User::class, 'payment', 'sponsor_id', 'member_id')
            ->select(['users.id', 'users.name', 'users.email', 'users.status_member', 'profiles.fullphone'])
            ->leftJoin('profiles', 'profiles.users_id', '=', 'users.id')
            ->distinct();
    }
}
