<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTicket extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events_tickets', function (Blueprint $table) {
            $table->id();
            $table->integer('events_id')->nullable();
            $table->string('title')->nullable();
            $table->double('price_rupiah', 2)->nullable();
            $table->double('price_dollar', 2)->nullable();
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            $table->string('status_ticket')->nullable();
            $table->string('status_sold')->nullable();
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
        Schema::dropIfExists('events_ticket');
    }
}
