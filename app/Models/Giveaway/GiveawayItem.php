<?php

namespace App\Models\Giveaway;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiveawayItem extends Model
{
    protected $fillable = [
        'name',
        'is_rare',
        'total_qty',
        'remaining_qty',
        'base_weight'
    ];
}
