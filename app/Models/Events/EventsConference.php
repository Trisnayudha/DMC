<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventsConference extends Model
{
    use HasFactory;
    protected $table = 'events_conferen';
    protected $casts = ['id' => 'int'];
    protected $fillable = [
        'sort',
        'events_id',
        'name',
        'slug',
        'date',
        'time_start',
        'time_end',
        'youtube_link',
        'status',
        'image'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($eventConference) {
            // Mendapatkan nilai 'sort' terbesar + 1
            $maxSort = static::max('sort');
            $eventConference->sort = $maxSort + 1;
        });
    }
}
