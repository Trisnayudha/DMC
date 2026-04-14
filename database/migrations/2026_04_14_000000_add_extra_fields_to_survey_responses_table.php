<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtraFieldsToSurveyResponsesTable extends Migration
{
    public function up()
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->tinyInteger('rating_content')->nullable()->after('event_rating');
            $table->tinyInteger('rating_networking')->nullable()->after('rating_content');
            $table->text('liked_most')->nullable()->after('rating_networking');
            $table->string('app_activated', 20)->nullable()->after('topic_recommendation');
        });
    }

    public function down()
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropColumn([
                'rating_content',
                'rating_networking',
                'liked_most',
                'app_activated',
            ]);
        });
    }
}
