<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLangToSponsorsAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sponsors_address', function (Blueprint $table) {
            // Menyimpan nilai latitude dengan presisi 10 digit dan 7 angka di belakang koma
            $table->decimal('lat', 10, 7);
            // Menyimpan nilai lang (longitude) dengan presisi yang sama
            $table->decimal('lang', 10, 7);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sponsors_address', function (Blueprint $table) {
            //
        });
    }
}
