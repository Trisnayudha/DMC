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
}
