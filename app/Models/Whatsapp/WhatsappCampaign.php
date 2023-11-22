<?php

namespace App\Models\Whatsapp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappCampaign extends Model
{
    use HasFactory;
    protected $table = 'wa_campaign';
    protected $fillable = [
        'name',
        'date',
    ];
}
