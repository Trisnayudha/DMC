<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGiveawayItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('giveaway_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_rare')->default(false);
            $table->integer('total_qty');
            $table->integer('remaining_qty');
            $table->integer('base_weight'); // bobot dasar
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
        Schema::dropIfExists('giveaway_items');
    }
}
