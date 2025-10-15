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
        return $this->belongsToMany(
            \App\Models\Events\EventsSpeakers::class,
            'events_speakers_rundown', // pivot table
            'events_rundown_id',       // foreign key ke rundown
            'events_speakers_id'       // foreign key ke speakers
        )->withTimestamps();
    }


    public function event()
    {
        return $this->belongsTo(\App\Models\Events\Events::class, 'events_id');
    }
}
