<?php

namespace App\Models\News;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsCategoryList extends Model
{
    use HasFactory;
    protected $table = 'news_category_list';
    protected $fillable = [
        'news_id',
        'news_category_id'
    ];
}
