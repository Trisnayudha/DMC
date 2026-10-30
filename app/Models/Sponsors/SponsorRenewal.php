<?php

namespace App\Models\Sponsors;

use App\Services\Sponsors\PaymentReceivedSecretariatNotifier;
use Illuminate\Database\Eloquent\Model;

class SponsorRenewal extends Model
{
    use \App\Traits\LogsSponsorActivity;

    /**
     * Email ke secretariat sekali, saat Paid Date pertama kali terisi — dari Renew
     * Contract, Edit Contract, maupun pelunasan VA otomatis (FinanceInvoiceService).
     */
    protected static function booted()
    {
        static::created(function (SponsorRenewal $renewal) {
            if ($renewal->paid_date) {
                PaymentReceivedSecretariatNotifier::send($renewal);
            }
        });

        static::updated(function (SponsorRenewal $renewal) {
            if ($renewal->paid_date && $renewal->wasChanged('paid_date') && !$renewal->getOriginal('paid_date')) {
                PaymentReceivedSecretariatNotifier::send($renewal);
            }
        });
    }

    protected $sponsorLogSubject = 'Contract';

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
        'vat_percent',
        'pph23_percent',
        'is_current',
        'notes',
        'quotation_number',
        'quotation_date',
        'invoice_date',
        'invoice_number',
        'invoice_sent_date',
        'paid_date',
    ];

    protected $casts = [
        'quotation_date'    => 'date',
        'invoice_date'      => 'date',
        'invoice_sent_date' => 'date',
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

    /**
     * Rincian pajak per mata uang: base (amount), VAT, Grand Total (incl. VAT),
     * PPh 23 yang dipotong dari base, dan Net Received (Grand Total − PPh 23).
     * Mata uang tanpa amount bernilai null.
     */
    public function taxBreakdown(): array
    {
        $vat  = (float) $this->vat_percent;
        $pph  = (float) $this->pph23_percent;
        $calc = function ($amount) use ($vat, $pph) {
            if ($amount === null || $amount === '') {
                return null;
            }
            $base   = (float) $amount;
            $vatAmt = $base * $vat / 100;
            $pphAmt = $base * $pph / 100;

            return [
                'base'  => $base,
                'vat'   => $vatAmt,
                'grand' => $base + $vatAmt,
                'pph23' => $pphAmt,
                'net'   => $base + $vatAmt - $pphAmt,
            ];
        };

        return [
            'vatPercent'   => $vat,
            'pph23Percent' => $pph,
            'usd'          => $calc($this->amount_usd),
            'idr'          => $calc($this->amount_idr),
        ];
    }
}
