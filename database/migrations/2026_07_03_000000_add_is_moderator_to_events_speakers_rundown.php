<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsModeratorToEventsSpeakersRundown extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events_speakers_rundown', function (Blueprint $table) {
            $table->boolean('is_moderator')->default(0)->after('events_speakers_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events_speakers_rundown', function (Blueprint $table) {
            $table->dropColumn('is_moderator');
        });
    }
}
