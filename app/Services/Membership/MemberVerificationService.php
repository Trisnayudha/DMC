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

    public function syncToMailchimp(User $user): void
    {
        $email = strtolower(trim($user->email ?? ''));
        $company = CompanyModel::where('users_id', $user->id)->first();
        $profile = ProfileModel::where('users_id', $user->id)->first();

        try {
            $apiKey = config('newsletter.apiKey') ?: env('MAILCHIMP_APIKEY');
            $server = config('newsletter.server') ?: (explode('-', $apiKey)[1] ?? null);
            $listId = config('newsletter.lists.subscribers.id') ?: env('MAILCHIMP_LIST_ID');

            if ($apiKey && $server && $listId) {
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

                $subscriberHash = md5($email);
                Http::withBasicAuth('anystring', $apiKey)
                    ->timeout(20)
                    ->put("https://{$server}.api.mailchimp.com/3.0/lists/{$listId}/members/{$subscriberHash}", [
                        'email_address' => $email,
                        'status_if_new' => 'subscribed',
                        'status'        => 'subscribed',
                        'merge_fields'  => $merge,
                    ]);
            }
        } catch (\Throwable $e) {
            Log::warning('MemberVerification: Mailchimp import failed for user ' . $user->id . ': ' . $e->getMessage());
        }
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
