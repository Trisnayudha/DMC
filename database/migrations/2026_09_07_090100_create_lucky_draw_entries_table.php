<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLuckyDrawEntriesTable extends Migration
{
    /**
     * Log tiap undian Lucky Draw. lucky_draw_item_id null berarti "zonk"
     * (tidak dapat hadiah). Data penerima (name/company_name/job_title/
     * phone/email) & business_card_path semuanya opsional, mengikuti pola
     * visit_booth — pengunjung tidak wajib mengisi apa pun untuk ikut undian.
     * App-level FK saja, tanpa constraint DB (sama seperti
     * member_lead_follow_ups & tabel lain di app ini).
     *
     * drawn_at ditambahkan belakangan lewat migration terpisah
     * (add_drawn_at_to_lucky_draw_entries_table) — JANGAN ditambahkan lagi
     * di sini, tabel ini sudah kelanjur ke-migrate duluan sebelum kolom itu
     * dibutuhkan.
     */
    public function up()
    {
        Schema::create('lucky_draw_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lucky_draw_item_id')->nullable();
            $table->string('name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('job_title')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('business_card_path')->nullable();
            $table->timestamps();

            $table->index('lucky_draw_item_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('lucky_draw_entries');
    }
}
