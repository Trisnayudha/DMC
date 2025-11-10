<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSurveyResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();

            $table->string('email');
            $table->unsignedTinyInteger('informative_score');

            // JSON untuk multi-pilihan (WAJIB, tanpa default)
            $table->json('most_relevant_presentations');

            // boolean/tinyint lebih enak diproses dibanding enum string
            $table->boolean('is_member')->nullable();        // validator-mu mewajibkan, bisa dibuat non-nullable juga
            $table->boolean('wants_more_info')->nullable();  // sama seperti di atas

            $table->text('feedback')->nullable();
            $table->text('topics_2026')->nullable();

            $table->string('ip', 45)->nullable();
            $table->string('ua', 255)->nullable();

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
        Schema::dropIfExists('survey_responses');
    }
}
