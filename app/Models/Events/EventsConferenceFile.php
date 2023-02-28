<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventsConferenceFile extends Model
{
    use HasFactory;
    protected $table = 'events_conferen_file';
    protected $casts = ['id' => 'int'];
    protected $fillable = [
        'file',
        'events_conference_id'
    ];
}
