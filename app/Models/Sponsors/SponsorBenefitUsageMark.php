<?php

namespace App\Models\Sponsors;

use Illuminate\Database\Eloquent\Model;

class SponsorBenefitUsageMark extends Model
{
    protected $table = 'sponsor_benefit_usage_marks';

    protected $fillable = [
        'sponsor_benefit_usage_id',
        'marked_at',
        'note',
        'proof_image',
        'created_by',
    ];

    protected $casts = [
        'marked_at' => 'date',
    ];

    public function usage()
    {
        return $this->belongsTo(SponsorBenefitUsage::class, 'sponsor_benefit_usage_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
