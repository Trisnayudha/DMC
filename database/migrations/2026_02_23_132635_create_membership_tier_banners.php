<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMembershipTierBanners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('membership_tier_banners', function (Blueprint $table) {
            $table->id();

            // tier user: reguler / black
            $table->enum('tier', ['reguler', 'black'])->default('reguler');

            // untuk bedain 2 carousel: misal dashboard_left & dashboard_right
            $table->string('section_key', 50)->default('dashboard_left');

            $table->string('title')->nullable();

            // image URL/path (kita simpan seperti contoh kamu: /storage/xxx/filename.png)
            $table->string('image');

            // linkable
            $table->string('link_url')->nullable();
            $table->boolean('open_new_tab')->default(false);

            $table->integer('sort_order')->default(1);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // $table->index(['tier', 'section_key', 'is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('membership_tier_banners');
    }
}
