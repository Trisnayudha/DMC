<?php

namespace App\Traits;

use App\Helpers\EmailSender;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait LogsProfileChanges
{
    /**
     * Fields that trigger an admin email notification when changed by the user.
     */
    private array $criticalFields = [
        'company_name',
        'company_category',
        'company_other',
        'prefix',
    ];

    /**
     * All fields we track (critical + non-critical).
     */
    private array $trackedFields = [
        'company_name',
        'company_category',
        'company_other',
        'prefix',
        'name',
        'job_title',
        'address',
        'city',
        'portal_code',
        'country',
        'office_number',
        'prefix_office_number',
        'full_office_number',
        'company_website',
    ];

    /**
     * Compare old vs new values, log changes, and notify admin on critical changes.
     *
     * @param  \App\Models\User  $user
     * @param  array<string,mixed>  $oldValues  ['field' => old_value, ...]
     * @param  array<string,mixed>  $newValues  ['field' => new_value, ...]
     * @param  string  $source  e.g. 'mobile_app' | 'web'
     */
    protected function trackProfileChanges($user, array $oldValues, array $newValues, string $source = 'user'): void
    {
        $changes = [];

        foreach ($this->trackedFields as $field) {
            $old = isset($oldValues[$field]) ? (string) $oldValues[$field] : '';
            $new = isset($newValues[$field]) ? (string) $newValues[$field] : '';

            if ($old !== $new) {
                $changes[$field] = ['old' => $old, 'new' => $new];
            }
        }

        if (empty($changes)) {
            return;
        }

        // Write to audit log
        DB::table('user_edit_logs')->insert([
            'user_id'    => $user->id,
            'admin_id'   => null,
            'admin_name' => 'User self-edit via ' . $source,
            'changes'    => json_encode($changes),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info('User self-edited profile', [
            'source'  => $source,
            'user_id' => $user->id,
            'email'   => $user->email,
            'changes' => $changes,
        ]);

        // Notify admin only if a critical field changed
        $criticalChanges = array_intersect_key($changes, array_flip($this->criticalFields));

        if (!empty($criticalChanges)) {
            $this->notifyAdminCriticalChange($user, $criticalChanges, $source);
        }
    }

    private function notifyAdminCriticalChange($user, array $criticalChanges, string $source): void
    {
        $adminEmail = env('ADMIN_NOTIFICATION_EMAIL', env('EMAIL_SENDER'));
        if (empty($adminEmail)) {
            return;
        }

        try {
            $lines = [];
            foreach ($criticalChanges as $field => $diff) {
                $label    = ucwords(str_replace('_', ' ', $field));
                $oldVal   = $diff['old'] ?: '(kosong)';
                $newVal   = $diff['new'] ?: '(kosong)';
                $lines[]  = "<li><strong>{$label}</strong>: <span style='color:#dc3545'>{$oldVal}</span> → <span style='color:#28a745'>{$newVal}</span></li>";
            }
            $listHtml = '<ul>' . implode('', $lines) . '</ul>';

            $send                = new EmailSender();
            $send->subject       = '[DMC] User mengubah data kritis — ' . ($user->name ?? $user->email);
            $send->template      = 'email.admin-profile-change-alert';
            $send->data          = [
                'user_name'      => $user->name ?? '-',
                'user_email'     => $user->email ?? '-',
                'user_id'        => $user->id,
                'source'         => $source,
                'changes_html'   => $listHtml,
                'admin_url'      => url('/admin/users'),
                'changed_at'     => now()->format('d M Y H:i'),
            ];
            $send->from          = env('EMAIL_SENDER');
            $send->name_sender   = env('EMAIL_NAME');
            $send->to            = $adminEmail;
            $send->sendEmail();
        } catch (\Throwable $e) {
            Log::warning('Admin notification failed for profile change', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
