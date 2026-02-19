<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToNews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('news', function (Blueprint $table) {
            $table->enum('type', ['default', 'partnership', 'sponsor'])
                ->default('default')
                ->after('slug');
            $table->unsignedBigInteger('news_partners_id')->nullable()->after('type');

            // kalau mau FK (recommended)


            // optional, kalau butuh listing ringkas
            // $table->string('excerpt', 255)->nullable()->after('title');
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
            //
        });
    }
}
