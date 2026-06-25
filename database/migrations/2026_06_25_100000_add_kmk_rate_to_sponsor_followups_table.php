<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKmkRateToSponsorFollowupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * KMK rate (kurs pajak USD/IDR) diinput saat follow-up pertama — yaitu saat
     * renewal form di-generate — supaya nilai kontrak di renewal form mengacu ke
     * kurs yang dipakai saat proposal dikirim, bukan kurs live yang bisa berubah.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sponsor_followups', function (Blueprint $table) {
            $table->unsignedInteger('kmk_rate')->nullable()->after('notes');
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
            $table->dropColumn('kmk_rate');
        });
    }
}
