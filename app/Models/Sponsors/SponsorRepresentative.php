<?php

namespace App\Models\Sponsors;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SponsorRepresentative extends Model
{
    use \App\Traits\LogsSponsorActivity;

    protected $sponsorLogSubject = 'Representative';

    use HasFactory;
    protected $table = 'sponsors_representative';
}
