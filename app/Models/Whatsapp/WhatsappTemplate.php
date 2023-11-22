<?php

namespace App\Models\Whatsapp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappTemplate extends Model
{
    use HasFactory;
    protected $table = 'wa_template';
    protected $fillable = [
        'text',
        'image',
    ];
}
