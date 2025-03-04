<?php

namespace App\Models\Sponsors;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Benefit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category'
    ];

    // Relasi: Satu Benefit dapat dimiliki oleh banyak PackageBenefit
    public function packageBenefits()
    {
        return $this->hasMany(PackageBenefit::class);
    }

    // Relasi: Satu Benefit dapat digunakan oleh banyak Sponsor melalui SponsorBenefitUsage
    public function sponsorBenefitUsages()
    {
        return $this->hasMany(SponsorBenefitUsage::class);
    }
}
