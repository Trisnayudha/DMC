<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventQuestion extends Model
{
    protected $fillable = ['event_slug', 'code_payment', 'name', 'email', 'company', 'question'];
}
