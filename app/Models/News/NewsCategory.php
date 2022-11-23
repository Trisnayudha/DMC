<?php

namespace App\Models\News;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsCategory extends Model
{
    use HasFactory;
    protected $table = 'news_category';
    protected $casts = ['id' => 'int'];
    protected $fillable = [
        'name_category'
    ];
}
