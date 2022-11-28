<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventsCategoryList extends Model
{
    use HasFactory;
    protected $table = 'event_category_list';
    protected $casts = ['id' => 'int'];
    protected $fillable = [
        'events_id',
        'events_category_id'
    ];
}
