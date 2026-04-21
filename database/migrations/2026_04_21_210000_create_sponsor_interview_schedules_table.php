<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sponsor_interview_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sponsor_id')->index();
            $table->string('company_name');
            $table->string('sponsor_package', 20);
            $table->unsignedTinyInteger('number_of_interviewees');
            $table->json('interviewees');
            $table->string('preferred_time_slot', 30);
            $table->json('selected_questions');
            $table->timestamps();

            $table->unique('preferred_time_slot', 'uniq_preferred_time_slot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsor_interview_schedules');
    }
};
