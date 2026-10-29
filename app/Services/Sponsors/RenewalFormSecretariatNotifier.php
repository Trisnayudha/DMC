<?php

namespace App\Services\Sponsors;

use App\Models\Sponsors\Sponsor;
use App\Models\Sponsors\SponsorRenewalForm;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Kirim email ke tim secretariat setiap renewal form di-generate (atau di-update),
 * dengan PDF renewal form sebagai attachment.
 *
 * Penerima diatur lewat env SECRETARIAT_NOTIFICATION_EMAIL (boleh lebih dari satu,
 * pisahkan dengan koma). Kalau kosong, notifikasi di-skip.
 */
class RenewalFormSecretariatNotifier
{
    public static function send(Sponsor $sponsor, SponsorRenewalForm $form, string $pdfContent, string $pdfFilename, bool $isUpdate = false): void
    {
        $recipients = self::recipients();
        if (empty($recipients)) {
            return;
        }

        $subject = '[DMC Secretariat] Renewal Form ' . ($isUpdate ? 'Updated' : 'Generated')
            . ' — ' . $sponsor->name . ' (' . $form->form_number . ')';

        try {
            Mail::send('email.secretariat-renewal-form', self::buildData($sponsor, $form, $isUpdate), function ($message) use ($recipients, $subject, $pdfContent, $pdfFilename) {
                $message->from(env('EMAIL_SENDER'), env('EMAIL_NAME'));
                $message->to($recipients);
                $message->subject($subject);
                $message->attachData($pdfContent, $pdfFilename, ['mime' => 'application/pdf']);
            });
        } catch (\Throwable $e) {
            Log::warning('Secretariat renewal-form email failed', ['sponsor_id' => $sponsor->id, 'error' => $e->getMessage()]);
        }
    }

    private static function recipients(): array
    {
        $raw = (string) config('dmc.secretariat_notification_email', '');

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    private static function buildData(Sponsor $sponsor, SponsorRenewalForm $form, bool $isUpdate): array
    {
        return [
            'sponsor'      => $sponsor,
            'form'         => $form,
            'isUpdate'     => $isUpdate,
            'package'      => sponsor_package_label($sponsor->package) . ' Sponsorship',
            'contractEnd'  => self::formatMonth($sponsor->contract_end),
            'pic'          => $sponsor->firstPic,
            'generatedBy'  => SponsorContacts::cmsUserName($form->created_by),
            'sentAt'       => Carbon::now()->setTimezone('Asia/Jakarta')->format('d F Y, H:i') . ' WIB',
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
