<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSponsorsAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sponsors_address', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('link_gmaps')->nullable();
            $table->string('address')->nullable();
            $table->string('country')->nullable();
            $table->string('image_country')->nullable();
            $table->integer('sponsor_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sponsors_address');
    }
}
