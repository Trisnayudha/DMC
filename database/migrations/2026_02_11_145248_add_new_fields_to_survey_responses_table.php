<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToSurveyResponsesTable extends Migration
{
    public function up()
    {
        Schema::table('survey_responses', function (Blueprint $table) {

            // identifier event
            $table->string('event_code')->nullable()->after('email');

            // new survey structure
            $table->tinyInteger('event_rating')->nullable()->after('event_code');
            $table->text('improvement_feedback')->nullable()->after('event_rating');
            $table->text('topic_recommendation')->nullable()->after('improvement_feedback');
        });
    }

    public function down()
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropColumn([
                'event_code',
                'event_rating',
                'improvement_feedback',
                'topic_recommendation',
            ]);
        });
    }
}
