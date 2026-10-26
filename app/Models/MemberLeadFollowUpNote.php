<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One entry in a lead's notes history — e.g. what the prospect replied after
 * the sponsor kit / follow-up. step is the step the note belongs to
 * (sponsorkit | follow_up_1 | follow_up_2), or null for a general note.
 */
class MemberLeadFollowUpNote extends Model
{
    protected $table = 'member_lead_follow_up_notes';

    protected $fillable = [
        'lead_id',
        'step',
        'note',
        'created_by_id',
        'created_by_name',
    ];

    public function lead()
    {
        return $this->belongsTo(MemberLeadFollowUp::class, 'lead_id');
    }

    public function stepLabel(): string
    {
        return $this->step ? MemberLeadFollowUp::stepLabel($this->step) : 'General';
    }
}
