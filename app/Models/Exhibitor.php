<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exhibitor extends Model
{
    use HasFactory;

    protected $table = 'miningindo';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'name',
        'country',
        'desc',
        'category1',
        'category2',
        'website',
        'contact',
        'contact_email',
        'venue_hall',
        'pavilion',
        'raw_json'
    ];

    protected $casts = [
        'raw_json' => 'array',
    ];
}
