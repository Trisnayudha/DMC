<?php

namespace App\Services\Membership;

use App\Helpers\EmailSender;
use App\Models\Company\CompanyModel;
use App\Models\Profiles\ProfileModel;
use App\Models\User;
use App\Support\QrCode;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;

/**
 * Logika verifikasi member (status aktif, member ID, QR, Mailchimp, email approval).
 *
 * Dipakai bersama oleh:
 *  - Admin\UsersController::verifyMember  (verifikasi manual dari CMS)
 *  - API\AuthController::registerWeb      (auto-verify dari app scanner saat check-in)
 *
 * Sengaja dijadikan satu sumber kebenaran agar perilaku kedua jalur selalu identik.
 */
class MemberVerificationService
{
    /** Email lama tidak ada di audience / tidak berubah — tidak ada yang dikerjakan. */
    const MAILCHIMP_EMAIL_SKIPPED = 'skipped';

    /** Alamat contact-nya berhasil diganti di tempat (status & history tetap). */
    const MAILCHIMP_EMAIL_RENAMED = 'renamed';

    /** Email baru di-subscribe sebagai contact terpisah, email lama di-archive. */
    const MAILCHIMP_EMAIL_SWAPPED = 'swapped';

    /** Mailchimp menolak keduanya — perlu dirapikan manual. */
    const MAILCHIMP_EMAIL_FAILED = 'failed';

    /**
     * Aktifkan user sebagai member: status, member ID (uname), dan QR code.
     */
    public function activate(User $user, ?Carbon $verifiedAt = null): User
    {
        $verifiedAt = $verifiedAt ? $verifiedAt->copy() : now();

        $user->status_member = 'active';
        $user->uname = $this->generateVerificationMemberId($user, $verifiedAt);

        try {
            $qrImage = QrCode::format('png')
                ->size(300)
                ->errorCorrection('H')
                ->generate($user->uname);

            $fileName = 'img-verify-' . $user->id . '-' . $verifiedAt->timestamp . '.png';
            $outputFile = '/public/uploads/qr-code/' . $fileName;
            $dbPath = '/storage/uploads/qr-code/' . $fileName;

            Storage::disk('local')->put($outputFile, $qrImage);
            $user->qrcode = $dbPath;
        } catch (\Throwable $e) {
            Log::warning('MemberVerification: QR regeneration failed for user ' . $user->id . ': ' . $e->getMessage());
        }

        $user->save();

        return $user;
    }

    /**
     * Aktifkan + import Mailchimp + kirim email approval.
     */
    public function verifyAndNotify(User $user, ?Carbon $verifiedAt = null): User
    {
        $user = $this->activate($user, $verifiedAt);

        $email = strtolower(trim($user->email ?? ''));
        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->syncToMailchimp($user);
            $this->sendApprovalEmail($user);
        }

