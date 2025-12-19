<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dmc_sponsor_surveys', function (Blueprint $table) {
            $table->id();

            // Basic info
            $table->string('email');
            $table->string('name');
            $table->string('company');
            $table->string('type_of_sponsor');

            // Q1
            $table->string('promo_benefit_satisfaction');
            $table->string('promo_benefit_other')->nullable();

            // Q2
            $table->string('event_attendance_satisfaction');
            $table->string('event_attendance_other')->nullable();

            // Q3
            $table->string('live_event_branding_benefit');
            $table->string('live_event_branding_other')->nullable();

            // Q4
            $table->string('additional_value_satisfaction');
            $table->string('additional_value_other')->nullable();

            // Q5
            $table->string('price_alignment');
            $table->string('price_alignment_other')->nullable();

            // Q6
            $table->string('brand_visibility');
            $table->string('brand_visibility_other')->nullable();

            // Q7
            $table->string('team_responsiveness');
            $table->string('team_responsiveness_other')->nullable();

            // Q8
            $table->string('preferred_communication');
            $table->string('preferred_communication_other')->nullable();

            // Q9
            $table->string('mobile_app_awareness');

            // Q10
            $table->string('commodity_map_awareness');

            // Q11
            $table->string('new_program_awareness');

            // Q12
            $table->string('overall_experience');
            $table->string('overall_experience_other')->nullable();

            // Q13
            $table->string('renewal_interest');
            $table->string('renewal_interest_other')->nullable();

            // Q14–16 (paragraph)
            $table->text('renewal_reason')->nullable();
            $table->text('future_benefit_suggestion');
            $table->text('overall_experience_suggestion');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dmc_sponsor_surveys');
    }
};
