<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKmkNumberToSponsorRenewalFormsTable extends Migration
{
    /**
     * Nomor KMK (mis. "30/MK/EF.2/2026") diinput manual saat generate renewal form,
     * bersama KMK rate. Diambil dari https://fiskal.kemenkeu.go.id/informasi-publik/kurs-pajak
     * dan ditampilkan di dokumen renewal form.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sponsor_renewal_forms', function (Blueprint $table) {
            $table->string('kmk_number', 50)->nullable()->after('kmk_rate');
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::table('sponsor_renewal_forms', function (Blueprint $table) {
            $table->dropColumn('kmk_number');
        });
    }
}
