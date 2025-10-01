<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMiningindoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('miningindo', function (Blueprint $table) {
            // gunakan id external sebagai PK agar upsert gampang
            $table->unsignedBigInteger('id')->primary(); // id dari API (contoh: 3999)
            $table->string('name')->nullable();
            $table->string('country')->nullable();
            $table->text('desc')->nullable();
            $table->string('category1')->nullable();
            $table->string('category2')->nullable();
            $table->string('website')->nullable();
            $table->string('contact')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('venue_hall')->nullable();
            $table->string('pavilion')->nullable();
            $table->json('raw_json')->nullable(); // optional, untuk audit
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('miningindo');
    }
}
