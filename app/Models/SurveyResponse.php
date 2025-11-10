<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    protected $fillable = [
        'email',
        'informative_score',
        'most_relevant_presentations',
        'is_member',
        'wants_more_info',
        'feedback',
        'topics_2026',
        'ip',
        'ua',
    ];

    protected $casts = [
        'most_relevant_presentations' => 'array',
        'is_member' => 'boolean',
        'wants_more_info' => 'boolean',
    ];
}
