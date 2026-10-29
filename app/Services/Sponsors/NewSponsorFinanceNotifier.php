<?php

namespace App\Services\Sponsors;

use App\Helpers\EmailSender;
use App\Models\Sponsors\Sponsor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Kirim email ke tim finance setiap ada sponsor baru dibuat di CMS,
 * supaya finance bisa langsung buatkan invoice-nya.
 *
 * Penerima diatur lewat env FINANCE_NOTIFICATION_EMAIL (boleh lebih dari satu,
 * pisahkan dengan koma). Kalau kosong, notifikasi di-skip.
 */
class NewSponsorFinanceNotifier
{
    public static function send(Sponsor $sponsor): void
    {
        $recipients = self::recipients();
        if (empty($recipients)) {
            return;
        }

        try {
            $sponsor->load(['pics', 'billings']);

            $send              = new EmailSender();
            $send->subject     = '[DMC Finance] Sponsor Baru — ' . $sponsor->name . ' (mohon dibuatkan invoice)';
            $send->template    = 'email.finance-new-sponsor';
            $send->data        = self::buildData($sponsor);
            $send->from        = env('EMAIL_SENDER');
            $send->name_sender = env('EMAIL_NAME');
            $send->to          = $recipients;
            $send->name        = 'Finance Team';
            $result            = $send->sendEmail();

            if ($result !== 'send') {
                Log::warning('Finance new-sponsor email failed', ['sponsor_id' => $sponsor->id, 'error' => $result]);
            }
        } catch (\Throwable $e) {
            Log::warning('Finance new-sponsor email failed', ['sponsor_id' => $sponsor->id, 'error' => $e->getMessage()]);
        }
    }

    private static function recipients(): array
    {
        $raw = (string) config('dmc.finance_notification_email', '');

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    private static function buildData(Sponsor $sponsor): array
    {
        return [
            'sponsor'    => $sponsor,
            'package'    => sponsor_package_label($sponsor->package) . ' Sponsorship',
            'period'     => self::formatMonth($sponsor->contract_start) . ' – ' . self::formatMonth($sponsor->contract_end),
            'pics'       => $sponsor->pics,
            'billings'   => $sponsor->billings,
            'created_by' => auth()->user()->name ?? '-',
            'created_at' => Carbon::now()->setTimezone('Asia/Jakarta')->format('d F Y, H:i') . ' WIB',
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
