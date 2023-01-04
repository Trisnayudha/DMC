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
        'events_id' => 'int',
        'users_id' => 'int'
    ];
    protected $fillable = [
        'events_id',
        'users_id'
    ];
}
