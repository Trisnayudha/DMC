<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOtherToPayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment', function (Blueprint $table) {
            $table->integer('events_id')->nullable();
            $table->integer('tickets_id')->nullable();
            $table->dropColumn('price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment', function (Blueprint $table) {
            //
        });
    }
}
