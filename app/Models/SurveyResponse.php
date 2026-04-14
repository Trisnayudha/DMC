<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    protected $fillable = [

        // lama
        'email',
        'informative_score',
        'most_relevant_presentations',
        'is_member',
        'wants_more_info',
        'feedback',
        'topics_2026',

        // baru
        'event_code',
        'event_rating',
        'rating_content',
        'rating_networking',
        'liked_most',
        'improvement_feedback',
        'topic_recommendation',
        'app_activated',

        'ip',
        'ua',
    ];

    protected $casts = [
        'most_relevant_presentations' => 'array',
        'is_member' => 'boolean',
        'wants_more_info' => 'boolean',
        'event_rating' => 'integer',
        'rating_content' => 'integer',
        'rating_networking' => 'integer',
    ];
}
