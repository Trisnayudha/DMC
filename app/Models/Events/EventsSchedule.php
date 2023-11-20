<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventsSchedule extends Model
{
    use HasFactory;
    protected $table = 'events_schedule';
    protected $casts = ['id' => 'int'];
    protected $fillable = [
        'events_id',
        'slug',
        'name',
        'location',
        'date',
        'type',
    ];
}
