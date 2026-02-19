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
        'desc2',
        'views',
        'share',
        'reference_link'
    ];
    public function partner()
    {
        return $this->belongsTo(\App\Models\News\NewsPartner::class, 'news_partners_id');
    }
    public function sponsor()
    {
        return $this->belongsTo(\App\Models\Sponsors\Sponsor::class, 'sponsors_id');
    }
}
