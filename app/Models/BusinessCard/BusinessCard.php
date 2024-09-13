<?php

namespace App\Models\BusinessCard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessCard extends Model
{
    use HasFactory;

    // Nama tabel jika berbeda dari default (business_cards)
    protected $table = 'business_card';

    // Tentukan field yang boleh diisi secara massal
    protected $fillable = [
        'company',
        'name',
        'job_title',
        'email',
        'mobile',
    ];
}
