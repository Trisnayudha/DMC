<?php

namespace App\Models\Sponsors;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SponsorBenefitUsage extends Model
{
    use HasFactory;
    protected $table = 'sponsor_benefit_usage';

    protected $fillable = [
        'sponsor_id',
        'benefit_id',
        'status',
        'used_at'
    ];

    // Relasi: Setiap penggunaan benefit (usage) berkaitan dengan satu Benefit
    public function benefit()
    {
        return $this->belongsTo(Benefit::class);
    }

    // Relasi: Setiap penggunaan benefit (usage) berkaitan dengan satu Sponsor
    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class);
    }
}
