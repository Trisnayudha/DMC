<?php

namespace App\Services;

use App\Models\Payments\FinanceInvoice;
use App\Models\Sponsors\SponsorRenewal;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Invoice Finance yang dibayar lewat VA Mandiri (Xendit Payments API v3).
 *
 * reference_id ke Xendit = "FINV-{id}-{attempt}" supaya webhook
 * /api/xendit/payment-callback bisa membedakannya dari pembayaran tiket
 * (yang memakai payment.code_payment) — lihat XenditCallbackController::paymentCallback().
 */
class FinanceInvoiceService
{
    const REFERENCE_PREFIX = 'FINV-';

    // Batas bawah VA Xendit; di bawah ini request ditolak bank.
    const MIN_AMOUNT = 10000;

    public static function isFinanceReference($referenceId): bool
    {
        return strpos((string) $referenceId, self::REFERENCE_PREFIX) === 0;
    }

    /** Nomor urut per tahun: DMC-INV-2026-0001. */
    public static function nextInvoiceNumber(int $year): string
    {
        $prefix = 'DMC-INV-' . $year . '-';
        $last = FinanceInvoice::where('invoice_number', 'LIKE', $prefix . '%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');
        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Normalisasi item (qty & harga dibulatkan ke rupiah) + subtotal, PPN, total.
     *
     * @return array [items, subtotal, vat_amount, total]
     */
    public static function calculate(array $items, float $vatPercent): array
    {
        $normalized = [];
        $subtotal = 0;
        foreach ($items as $item) {
            $description = trim((string) ($item['description'] ?? ''));
            if ($description === '') {
                continue;
            }
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $unitPrice = (int) round((float) ($item['unit_price'] ?? 0));
            $amount = $qty * $unitPrice;
            $normalized[] = [
                'description' => $description,
                'qty'         => $qty,
                'unit_price'  => $unitPrice,
                'amount'      => $amount,
            ];
            $subtotal += $amount;
        }
        $vatAmount = (int) round($subtotal * $vatPercent / 100);

        return [$normalized, $subtotal, $vatAmount, $subtotal + $vatAmount];
    }

    /**
     * Buat (atau buat ulang) VA Mandiri untuk invoice. Berlaku sampai akhir
     * hari due_date (WIB). VA lama otomatis tidak dipakai lagi karena
     * reference_id-nya diganti.
     */
    public static function createVirtualAccount(FinanceInvoice $invoice): FinanceInvoice
    {
        $attempt = $invoice->va_attempts + 1;
        $referenceId = self::REFERENCE_PREFIX . $invoice->id . '-' . $attempt;
        $expiresAt = Carbon::parse($invoice->due_date->format('Y-m-d') . ' 23:59:59', 'Asia/Jakarta');

        $va = XenditPaymentRequestService::createVirtualAccount([
            'external_id'     => $referenceId,
            'bank_code'       => $invoice->va_bank ?: 'MANDIRI',
            'name'            => $invoice->bill_to_company ?: $invoice->bill_to_name,
            'expected_amount' => $invoice->total_amount,
            'expiration_date' => $expiresAt->timestamp,
            'description'     => 'Invoice ' . $invoice->invoice_number . ' - Djakarta Mining Club',
        ]);

        $invoice->forceFill([
            'va_attempts'        => $attempt,
            'reference_id'       => $referenceId,
            'va_number'          => $va['account_number'],
            'payment_request_id' => $va['payment_request_id'],
            'va_expires_at'      => $expiresAt,
            'status'             => 'pending',
        ])->save();

        return $invoice;
    }

    /**
     * Dipanggil webhook Xendit untuk reference_id FINV-*. Idempotent: webhook
     * dobel atau status lama (reference VA yang sudah diganti) diabaikan.
     */
    public static function handleCallback(string $referenceId, string $status, $amount): array
    {
        $invoice = FinanceInvoice::where('reference_id', $referenceId)->first();
        if (!$invoice) {
            Log::warning('Finance invoice webhook: reference not found', ['reference_id' => $referenceId]);
            return ['api_status' => 0, 'api_message' => 'Finance invoice not found'];
        }

        if ($status === 'PAID') {
            if ($invoice->status === 'paid') {
                return ['api_status' => 1, 'api_message' => 'Already paid'];
            }
            self::markPaid($invoice, $amount !== null ? (int) round((float) $amount) : $invoice->total_amount);
            return ['api_status' => 1, 'api_message' => 'Finance invoice paid'];
        }

        if ($status === 'EXPIRED' && $invoice->status === 'pending') {
            $invoice->forceFill(['status' => 'expired'])->save();
            return ['api_status' => 1, 'api_message' => 'Finance invoice expired'];
        }

        return ['api_status' => 1, 'api_message' => 'Ignored status ' . $status];
    }

    public static function markPaid(FinanceInvoice $invoice, int $paidAmount, ?Carbon $paidAt = null): void
    {
        $paidAt = $paidAt ?: now();
        $invoice->forceFill([
            'status'      => 'paid',
            'paid_at'     => $paidAt,
            'paid_amount' => $paidAmount,
        ])->save();

        self::syncSponsorRenewal($invoice);
        self::sendPaidEmail($invoice);
    }

    /**
     * Invoice sponsor renewal yang lunas melengkapi kontraknya: paid_date diisi,
     * invoice number/date diisi kalau masih kosong (tidak menimpa input manual).
     */
    public static function syncSponsorRenewal(FinanceInvoice $invoice): void
    {
        if ($invoice->type !== 'sponsor_renewal' || !$invoice->sponsor_renewal_id) {
            return;
        }
        $renewal = SponsorRenewal::find($invoice->sponsor_renewal_id);
        if (!$renewal) {
            return;
        }
        if (!$renewal->paid_date) {
            $renewal->paid_date = $invoice->paid_at->copy()->timezone('Asia/Jakarta')->toDateString();
        }
        if (!$renewal->invoice_date) {
            $renewal->invoice_date = $invoice->issued_date->toDateString();
        }
        if (!$renewal->invoice_number
            && !SponsorRenewal::where('invoice_number', $invoice->invoice_number)->exists()) {
            $renewal->invoice_number = $invoice->invoice_number;
        }
        $renewal->save();
    }

    public static function pdf(FinanceInvoice $invoice)
    {
        return Pdf::setOptions(['isRemoteEnabled' => true])
            ->loadView('admin.finance_invoices.pdf', ['invoice' => $invoice])
            ->setPaper('a4', 'portrait');
    }

    public static function pdfFilename(FinanceInvoice $invoice): string
    {
        return 'invoice-' . strtolower($invoice->invoice_number) . '.pdf';
    }

    /** Kirim invoice + instruksi bayar VA Mandiri, PDF terlampir. */
    public static function sendInvoiceEmail(FinanceInvoice $invoice): void
    {
        $pdf = self::pdf($invoice)->output();

        Mail::send('email.finance-invoice', ['invoice' => $invoice], function ($email) use ($invoice, $pdf) {
            $email->to($invoice->bill_to_email, $invoice->bill_to_name)
                ->subject('Invoice ' . $invoice->invoice_number . ' - Djakarta Mining Club');
            $email->from(env('EMAIL_SENDER'), env('EMAIL_NAME'));
            foreach (self::ccList($invoice) as $cc) {
                $email->cc($cc);
            }
            $email->attachData($pdf, self::pdfFilename($invoice), ['mime' => 'application/pdf']);
        });

        $invoice->forceFill(['email_sent_at' => now()])->save();
    }

    private static function sendPaidEmail(FinanceInvoice $invoice): void
    {
        try {
            Mail::send('email.finance-invoice-paid', ['invoice' => $invoice], function ($email) use ($invoice) {
                $email->to($invoice->bill_to_email, $invoice->bill_to_name)
                    ->subject('Payment Received - Invoice ' . $invoice->invoice_number);
                $email->from(env('EMAIL_SENDER'), env('EMAIL_NAME'));
                foreach (self::ccList($invoice) as $cc) {
                    $email->cc($cc);
                }
            });
        } catch (\Throwable $e) {
            // Status lunas tetap tersimpan walau email gagal; Finance bisa kirim ulang manual.
            Log::error('Finance invoice paid email failed', ['invoice' => $invoice->invoice_number, 'error' => $e->getMessage()]);
        }
    }

    private static function ccList(FinanceInvoice $invoice): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $invoice->bill_to_cc))));
    }
}
