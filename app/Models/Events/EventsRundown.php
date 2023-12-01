<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventsRundown extends Model
{
    use HasFactory;
    protected $table = 'events_rundown';
    protected $casts = [
        'id' => 'int',
        'events_id'
    ];
    protected $fillable = [
        'name',
        'date',
        'events_id',
    ];

    public function speakers()
    {
        return $this->belongsToMany(EventsSpeakersRundown::class, 'events_speakers_rundown', 'events_rundown_id', 'events_speakers_id');
    }
}
