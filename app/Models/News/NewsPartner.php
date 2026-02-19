<?php

namespace App\Models\News;

use Illuminate\Database\Eloquent\Model;

class NewsPartner extends Model
{
    protected $table = 'news_partners';

    protected $fillable = [
        'news_id',
        'partner_name',
        'partner_position',
        'partner_company',
        'partner_website',
        'partner_image',
        'partner_quote',
    ];

    public function news()
    {
        return $this->hasMany(\App\Models\News\News::class, 'news_partners_id');
    }
}
