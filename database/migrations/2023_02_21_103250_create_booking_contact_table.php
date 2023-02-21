<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingContactTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_contact', function (Blueprint $table) {
            $table->id();
            $table->string('name_contact')->nullable();
            $table->string('email_contact')->nullable();
            $table->string('phone_contact')->nullable();
            $table->string('job_title_contact')->nullable();
            $table->string('prefix')->nullable();
            $table->string('company_name')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('company_website')->nullable();
            $table->string('country')->nullable();
            $table->string('portal_code')->nullable();
            $table->string('company_category')->nullable();
            $table->string('company_other')->nullable();
            $table->string('office_number')->nullable();
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
        Schema::dropIfExists('booking_contact');
    }
}
