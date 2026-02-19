<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewsPartners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news_partners', function (Blueprint $table) {
            $table->id();
            $table->string('partner_name', 255);
            $table->string('partner_position', 255)->nullable();
            $table->string('partner_company', 255)->nullable();
            $table->string('partner_website', 255)->nullable();
            $table->string('partner_image', 255)->nullable(); // simpan path/url
            $table->string('partner_quote', 255)->nullable();
            $table->timestamps();

            $table->foreign('news_id')->references('id')->on('news')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('news_partners');
    }
}
