<?php

namespace App\Models\News;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsBookmark extends Model
{
    use HasFactory;
    protected $table = 'news_bookmark';
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
