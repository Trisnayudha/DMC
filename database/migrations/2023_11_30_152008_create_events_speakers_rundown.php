<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsSpeakersRundown extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events_speakers_rundown', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('events_rundown_id');
            $table->integer('events_speakers_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events_speakers_rundown');
    }
}
