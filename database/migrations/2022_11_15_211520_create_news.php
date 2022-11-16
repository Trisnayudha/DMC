<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('highlight')->default('No')->nullable();
            $table->string('all_highlight')->default('No')->nullable();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->string('news_category_id')->nullable();
            $table->string('image')->nullable();
            $table->string('location')->nullable();
            $table->dateTime('date_news')->nullable();
            $table->longText('desc')->nullable();
            $table->integer('views')->nullable();
            $table->integer('share')->nullable();
            $table->string('reference_link')->nullable();
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
        Schema::dropIfExists('news');
    }
}
