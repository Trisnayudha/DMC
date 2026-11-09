<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class DmcMemberSurvey extends Model
{
    protected $table = 'dmc_member_surveys';

    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const TOTAL_STEPS = 4;

    /**
     * Allowed values per multiple-choice field (shared by submit + autosave validation).
     */
    public const OPTIONS = [
        'event_types' => [
            'Seminar/ Workshops/ Training Session',
            'Conference/ Panel Discussion/ Expert Talk',
            'Member Meetup/ Business Networking/ Informal Gatherings',
            'Charity Events/ Community Engagement',
            'Sport Activites/ Festive Celebrations',
            'Other',
        ],
        'event_improvement_aspects' => ['Program Topics & Content', 'Speakers & Presentation Quality', 'Program Format & Duration', 'Networking Opportunities', 'Venue & Facilities', 'Other'],
        'edm_received' => ['Yes', 'No', 'Not sure'],
        'platforms' => ['LinkedIn', 'Instagram', 'Facebook', 'YouTube', 'WhatsApp Channel', "I don't follow any"],
        'app_awareness' => ['Yes, both', 'Yes, mobile app only', 'Yes, website only', 'No'],
        'usage_frequency' => ['Frequently', 'Occasionally', 'Rarely'],
        'email_primary_goal' => [
            'Seeking information on upcoming events (Registration / Agenda)',
            'Obtaining industry news and updates',
            'Looking for networking opportunities',
            'Other',
        ],
        'email_frequency' => ['1 email per week', '2 emails per week', '3 emails per week', 'Only for important announcements and event updates'],
    ];

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        /* =========================
         * 1. MEMBER INFORMATION
         * ========================= */
        'full_name',
        'company',
        'position',
        'email',
        'phone',
        'linkedin',

        /* =========================
         * 2. PROGRAM EXPECTATION
         * ========================= */
        'event_types',
        'event_types_other',
        'nominee_reason',
        'event_improvement_aspects',
        'event_improvement_other',
        'email_frequency',
        'topics_interest',
        'speaker_wishlist',
        'nominee_name',
        'nominee_company',
        'event_improvement',

        /* =========================
         * 3. MARKETING & COMMUNICATION
         * ========================= */
        'social_familiarity',
        'edm_received',
        'platforms',
        'app_awareness',
        'usage_frequency',
        'preferred_channels',

        // === NEW: EMAIL COMMUNICATION PREFERENCES ===
        'email_primary_goal',
        'email_primary_goal_other',
        'email_best_day',

        'communication_feedback',

        /* =========================
         * 4. ADDITIONAL
         * ========================= */
        'additional_feedback',

        /* =========================
         * METADATA
         * ========================= */
        'ip_address',
        'user_agent',

        /* =========================
         * AUTOSAVE / COMPLETION
         * ========================= */
        'status',
        'draft_token',
        'last_step',
        'completed_at',
    ];

    protected $hidden = ['draft_token'];

    /**
     * Cast attributes
     * (WAJIB untuk field checkbox / multiple choice)
     */
    protected $casts = [
        'event_types'         => 'array',
        'event_improvement_aspects' => 'array',
        'email_frequency' => 'array',
        'social_familiarity'  => 'array',
        'platforms'           => 'array',
        'app_awareness'       => 'array',
        'usage_frequency'     => 'array',
        'preferred_channels'  => 'array',

        // === NEW ===
        'email_primary_goal'  => 'array',
        'email_best_day'      => 'array',

        'last_step'           => 'integer',
        'completed_at'        => 'datetime',
    ];

    /**
     * Default values
     * (hindari null untuk field array)
     */
    protected $attributes = [
        'event_types'         => '[]',
        'social_familiarity'  => '[]',
        'platforms'           => '[]',
        'app_awareness'       => '[]',
        'usage_frequency'     => '[]',
        'preferred_channels'  => '[]',

        // === NEW ===
        'email_primary_goal'  => '[]',
        'email_best_day'      => '[]',
    ];

    /**
     * =========================
     * OPTIONAL SCOPES
     * =========================
     */

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function isCompleted(): bool
    {
        return $this->status !== self::STATUS_IN_PROGRESS;
    }

    // Filter by company
    public function scopeCompany($query, $company)
    {
        return $query->where('company', $company);
    }

    // Filter by survey year (future-proof)
    public function scopeYear($query, $year)
    {
        if (Schema::hasColumn($this->getTable(), 'survey_year')) {
            return $query->where('survey_year', $year);
        }

        return $query;
    }
}
