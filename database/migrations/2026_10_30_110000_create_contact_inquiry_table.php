<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactInquiryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Simpan pesan Contact Us (web & apps) yang sebelumnya hanya dikirim lewat
     * email. source: 'web' (form publik) | 'apps' (user login, pakai category).
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_inquiry', function (Blueprint $table) {
            $table->id();
            $table->string('source', 10)->index();
            $table->unsignedBigInteger('users_id')->nullable()->index();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone', 30)->nullable();
            $table->string('category')->nullable();
            $table->text('message')->nullable();
            // Hanya untuk data rekap lama dari Gmail (contact-inquiry:import), mencegah import dobel.
            $table->string('email_message_id', 40)->nullable()->unique();
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
        Schema::dropIfExists('contact_inquiry');
    }
}
