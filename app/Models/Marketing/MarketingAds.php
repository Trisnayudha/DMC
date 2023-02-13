<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingAds extends Model
{
    use HasFactory;
    protected $table = 'ads_marketing';
    protected $casts = ['id' => 'int'];
    protected $fillable = [
        'image',
        'location',
        'type',
        'target_id',
        'news_category_id',
        'sort',
    ];
}
