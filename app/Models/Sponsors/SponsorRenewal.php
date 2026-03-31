<?php

namespace App\Models\Sponsors;

use Illuminate\Database\Eloquent\Model;

class SponsorRenewal extends Model
{
    protected $table = 'sponsor_renewals';

    protected $fillable = [
        'sponsor_id',
        'renewal_year',
        'contract_start',
        'contract_end',
        'package',
        'renewal_status',
        'is_current',
        'notes',
    ];

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class, 'sponsor_id');
    }
}
