<?php

namespace App\Models\Email;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostmarkCallback extends Model
{
    use HasFactory;
    protected $fillable = [
        'record_type',
        'message_id',
        'recipient',
        'tag',
        'metadata',
        'payload'
    ];

    protected $casts = [
        'metadata' => 'json',
        'payload'  => 'json'
    ];
}
