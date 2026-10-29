<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConfirmedRenewalToSponsorFollowupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Hasil follow-up: sponsor sudah konfirmasi renew. Follow-up yang ditandai
     * confirmed memicu email ke finance supaya invoice dibuat (manual di Accurate).
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sponsor_followups', function (Blueprint $table) {
            $table->boolean('confirmed_renewal')->default(false)->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sponsor_followups', function (Blueprint $table) {
            $table->dropColumn('confirmed_renewal');
        });
    }
}
