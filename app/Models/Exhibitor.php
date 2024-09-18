<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exhibitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country',
        'desc',
        'website',
        'contact',
        'contact_email',
        'display_email',
        'venue_hall',
        'event_name',
        'exhibitor_logo',
        'booth_number',
        'category1',
        'category2'
    ];
}
