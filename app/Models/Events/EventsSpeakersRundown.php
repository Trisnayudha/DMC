<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventsSpeakersRundown extends Model
{
    use HasFactory;
    protected $table = 'events_speakers';
    protected $casts = [
        'id' => 'int',
        'event_rundown_id',
        'event_speakers_id'
    ];
    protected $fillable = [
        'event_rundown_id',
        'event_speakers_id'
    ];
}
