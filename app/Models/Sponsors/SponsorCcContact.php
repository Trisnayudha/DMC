<?php

namespace App\Models\Sponsors;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsSponsorActivity;

class SponsorCcContact extends Model
{
    use LogsSponsorActivity;

    protected $table = 'sponsors_cc_contacts';
    protected $sponsorLogSubject = 'CC Contact';
    protected $fillable = ['sponsor_id', 'salutation', 'name', 'title', 'email', 'phone'];

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class);
    }
}
