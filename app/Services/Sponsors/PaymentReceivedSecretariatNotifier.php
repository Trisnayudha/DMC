<?php

namespace App\Services\Sponsors;

use App\Models\Sponsors\SponsorRenewal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Kabari secretariat kalau pembayaran kontrak sponsor sudah diterima (Paid Date
 * terisi). Penerima & pengirim sama dengan notifikasi renewal form
 * (SECRETARIAT_NOTIFICATION_EMAIL / dmc.secretariat_notification_from).
 */
class PaymentReceivedSecretariatNotifier
{
    public static function send(SponsorRenewal $renewal): void
    {
        $recipients = array_values(array_filter(array_map('trim', explode(',', (string) config('dmc.secretariat_notification_email', '')))));
        $sponsor    = $renewal->sponsor;
        if (empty($recipients) || !$sponsor) {
            return;
        }

        $sponsorName = $sponsor->branding_name ?: $sponsor->name;
        $subject     = '[DMC Secretariat] Payment Received — ' . $sponsorName
            . ($renewal->invoice_number ? ' (Invoice ' . $renewal->invoice_number . ')' : '');

        try {
            Mail::send('email.secretariat-payment-received', self::buildData($renewal), function ($message) use ($recipients, $subject) {
                $message->from(config('dmc.secretariat_notification_from'), env('EMAIL_NAME'));
                $message->to($recipients);
                $message->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::warning('Secretariat payment-received email failed', ['renewal_id' => $renewal->id, 'error' => $e->getMessage()]);
        }
    }

    private static function buildData(SponsorRenewal $renewal): array
    {
        $user = auth()->user();

        return [
            'renewal'     => $renewal,
            'sponsor'     => $renewal->sponsor,
            'package'     => sponsor_package_label($renewal->package) . ' Sponsorship',
            'period'      => self::formatMonth($renewal->contract_start) . ' – ' . self::formatMonth($renewal->contract_end),
            'tax'         => $renewal->taxBreakdown(),
            'recordedBy'  => $user && !empty($user->name) ? $user->name : 'Automatic (Virtual Account payment)',
            'sentAt'      => Carbon::now()->setTimezone('Asia/Jakarta')->format('d F Y, H:i') . ' WIB',
        ];
    }

    private static function formatMonth(?string $yearMonth): string
    {
        if (empty($yearMonth)) {
            return '-';
        }

        try {
            return Carbon::createFromFormat('Y-m', $yearMonth)->format('F Y');
        } catch (\Throwable $e) {
            return $yearMonth;
        }
    }
}
