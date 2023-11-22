<?php

namespace App\Models\Whatsapp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappBlast extends Model
{
    use HasFactory;
    protected $table = 'wa_blast';
    protected $fillable = [
        'wa_db_id',
        'wa_temp_id',
        'wa_sender_id',
        'status',
    ];
}
