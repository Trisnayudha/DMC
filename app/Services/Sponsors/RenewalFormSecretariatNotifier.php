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
    /**
     * @param array $changes perubahan saat update: [label => ['old' => ..., 'new' => ...]]
     */
    public static function send(Sponsor $sponsor, SponsorRenewalForm $form, string $pdfContent, string $pdfFilename, bool $isUpdate = false, array $changes = []): void
    {
        $recipients = self::recipients();
        if (empty($recipients)) {
            return;
        }

        // Subjek sama untuk generate & update (pakai tahun renewal, bukan nomor form yang
        // bisa diedit) supaya email update masuk ke thread yang sama di inbox secretariat.
        $subject = '[DMC Secretariat] Renewal Form ' . $form->renewal_year . ' — ' . $sponsor->name;
        $threadId = self::threadMessageId($form);

        try {
            Mail::send('email.secretariat-renewal-form', self::buildData($sponsor, $form, $isUpdate, $changes), function ($message) use ($recipients, $subject, $pdfContent, $pdfFilename, $threadId, $isUpdate) {
                $message->from(config('dmc.secretariat_notification_from'), env('EMAIL_NAME'));
                $message->to($recipients);
                $message->subject($subject);
                $message->attachData($pdfContent, $pdfFilename, ['mime' => 'application/pdf']);

                // Email pertama jadi akar thread; update merujuk ke sana.
                $swift = $message->getSwiftMessage();
                if ($isUpdate) {
                    $swift->getHeaders()->addTextHeader('In-Reply-To', '<' . $threadId . '>');
                    $swift->getHeaders()->addTextHeader('References', '<' . $threadId . '>');
                } else {
                    $swift->setId($threadId);
                }
            });
        } catch (\Throwable $e) {
            Log::warning('Secretariat renewal-form email failed', ['sponsor_id' => $sponsor->id, 'error' => $e->getMessage()]);
        }
    }

    /** Message-ID tetap per renewal form (tanpa < >), dipakai sebagai akar thread email. */
    private static function threadMessageId(SponsorRenewalForm $form): string
    {
        return 'renewal-form-' . $form->id . '@djakarta-miningclub.com';
    }

    private static function recipients(): array
    {
        $raw = (string) config('dmc.secretariat_notification_email', '');

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    private static function buildData(Sponsor $sponsor, SponsorRenewalForm $form, bool $isUpdate, array $changes): array
    {
        $user = auth()->user();

        return [
            'sponsor'      => $sponsor,
            'form'         => $form,
            'isUpdate'     => $isUpdate,
            'package'      => sponsor_package_label($sponsor->package) . ' Sponsorship',
            'periods'      => self::contractPeriods($sponsor, $form),
            'pic'          => $sponsor->billingContact(),
            'generatedBy'  => SponsorContacts::cmsUserName($form->created_by),
            'updatedBy'    => $isUpdate && $user ? $user->name : null,
            'changes'      => $changes,
            'pricing'      => self::pricing($form),
            'sentAt'       => Carbon::now()->setTimezone('Asia/Jakarta')->format('d F Y, H:i') . ' WIB',
        ];
    }

    /**
     * Angka harga sama dengan PDF renewal form: IDR = amount_idr, kalau kosong
     * amount_usd × KMK rate; VAT & grand total hanya kalau vat_percent > 0.
     */
    private static function pricing(SponsorRenewalForm $form): array
    {
        $usd = $form->amount_usd ? (float) $form->amount_usd : null;
        $idr = $form->amount_idr !== null && $form->amount_idr !== ''
            ? (float) $form->amount_idr
            : (($usd && $form->kmk_rate) ? $usd * (float) $form->kmk_rate : null);
        $vat = (float) $form->vat_percent;

        return [
            'usd'        => $usd,
            'idr'        => $idr,
            'vatPercent' => $vat,
            'vatIdr'     => ($vat > 0 && $idr !== null) ? $idr * $vat / 100 : null,
            'grandIdr'   => ($vat > 0 && $idr !== null) ? $idr * (1 + $vat / 100) : null,
            'vatUsd'     => ($vat > 0 && $usd !== null) ? $usd * $vat / 100 : null,
            'grandUsd'   => ($vat > 0 && $usd !== null) ? $usd * (1 + $vat / 100) : null,
        ];
    }

    private static function contractPeriods(Sponsor $sponsor, SponsorRenewalForm $form): array
    {
        $periods = RenewalPeriod::forForm($sponsor, $form);

        return [
            'current' => self::formatRange($periods['current'][0], $periods['current'][1]),
            'renewal' => self::formatRange($periods['renewal'][0], $periods['renewal'][1]),
        ];
    }

    private static function formatRange(?string $start, ?string $end): string
    {
        if (empty($start) && empty($end)) {
            return '-';
        }
        return self::formatMonth($start) . ' – ' . self::formatMonth($end);
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
