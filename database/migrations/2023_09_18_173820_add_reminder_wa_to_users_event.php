<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReminderWaToUsersEvent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_event', function (Blueprint $table) {
            $table->dateTime('reminder_wa')->nullable();
            $table->integer('pic_id_reminder_wa')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_event', function (Blueprint $table) {
            //
        });
    }
}
