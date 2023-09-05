<?php

namespace App\Services\Sponsors;

use App\Models\Sponsors\Sponsor;

class SponsorService extends Sponsor
{

    public static function getSponsorType($type)
    {
        return Sponsor::where('package', $type)->orderby('id', 'desc')->get();
    }
}
