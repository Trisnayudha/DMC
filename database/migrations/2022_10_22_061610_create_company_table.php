<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('company_name')->nullable();
            $table->string('company_website')->nullable();
            $table->string('company_category')->nullable();
            $table->string('company_other')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('portal_code')->nullable();
            $table->string('prefix_office_number')->nullable();
            $table->string('office_number')->nullable();
            $table->string('country')->nullable();
            $table->string('cci')->nullable();
            $table->string('explore')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company');
    }
}
