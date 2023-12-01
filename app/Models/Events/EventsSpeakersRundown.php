<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventsSpeakersRundown extends Model
{
    use HasFactory;

    protected $table = 'events_speakers_rundown';
    public $timestamps = true; // Use 'public' to make it accessible

    protected $casts = [
        'id' => 'int',
        'events_rundown_id',
        'events_speakers_id'
    ];

    protected $fillable = [
        'events_rundown_id',
        'events_speakers_id'
    ];
}
