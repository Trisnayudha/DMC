<?php

namespace App\Models\Giveaway;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiveawayLog extends Model
{
    protected $fillable = [
        'visit_id',
        'giveaway_item_id'
    ];
}
