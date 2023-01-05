<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRegister extends Model
{
    use HasFactory;
    protected $table = 'users_event';
    protected $fillable = [
        'users_id',
        'events_id',
        'payment_id',
        'present'
    ];
}
