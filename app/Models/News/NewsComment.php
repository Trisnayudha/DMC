<?php

namespace App\Models\News;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsComment extends Model
{
    use HasFactory;
    protected $table = 'news_comment';
    protected $casts = [
        'id' => 'int',
        'news_id' => 'int',
        'users_id' => 'int'
    ];
    protected $fillable = [
        'news_id',
        'users_id',
        'comment'
    ];
}
