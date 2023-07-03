<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPicToUsersEvent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_event', function (Blueprint $table) {
            $table->dateTime('present')->nullable();
            $table->integer('pic_id_present')->nullable();
            $table->dateTime('reminder')->nullable();
            $table->integer('pic_id_reminder')->nullable();
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
