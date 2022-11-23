<?php

namespace App\Models\News;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;
    protected $table = 'news';
    protected $casts = ['id' => 'int'];
    protected $fillable = [
        'highlight',
        'all_highlight',
        'title',
        'slug',
        'news_category_id',
        'image',
        'location',
        'date_news',
        'desc',
        'views',
        'share',
        'reference_link'
    ];
}
