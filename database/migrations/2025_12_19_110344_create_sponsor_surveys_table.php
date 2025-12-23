<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dmc_sponsor_surveys', function (Blueprint $table) {
            $table->id();

            // === Basic Identity ===
            $table->string('email');
            $table->string('name');
            $table->string('company');

            // === Quick Feedback Questions ===

            // Q1
            $table->string('program_familiarity');

            // Q2
            $table->string('branding_value');

            // Q3
            $table->string('brand_visibility');

            // Q4
            $table->string('team_support');

            // Q5
            $table->string('renewal_interest');

            // Q6
            $table->text('improvement_suggestion');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dmc_sponsor_surveys');
    }
};
