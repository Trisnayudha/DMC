<?php

namespace App\Models\Sponsors;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SponsorInterviewSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'sponsor_id',
        'company_name',
        'sponsor_package',
        'number_of_interviewees',
        'interviewees',
        'preferred_time_slot',
        'selected_questions',
    ];

    protected $casts = [
        'interviewees' => 'array',
        'selected_questions' => 'array',
    ];

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class, 'sponsor_id');
    }
}
