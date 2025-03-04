<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackageBenefitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_benefit', function (Blueprint $table) {
            $table->id();
            $table->string('package_name'); // misalnya: platinum, gold, silver
            $table->unsignedBigInteger('benefit_id');
            $table->integer('quantity')->default(1); // jika ada benefit dengan batasan jumlah
            $table->text('additional_info')->nullable(); // untuk informasi tambahan jika diperlukan
            $table->timestamps();

            $table->foreign('benefit_id')->references('id')->on('benefits')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('package_benefit');
    }
}
