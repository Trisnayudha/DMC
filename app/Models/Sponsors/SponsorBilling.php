<?php

namespace App\Models\Sponsors;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SponsorBilling extends Model
{
    use \App\Traits\LogsSponsorActivity;

    protected $sponsorLogSubject = 'Billing';

    use HasFactory;

    protected $table = 'sponsors_billing';

    protected $fillable = [
        'sponsor_id',
        'name',
        'title',
        'email',
        'phone',
    ];

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class);
    }
}
