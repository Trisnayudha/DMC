<?php

namespace App\Models\Sponsors;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageBenefit extends Model
{
    use HasFactory;

    protected $table = 'package_benefit';

    protected $fillable = [
        'package_name',
        'benefit_id',
        'quantity',
        'additional_info'
    ];

    // Relasi: Setiap PackageBenefit milik satu Benefit
    public function benefit()
    {
        return $this->belongsTo(Benefit::class);
    }
}
