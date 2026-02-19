<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSponsorsIdToNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('news', function (Blueprint $table) {
            $table->unsignedBigInteger('sponsors_id')->nullable()->after('news_partners_id');

            // kalau tabel sponsor kamu namanya "sponsors"
            $table->foreign('sponsors_id')
                ->references('id')->on('sponsors')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropForeign(['sponsors_id']);
            $table->dropColumn('sponsors_id');
        });
    }
}
