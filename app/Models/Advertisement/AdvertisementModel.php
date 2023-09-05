<?php

namespace App\Models\Advertisement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvertisementModel extends Model
{
    use HasFactory;
    protected $table = 'advertisement';
    protected $fillable = [
        'image', 'link', 'type'
    ];
}
