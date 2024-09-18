<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExhibitorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exhibitors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('country');
            $table->text('desc')->nullable();
            $table->string('website')->nullable();
            $table->string('contact')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('display_email')->nullable();
            $table->string('venue_hall')->nullable();
            $table->string('event_name')->nullable();
            $table->string('exhibitor_logo')->nullable();
            $table->string('booth_number')->nullable();
            $table->string('category1')->nullable();
            $table->string('category2')->nullable();
            $table->timestamps(); // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exhibitors');
    }
}
