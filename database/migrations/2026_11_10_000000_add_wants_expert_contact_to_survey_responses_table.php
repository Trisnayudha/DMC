<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWantsExpertContactToSurveyResponsesTable extends Migration
{
    public function up()
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            // "Speak further with the McCloskey experts for deeper insights and a free trial" (Yes/No)
            $table->boolean('wants_expert_contact')->nullable()->after('topic_recommendation');
        });
    }

    public function down()
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropColumn('wants_expert_contact');
        });
    }
}
