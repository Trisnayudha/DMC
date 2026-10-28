<?php

namespace App\Models\Payments;

use App\Models\CmsUser;
use App\Models\Events\Events;
use App\Models\Sponsors\Sponsor;
use App\Models\Sponsors\SponsorRenewal;
use Illuminate\Database\Eloquent\Model;

/**
 * Invoice Finance yang dibayar lewat VA Mandiri Xendit. Lihat FinanceInvoiceService.
 */
class FinanceInvoice extends Model
{
    protected $table = 'finance_invoices';

    const TYPES = [
        'sponsor_renewal' => 'Sponsor Renewal',
        'event_ticket'    => 'Tiket Event',
        'custom'          => 'Tagihan Lainnya',
    ];

    const STATUSES = [
        'pending'   => 'Menunggu Pembayaran',
        'paid'      => 'Lunas',
        'expired'   => 'Expired',
        'cancelled' => 'Dibatalkan',
    ];

    protected $fillable = [
        'invoice_number',
        'type',
        'status',
        'bill_to_name',
        'bill_to_company',
        'bill_to_email',
        'bill_to_cc',
        'bill_to_phone',
        'bill_to_address',
        'items',
        'subtotal',
        'vat_percent',
        'vat_amount',
        'total_amount',
        'notes',
        'sponsor_id',
        'sponsor_renewal_id',
        'events_id',
        'issued_date',
        'due_date',
        'va_bank',
        'va_number',
        'reference_id',
        'payment_request_id',
        'va_attempts',
        'va_expires_at',
        'paid_at',
        'paid_amount',
        'email_sent_at',
        'created_by',
    ];

    protected $casts = [
        'items'         => 'array',
        'vat_percent'   => 'float',
        'issued_date'   => 'date',
        'due_date'      => 'date',
        'va_expires_at' => 'datetime',
        'paid_at'       => 'datetime',
        'email_sent_at' => 'datetime',
    ];

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class, 'sponsor_id');
    }

    public function sponsorRenewal()
    {
        return $this->belongsTo(SponsorRenewal::class, 'sponsor_renewal_id');
    }

    public function event()
    {
        return $this->belongsTo(Events::class, 'events_id');
    }

    public function creator()
    {
        return $this->belongsTo(CmsUser::class, 'created_by');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** VA masih bisa dibayar: belum lunas/batal dan belum lewat masa berlaku. */
    public function isPayable(): bool
    {
        return $this->status === 'pending'
            && $this->va_number
            && (!$this->va_expires_at || $this->va_expires_at->isFuture());
    }
}
