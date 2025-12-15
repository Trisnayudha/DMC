<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDmcMemberSurveysTable extends Migration
{
    public function up()
    {
        Schema::create('dmc_member_surveys', function (Blueprint $table) {
            $table->bigIncrements('id');

            /* =========================
             * 1. MEMBER INFORMATION
             * ========================= */
            $table->string('full_name');
            $table->string('company');
            $table->string('position')->nullable();
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->string('linkedin')->nullable();

            /* =========================
             * 2. PROGRAM EXPECTATION
             * ========================= */
            $table->json('event_types');
            $table->text('topics_interest')->nullable();
            $table->text('speaker_wishlist')->nullable();
            $table->string('nominee_name')->nullable();
            $table->string('nominee_company')->nullable();
            $table->text('event_improvement')->nullable();

            /* =========================
             * 3. MARKETING & COMMUNICATION
             * ========================= */
            $table->json('social_familiarity');
            $table->json('platforms')->nullable();
            $table->json('app_awareness');
            $table->json('usage_frequency')->nullable();
            $table->json('preferred_channels')->nullable();
            $table->text('communication_feedback')->nullable();

            /* =========================
             * 4. ADDITIONAL
             * ========================= */
            $table->text('additional_feedback')->nullable();

            /* =========================
             * METADATA
             * ========================= */
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent', 255)->nullable();

            $table->timestamps();

            /* =========================
             * INDEX
             * ========================= */
            $table->index('email');
            $table->index('company');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dmc_member_surveys');
    }
}
