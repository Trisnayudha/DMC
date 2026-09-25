<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Events extends Model
{
    use HasFactory;
    protected $table = 'events';
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'image',
        'location',
        'event_category_id',
        'slug',
        'topic',
        'status',
        'type',
        'maps',
        'image_banner',
        'has_giveaway',
    ];

    protected $casts = [
        'has_giveaway' => 'boolean',
    ];

    public function getSubjectNameAttribute()
    {
        return preg_replace('/^The\s+/i', '', $this->name);
    }

    public function partnershipEventVisitors()
    {
        return $this->hasMany(\App\Models\PartnershipEvent\PartnershipEventVisitor::class, 'events_id');
    }
}
