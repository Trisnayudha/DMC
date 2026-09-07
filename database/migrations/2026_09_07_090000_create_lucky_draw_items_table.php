<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLuckyDrawItemsTable extends Migration
{
    /**
     * Katalog hadiah Lucky Draw. chance_percent diatur manual oleh admin per
     * item (bukan dihitung dari stok seperti giveaway_items/visit booth yang
     * lama) — total chance_percent semua item is_active boleh kurang dari
     * 100%, sisanya otomatis jadi peluang "zonk" (lihat LuckyDrawService).
     * Item dengan chance_percent 0 tidak akan pernah keluar.
     */
    public function up()
    {
        Schema::create('lucky_draw_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image')->nullable();
            $table->decimal('chance_percent', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lucky_draw_items');
    }
}
