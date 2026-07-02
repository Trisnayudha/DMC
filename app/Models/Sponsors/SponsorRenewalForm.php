<?php

namespace App\Models\Sponsors;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Renewal form / proposal yang di-generate sebelum siklus follow-up dimulai.
 * Keberadaan record ini menjadi syarat (gate) agar follow-up bisa dicatat.
 */
class SponsorRenewalForm extends Model
{
    protected $table = 'sponsor_renewal_forms';

    protected $fillable = [
        'sponsor_id',
        'renewal_year',
        'form_number',
        'kmk_rate',
        'amount_usd',
        'amount_idr',
        'notes',
        'generated_at',
        'created_by',
    ];

    protected $casts = [
        'generated_at' => 'date',
    ];

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class, 'sponsor_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Nomor renewal form berikutnya untuk suatu tahun, pola sama dengan quotation
     * number ({tahun}DMC{urut}) supaya konsisten. Auto-suggest, tetap bisa diedit.
     */
    public static function generateFormNumber(int $year): string
    {
        $count = self::whereNotNull('form_number')
            ->where('form_number', 'LIKE', $year . 'DMC%')
            ->count();

        return $year . 'DMC' . ($count + 1);
    }
}
