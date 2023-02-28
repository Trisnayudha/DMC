<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventsConferenceLog extends Model
{
    use HasFactory;
    protected $table = 'events_conferen_log';
    protected $casts = ['id' => 'int'];
    protected $fillable = [
        'events_id',
        'events_conference_id',
        'users_id'
    ];
}
