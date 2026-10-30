<?php

namespace App\Models\Sponsors;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSponsors extends Model
{
    use \App\Traits\LogsSponsorActivity;

    protected $sponsorLogSubject = 'Event Sponsor';

    public function sponsorLogSponsorId(): ?int
    {
        return $this->sponsors_id ? (int) $this->sponsors_id : null;
    }

    use HasFactory;
    protected $table = 'event_sponsors';
    protected $fillable = [
        'events_id',
        'sponsors_id',
        'code_access',
        'count'
    ];
}
