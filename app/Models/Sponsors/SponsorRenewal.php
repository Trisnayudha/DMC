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
        'renewal_type',
        'amount_usd',
        'amount_idr',
        'is_current',
        'notes',
        'quotation_number',
        'quotation_date',
        'invoice_date',
        'invoice_number',
        'paid_date',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'invoice_date'   => 'date',
        'paid_date'      => 'date',
    ];

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class, 'sponsor_id');
    }

    public static function generateQuotationNumber(int $year): string
    {
        $count = self::whereNotNull('quotation_number')
            ->where('quotation_number', 'LIKE', $year . 'DMC%')
            ->count();
        return $year . 'DMC' . ($count + 1);
    }

    /**
     * Hari dari invoice terkirim sampai dibayar (atau sampai sekarang kalau belum dibayar).
     */
    public function getPaymentTurnaroundDaysAttribute(): ?int
    {
        if (!$this->invoice_date) {
            return null;
        }
        $end = $this->paid_date ?? now();
        return (int) $this->invoice_date->diffInDays($end, false);
    }
}
