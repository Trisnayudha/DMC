<?php

namespace App\Models\Sponsors;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SponsorAddress extends Model
{
    use \App\Traits\LogsSponsorActivity;

    protected $sponsorLogSubject = 'Address';

    use HasFactory;
    protected $table = 'sponsors_address';
}
