<?php

namespace App\Models\News;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsViews extends Model
{
    use HasFactory;
    protected $table = 'news_views';
    protected $casts = [
        'id' => 'int',
        'news_id' => 'int',
        'users_id' => 'int'
    ];
    protected $fillable = [
        'news_id',
        'users_id'
    ];
}
