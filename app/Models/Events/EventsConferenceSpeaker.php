<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventsConferenceSpeaker extends Model
{
    use HasFactory;
    protected $table = 'events_conferen_speaker';
    protected $casts = ['id' => 'int'];
    protected $fillable = [
        'events_conference_id',
        'events_speaker_id'
    ];
}
