<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    /** Admin filter value for the first survey rows, which have no event_code. */
    public const LEGACY_FILTER = 'legacy';

    /** Readable names for event codes; unknown codes fall back to the code itself. */
    public const EVENT_LABELS = [
        'DMC18_2026_IEMB2026' => 'Indonesia Energy Market Briefing 2026',
        'DMC17_2026_71ST' => 'The 71st DMC Networking Event',
        'DMC13_2026_MINING_INSIGHTS' => 'Mining Insights (DMC13 2026)',
        'DMC69_2026_MINING_INSIGHTS' => 'Mining Insights (DMC69 2026)',
    ];

    public static function eventLabel($code)
    {
        if (!$code) {
            return 'First Survey (Nov–Dec 2025, no event code)';
        }

        return self::EVENT_LABELS[$code] ?? $code;
    }

    public function isLegacy()
    {
        return !$this->event_code;
    }

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
        'wants_expert_contact',

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
        'wants_expert_contact' => 'boolean',
    ];
}
