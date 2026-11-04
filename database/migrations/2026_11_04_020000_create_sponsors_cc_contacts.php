<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSponsorsCcContacts extends Migration
{
    public function up()
    {
        Schema::create('sponsors_cc_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sponsor_id');
            $table->string('salutation', 5)->nullable();
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->timestamps();
            $table->foreign('sponsor_id')->references('id')->on('sponsors')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sponsors_cc_contacts');
    }
}
