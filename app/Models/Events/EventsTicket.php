<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventsTicket extends Model
{
    use HasFactory;
    protected $table = 'events_tickets';
    protected $fillable = [
        'events_id',
        'title',
        'price_rupiah',
        'price_dollar',
        'type',
        'description',
        'status_ticket',
        'status_sold'
    ];
}
