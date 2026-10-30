<?php

namespace App\Services\Sponsors;

use App\Models\Sponsors\Sponsor;
use App\Models\Sponsors\SponsorFollowup;
use App\Models\Sponsors\SponsorRenewalForm;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

/**
 * Kirim email ke finance saat hasil follow-up secretariat = sponsor confirm renewal,
 * supaya finance bisa issue invoice-nya (manual di Accurate). Bukti follow-up
 * dilampirkan.
 *
 * Penerima sama dengan notifikasi sponsor baru: env FINANCE_NOTIFICATION_EMAIL.
 */
class RenewalConfirmedFinanceNotifier
{
    public static function send(Sponsor $sponsor, SponsorFollowup $followup): void
    {
        $recipients = self::recipients();
        if (empty($recipients)) {
            return;
        }

        try {
            $sponsor->load(['pics', 'billings']);

            $form = SponsorRenewalForm::where('sponsor_id', $sponsor->id)
                ->where('renewal_year', $followup->renewal_year)
                ->first();

            $subject   = '[DMC Finance] Renewal Confirmed — ' . $sponsor->name
                . ($form ? ' (' . $form->form_number . ')' : '') . ' — Please Issue Invoice';
            $proofPath = $followup->proof_path ? Storage::disk('public')->path($followup->proof_path) : null;

            Mail::send('email.finance-renewal-confirmed', self::buildData($sponsor, $followup, $form), function ($message) use ($recipients, $subject, $proofPath) {
                $message->from(env('EMAIL_SENDER'), env('EMAIL_NAME'));
                $message->to($recipients);
                $message->subject($subject);
                if ($proofPath && is_file($proofPath)) {
                    $message->attach($proofPath, ['as' => 'bukti-konfirmasi-' . basename($proofPath)]);
                }
            });
        } catch (\Throwable $e) {
            Log::warning('Finance renewal-confirmed email failed', ['sponsor_id' => $sponsor->id, 'error' => $e->getMessage()]);
        }
    }

    private static function recipients(): array
    {
        $raw = (string) config('dmc.finance_notification_email', '');

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    private static function buildData(Sponsor $sponsor, SponsorFollowup $followup, ?SponsorRenewalForm $form): array
    {
        return [
            'sponsor'     => $sponsor,
            'followup'    => $followup,
            'form'        => $form,
            'package'     => sponsor_package_label($sponsor->package) . ' Sponsorship',
            'contractEnd' => self::formatMonth($sponsor->contract_end),
            'pics'        => SponsorContacts::unique($sponsor->pics),
            'billings'    => SponsorContacts::unique($sponsor->billings),
            'confirmedBy' => SponsorContacts::cmsUserName($followup->created_by),
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
