<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventSponsors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_sponsors', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('events_id')->nullable();
            $table->string('sponsors_id')->nullable();
            $table->string('code_access')->nullable();
            $table->integer('count')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_sponsors');
    }
}
