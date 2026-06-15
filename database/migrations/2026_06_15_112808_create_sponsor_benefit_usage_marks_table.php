<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSponsorBenefitUsageMarksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sponsor_benefit_usage_marks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sponsor_benefit_usage_id');
            $table->date('marked_at');                   // tanggal bisa ditentukan manual
            $table->string('note')->nullable();
            $table->string('proof_image')->nullable();   // path file, optional
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('sponsor_benefit_usage_id')
                ->references('id')->on('sponsor_benefit_usage')->onDelete('cascade');
            $table->foreign('created_by')
                ->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sponsor_benefit_usage_marks');
    }
}
