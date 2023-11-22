<?php

namespace App\Models\Whatsapp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappDB extends Model
{
    use HasFactory;
    protected $table = 'wa_db';
    protected $fillable = [
        'name',
        'company_name',
        'job_title',
        'phone',
        'wa_camp_id'
    ];
}
