<?php

namespace App\Support;

use App\Models\CmsUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class CmsLoginOtp
{
    public const LIFETIME_MINUTES = 10;
    private const MAX_ATTEMPTS = 5;

    public static function send(CmsUser $user): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $hash = Hash::make($code);
        // Competing sends (including another password login) share the same cooldown.
        $updated = CmsUser::whereKey($user->id)
            ->where('otp_enabled', true)
            ->where('login_otp_hash', $user->login_otp_hash)
            ->where(function ($query) {
                $query->whereNull('login_otp_sent_at')->orWhere('login_otp_sent_at', '<=', now()->subMinute());
            })
            ->update(['login_otp_hash' => $hash, 'login_otp_sent_at' => now(), 'login_otp_attempts' => 0]);

        if (!$updated) {
            throw ValidationException::withMessages(['email' => 'Please wait 60 seconds before requesting another code.']);
        }

        try {
            $minutes = self::LIFETIME_MINUTES;
            $views = [
                'html' => 'emails.cms_login_otp',
                'raw' => "Your DMC Admin login code is {$code}. It expires in {$minutes} minutes.",
            ];
            Mail::send($views, ['code' => $code, 'minutes' => $minutes], function ($message) use ($user) {
                $message->from(config('mail.from.address'), 'DMC Admin')
                    ->to($user->email)->subject('Your DMC Admin login code');
            });
            if (Mail::failures()) {
                throw new \RuntimeException('Recipient rejected');
            }
        } catch (\Exception $exception) {
            CmsUser::whereKey($user->id)->where('login_otp_hash', $hash)->update(['login_otp_hash' => null]);
            Log::warning('CMS login code delivery failed', ['cms_user_id' => $user->id, 'exception' => get_class($exception)]);
            throw ValidationException::withMessages(['email' => 'We could not send your code. Please sign in again in a minute.'])
                ->redirectTo(route('cms.login'));
        }

        return self::signature($user, $hash);
    }

    // Bind the password-verified browser session to this code and the credentials used to request it.
    private static function signature(CmsUser $user, string $hash): string
    {
        return hash_hmac('sha256', $user->email . '|' . $user->getAuthPassword() . '|' . $hash, config('app.key'));
    }

    public static function pendingUser(array $pending, bool $lock = false): ?CmsUser
    {
        if (empty($pending['user_id']) || empty($pending['signature'])) {
            return null;
        }
        $query = CmsUser::whereKey($pending['user_id']);
        $user = ($lock ? $query->lockForUpdate() : $query)->first();
        if (!$user || !$user->otp_enabled || !$user->is_active || !$user->login_otp_hash
            || !$user->login_otp_sent_at || $user->login_otp_sent_at->lte(now()->subMinutes(self::LIFETIME_MINUTES))
            || $user->login_otp_attempts >= self::MAX_ATTEMPTS
            || !hash_equals(self::signature($user, $user->login_otp_hash), $pending['signature'])) {
            return null;
        }
        return $user;
    }

    public static function verify(array $pending, string $code): ?CmsUser
    {
        return DB::transaction(function () use ($pending, $code) {
            $user = self::pendingUser($pending, true);
            if (!$user) {
                return null;
            }
            if (!Hash::check($code, $user->login_otp_hash)) {
                $user->increment('login_otp_attempts');
                return null;
            }
            $user->login_otp_hash = null;
            $user->save();
            return $user;
        });
    }
}
