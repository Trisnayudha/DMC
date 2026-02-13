<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventsHighlight extends Model
{
    use HasFactory;
    protected $table = 'events_highlight';
    protected $casts = ['id' => 'int'];
    protected $fillable = [
        'events_id',
        'image',
        'sort'
    ];


    public function event()
    {
        return $this->belongsTo(Events::class, 'events_id');
    }
}
