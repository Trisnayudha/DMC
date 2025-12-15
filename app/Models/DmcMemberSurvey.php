<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class DmcMemberSurvey extends Model
{
    protected $table = 'dmc_member_surveys';

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
        'topics_interest',
        'speaker_wishlist',
        'nominee_name',
        'nominee_company',
        'event_improvement',

        /* =========================
         * 3. MARKETING & COMMUNICATION
         * ========================= */
        'social_familiarity',
        'platforms',
        'app_awareness',
        'usage_frequency',
        'preferred_channels',
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
    ];

    /**
     * Cast attributes (WAJIB untuk checkbox / array)
     */
    protected $casts = [
        'event_types'        => 'array',
        'social_familiarity' => 'array',
        'platforms'          => 'array',
        'app_awareness'      => 'array',
        'usage_frequency'    => 'array',
        'preferred_channels' => 'array',
    ];

    /**
     * Default values (hindari null)
     */
    protected $attributes = [
        'event_types'        => '[]',
        'social_familiarity' => '[]',
        'platforms'          => '[]',
        'app_awareness'      => '[]',
        'usage_frequency'    => '[]',
        'preferred_channels' => '[]',
    ];

    /**
     * =========================
     * OPTIONAL SCOPES
     * =========================
     */

    public function scopeCompany($query, $company)
    {
        return $query->where('company', $company);
    }

    public function scopeYear($query, $year)
    {
        if (Schema::hasColumn($this->getTable(), 'survey_year')) {
            return $query->where('survey_year', $year);
        }

        return $query;
    }
}