        return $user;
    }

    /**
     * Import / re-subscribe member ke Mailchimp.
     *
     * @return bool true kalau Mailchimp mengonfirmasi contact-nya tersimpan
     *              subscribed. Contact yang pernah unsubscribe ditolak
     *              Mailchimp (compliance state) dan akan mengembalikan false.
     */
    public function syncToMailchimp(User $user): bool
    {
        $email = strtolower(trim($user->email ?? ''));
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $company = CompanyModel::where('users_id', $user->id)->first();
        $profile = ProfileModel::where('users_id', $user->id)->first();

        try {
            $config = $this->mailchimpConfig();

            if ($config) {
                $merge = [];
                if (!empty($user->name))               $merge['FNAME']    = $user->name;
                if (!empty($company->company_name))    $merge['MMERGE5']  = $company->company_name;
                if (!empty($profile->job_title))       $merge['MMERGE7']  = $profile->job_title;
                if (!empty($company->company_website)) $merge['MMERGE13'] = $company->company_website;
                $merge['MMERGE11'] = now()->format('m/d/Y');

                $phone = $profile->fullphone ?? $profile->phone ?? null;
                if ($phone && preg_match('/^\+\d[\d\s\-\(\)]{5,}$/', trim((string) $phone))) {
                    $merge['MERGE4'] = trim((string) $phone);
                }

                $response = $this->mailchimpRequest($config)
                    ->put($this->mailchimpMemberUrl($config, $email), [
                        'email_address' => $email,
                        'status_if_new' => 'subscribed',
                        'status'        => 'subscribed',
                        'merge_fields'  => $merge,
                    ]);

                if ($response->successful()) {
                    return true;
                }

                Log::warning('MemberVerification: Mailchimp import failed for user ' . $user->id
                    . ' (HTTP ' . $response->status() . '): ' . $response->body());
            }
        } catch (\Throwable $e) {
            Log::warning('MemberVerification: Mailchimp import failed for user ' . $user->id . ': ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Archive member dari audience Mailchimp (dipakai saat deactivate, dan saat
     * email lama sudah tidak dipakai lagi).
     *
     * Sengaja archive (DELETE), bukan unsubscribe (PATCH status=unsubscribed):
     * contact yang di-archive tidak lagi dihitung ke billing Mailchimp, sedangkan
     * contact yang cuma di-unsubscribe tetap terhitung. Archive tetap "soft" —
     * Mailchimp menyimpan contact-nya, cuma dikeluarkan dari audience aktif, jadi
     * beda dengan permanent-delete dan masih bisa dipulihkan kalau perlu.
     *
     * @return bool true kalau Mailchimp mengonfirmasi contact-nya ter-archive.
     */
    public function archiveFromMailchimp(User $user): bool
    {
        return $this->archiveEmailFromMailchimp(strtolower(trim($user->email ?? '')), $user->id);
    }

    /**
     * Archive satu alamat email — dipakai kalau alamat yang mau dimatikan bukan
     * lagi email user-nya sekarang (mis. setelah admin ganti email).
     */
    public function archiveEmailFromMailchimp(string $email, $userId = null): bool
    {
        $email = strtolower(trim($email));
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $config = $this->mailchimpConfig();
        if (!$config) {
            return false;
        }

        try {
            $response = $this->mailchimpRequest($config)
                ->delete($this->mailchimpMemberUrl($config, $email));

            if ($response->successful()) {
                return true;
            }

            // 404 = contact-nya memang tidak ada di audience, bukan kegagalan.
            if ($response->status() !== 404) {
                Log::warning('MemberVerification: Mailchimp archive failed for user ' . $userId
                    . ' (HTTP ' . $response->status() . '): ' . $response->body());
            }

            return false;
        } catch (\Throwable $e) {
            Log::warning('MemberVerification: Mailchimp archive failed for user ' . $userId . ': ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Ikutkan Mailchimp saat email member diganti dari backend.
     *
     * Jalur utamanya PATCH `email_address` pada contact lama — cara native
     * Mailchimp untuk ganti alamat: status subscribe, tag, history, dan merge
     * field ikut pindah, dan alamat lama hilang dari audience. Ini juga yang
     * bikin koreksi typo aman: alamat lama tidak ditandai unsubscribed, jadi
     * masih bisa dikembalikan kalau ternyata salah ketik.
     *
     * Kalau ditolak — hampir selalu karena email barunya sudah jadi contact
     * terpisah di audience — baru fallback ke subscribe email baru lalu
     * unsubscribe email lama.
     *
     * Panggil SETELAH email barunya tersimpan di $user.
     *
     * @param  string $oldEmail Email sebelum diganti.
     * @return string Salah satu konstanta MAILCHIMP_EMAIL_*.
     */
    public function changeMailchimpEmail(User $user, string $oldEmail): string
    {
        $oldEmail = strtolower(trim($oldEmail));
        $newEmail = strtolower(trim($user->email ?? ''));

        if (!$oldEmail || !$newEmail || $oldEmail === $newEmail) {
            return self::MAILCHIMP_EMAIL_SKIPPED;
        }

        if (!filter_var($oldEmail, FILTER_VALIDATE_EMAIL) || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            return self::MAILCHIMP_EMAIL_SKIPPED;
        }

        $config = $this->mailchimpConfig();
        if (!$config) {
            return self::MAILCHIMP_EMAIL_SKIPPED;
        }

        try {
            $existing = $this->mailchimpRequest($config)->get($this->mailchimpMemberUrl($config, $oldEmail));

            // Email lama tidak pernah masuk audience (mis. member belum pernah
            // di-verify) — jangan bikin contact baru cuma gara-gara diedit.
            if ($existing->status() === 404) {
                return self::MAILCHIMP_EMAIL_SKIPPED;
            }

            if ($existing->failed()) {
                Log::warning('MemberVerification: Mailchimp lookup failed for user ' . $user->id
                    . ' (HTTP ' . $existing->status() . '): ' . $existing->body());

                return self::MAILCHIMP_EMAIL_FAILED;
            }

            $oldStatus = (string) $existing->json('status');

            $renamed = $this->mailchimpRequest($config)
                ->patch($this->mailchimpMemberUrl($config, $oldEmail), [
                    'email_address' => $newEmail,
                ]);

            if ($renamed->successful()) {
                return self::MAILCHIMP_EMAIL_RENAMED;
            }

            // Subscribe email baru hanya kalau yang lama memang masih
            // subscribed — kalau member sudah opt-out, ganti email bukan
            // alasan untuk mendaftarkannya lagi.
            $subscribed = $oldStatus === 'subscribed' ? $this->syncToMailchimp($user) : false;
            $archived = $this->archiveEmailFromMailchimp($oldEmail, $user->id);

            if ($subscribed || $archived) {
                return self::MAILCHIMP_EMAIL_SWAPPED;
            }

            Log::warning('MemberVerification: Mailchimp email change failed for user ' . $user->id
                . ' (HTTP ' . $renamed->status() . '): ' . $renamed->body());

            return self::MAILCHIMP_EMAIL_FAILED;
        } catch (\Throwable $e) {
            Log::warning('MemberVerification: Mailchimp email change failed for user ' . $user->id . ': ' . $e->getMessage());

            return self::MAILCHIMP_EMAIL_FAILED;
        }
    }

    /**
     * Kredensial Mailchimp, atau null kalau konfigurasinya belum lengkap.
     */
    private function mailchimpConfig(): ?array
    {
        $apiKey = config('newsletter.apiKey') ?: env('MAILCHIMP_APIKEY');
        $server = config('newsletter.server') ?: (explode('-', (string) $apiKey)[1] ?? null);
        $listId = config('newsletter.lists.subscribers.id') ?: env('MAILCHIMP_LIST_ID');

        if (!$apiKey || !$server || !$listId) {
            return null;
        }

        return ['apiKey' => $apiKey, 'server' => $server, 'listId' => $listId];
    }

    private function mailchimpRequest(array $config)
    {
        return Http::withBasicAuth('anystring', $config['apiKey'])->timeout(20);
    }

    private function mailchimpMemberUrl(array $config, string $email): string
    {
        $subscriberHash = md5(strtolower(trim($email)));

        return 'https://' . $config['server'] . '.api.mailchimp.com/3.0/lists/' . $config['listId'] . '/members/' . $subscriberHash;
    }

    public function sendApprovalEmail(User $user): void
    {
        $email = strtolower(trim($user->email ?? ''));

        try {
            $setPasswordUrl = null;
            if (empty($user->password)) {
                $token = Password::broker()->createToken($user);
                $setPasswordUrl = route('password.reset', [
                    'token' => $token,
                    'email' => $email,
                ]);
            }

            $memberId = $user->uname;
            $linkExpiryMinutes = (int) config('auth.passwords.users.expire', 60);
            $linkExpiryHours = (int) max(1, ceil($linkExpiryMinutes / 60));
            $loginUrl = (string) config('dmc.post_reset_password_redirect_url', 'https://www.djakarta-miningclub.com?modalloginopen=true');

            $send = new EmailSender();
            $send->subject = 'Djakarta Mining Club – Membership Approval Confirmation (ID: ' . $memberId . ')';
            $send->template = 'email.membership-approved';
            $send->data = [
                'users_name' => $user->name ?? 'Member',
                'member_id' => $memberId,
                'registered_email' => $email,
                'set_password_url' => $setPasswordUrl,
                'link_expiry_hours' => $linkExpiryHours,
                'login_url' => $loginUrl,
            ];
            $send->name = $user->name ?? 'Member';
            $send->from = env('EMAIL_SENDER');
            $send->name_sender = env('EMAIL_NAME');
            $send->to = $email;
            $send->sendEmail();
        } catch (\Throwable $e) {
            Log::warning('MemberVerification: approval email failed for user ' . $user->id . ': ' . $e->getMessage());
        }
    }

    public function generateVerificationMemberId(User $user, ?Carbon $verifiedAt = null): string
    {
        $verifiedAt = $verifiedAt ? $verifiedAt->copy() : now();
        $datePart = $verifiedAt->format('Ymd');
        $monthPrefix = $verifiedAt->format('Ym');

        $maxSequence = 0;

        User::where('uname', 'like', $monthPrefix . '%')
            ->pluck('uname')
            ->each(function ($uname) use ($monthPrefix, &$maxSequence) {
                if (!is_string($uname)) {
                    return;
                }

                if (preg_match('/^' . preg_quote($monthPrefix, '/') . '\d{2}(\d{4})[A-Z0-9]*$/', $uname, $matches)) {
                    $sequence = (int) $matches[1];
                    if ($sequence > $maxSequence) {
                        $maxSequence = $sequence;
                    }
                }
            });

        $nextSequence = max(1, $maxSequence + 1);

        for ($sequence = $nextSequence; $sequence <= 9999; $sequence++) {
            $memberId = $datePart . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            if (!$this->memberIdExistsForOtherUser($memberId, $user)) {
                return $memberId;
            }
        }

        $sequence = max(10000, $nextSequence);
        while (true) {
            $memberId = $datePart . (string) $sequence;
            if (!$this->memberIdExistsForOtherUser($memberId, $user)) {
                return $memberId;
            }
            $sequence++;
        }
    }

    private function memberIdExistsForOtherUser(string $memberId, User $user): bool
    {
        return User::where('uname', $memberId)
            ->where('id', '!=', $user->id)
            ->exists();
    }
}
