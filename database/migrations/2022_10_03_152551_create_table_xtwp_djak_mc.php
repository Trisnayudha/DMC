<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableXtwpDjakMc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xtwp_users_dmc', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('prefix')->nullable();
            $table->string('company_name')->nullable();
            $table->string('name')->nullable();
            $table->string('job_title')->nullable();
            $table->string('prefix_phone')->nullable();
            $table->string('phone')->nullable();
            $table->string('fullphone')->nullable();
            $table->string('email')->nullable();
            $table->string('company_website')->nullable();
            $table->string('company_category')->nullable();
            $table->string('company_other')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('portal_code')->nullable();
            $table->string('prefix_office_number')->nullable();
            $table->string('office_number')->nullable();
            $table->string('full_office_number')->nullable();
            $table->string('country')->nullable();
            $table->string('cci')->nullable();
            $table->string('explore')->nullable();
            $table->string('password')->nullable();
            $table->string('otp')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_xtwp_djak_mc');
    }
}
