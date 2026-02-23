<?php

// app/Models/Membership/MembershipTierBanner.php

namespace App\Models\Membership;

use Illuminate\Database\Eloquent\Model;

class MembershipTierBanner extends Model
{
    protected $table = 'membership_tier_banners';

    protected $fillable = [
        'tier',
        'section_key',
        'title',
        'image',
        'link_url',
        'open_new_tab',
        'sort_order',
        'is_active',
    ];
}
