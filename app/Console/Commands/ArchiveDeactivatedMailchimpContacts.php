<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Membership\MemberVerificationService;
use Illuminate\Console\Command;

/**
 * One-time backlog cleanup: archive (not unsubscribe) every currently
 * deactivated member's email from Mailchimp, since archived contacts don't
 * count toward billing while unsubscribed ones still do.
 *
 * Going forward this happens automatically per-member — UsersController::
 * deactivateMember() already calls archiveFromMailchimp() when an admin
 * deactivates someone. This command is only for members deactivated before
 * that existed, or whose archive call failed at the time.
 *
 * Safe to re-run: archiveEmailFromMailchimp() DELETEs the contact, and a
 * contact that's already archived/never subscribed just 404s — not an error.
 */
class ArchiveDeactivatedMailchimpContacts extends Command
{
    protected $signature = 'mailchimp:archive-deactivated {--dry-run : List candidates without calling Mailchimp} {--limit= : Only process the first N candidates (for a small test run)}';
    protected $description = "Archive every currently deactivated member's email from Mailchimp (bulk backlog cleanup)";

    public function handle(MemberVerificationService $verificationService)
    {
        $users = User::where('status_member', 'deactivated')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get(['id', 'name', 'email', 'deactivated_at']);

        $this->info("Found {$users->count()} deactivated member(s) with an email on file.");

        if ($this->option('limit')) {
            $users = $users->take((int) $this->option('limit'));
            $this->line('--limit set: only processing the first ' . $users->count() . '.');
        }

        if ($this->option('dry-run')) {
            $this->table(
                ['ID', 'Name', 'Email', 'Deactivated At'],
                $users->map(function ($u) {
                    return [$u->id, $u->name, $u->email, $u->deactivated_at];
                })->all()
            );
            $this->line('Dry run only — no Mailchimp calls made. Re-run without --dry-run to actually archive.');
            return 0;
        }

        $archived = 0;
        $invalidEmail = 0;
        $notArchived = 0;

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                $invalidEmail++;
                $bar->advance();
                continue;
            }

            $result = $verificationService->archiveEmailFromMailchimp($user->email, $user->id);

            if ($result) {
                $archived++;
            } else {
                // Covers both "already not in the audience" (the common case —
                // never subscribed, or already archived) and a real API
                // failure. Real failures are logged as warnings by the service
                // itself (storage/logs/laravel.log, "Mailchimp archive
                // failed") — this count alone can't tell the two apart.
                $notArchived++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Archived just now: {$archived}");
        $this->info("Skipped (invalid/blank email): {$invalidEmail}");
        $this->line("Not archived: {$notArchived} — almost always means they were already out of the Mailchimp audience (never subscribed, or already archived/deleted there). Check storage/logs/laravel.log for \"Mailchimp archive failed\" if you want to confirm none of these were real failures.");

        return 0;
    }
}
