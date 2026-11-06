<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMailchimpUnsubscribeSnapshotsTable extends Migration
{
    public function up()
    {
        Schema::create('mailchimp_unsubscribe_snapshots', function (Blueprint $table) {
            $table->string('list_id')->primary();
            $table->longText('members')->nullable();
            $table->longText('activities')->nullable();
            $table->longText('activity_retry')->nullable();
            $table->longText('stats')->nullable();
            $table->longText('local_resubscribes')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->string('sync_token', 64)->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mailchimp_unsubscribe_snapshots');
    }
}
