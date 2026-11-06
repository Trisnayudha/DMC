<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\MailchimpUnsubscribeController;
use App\Services\Mailchimp\UnsubscribeSnapshotService;
use Illuminate\Console\Command;

class SyncMailchimpUnsubscribes extends Command
{
    protected $signature = 'mailchimp:sync-unsubscribes {--force : Sync even if the snapshot is fresh}';
    protected $description = 'Update the local Mailchimp unsubscribe snapshot';

    public function handle(UnsubscribeSnapshotService $service)
    {
        $config = app(MailchimpUnsubscribeController::class)->mailchimpConfig();
        if (!$config) {
            $this->error('Mailchimp is not configured.');
            return 1;
        }
        $service->syncIfDue($config, $this->option('force'));
        return 0;
    }
}
