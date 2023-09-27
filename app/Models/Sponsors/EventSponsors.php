<?php

namespace App\Models\Sponsors;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSponsors extends Model
{
    use HasFactory;
    protected $table = 'event_sponsors';
    protected $fillable = [
        'events_id',
        'sponsors_id',
        'code_access',
        'count'
    ];
}
