<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventsCategory extends Model
{
    use HasFactory;
    protected $table = 'events_category';
    protected $fillable = [
        'category_name'
    ];
}
