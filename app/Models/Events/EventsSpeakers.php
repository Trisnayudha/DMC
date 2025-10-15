<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventsSpeakers extends Model
{
    use HasFactory;
    protected $table = 'events_speakers';
    protected $casts = [
        'id' => 'int',
    ];
    protected $fillable = [
        'name',
        'job_title',
        'company',
        'image'
    ];

    public function rundowns()
    {
        return $this->belongsToMany(
            \App\Models\Events\EventsRundown::class,
            'events_speakers_rundown', // pivot table
            'events_speakers_id',      // foreign key ke speakers
            'events_rundown_id'        // foreign key ke rundown
        )->withTimestamps();
    }
}
