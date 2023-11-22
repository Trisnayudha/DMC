<?php

namespace App\Models\Whatsapp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappSender extends Model
{
    use HasFactory;
    protected $table = 'wa_sender';
    protected $fillable = [
        'phone',
        'api_key',
    ];
}
