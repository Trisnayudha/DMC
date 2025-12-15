<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailPreferenceFieldsToDmcMemberSurveysTable extends Migration
{
    public function up()
    {
        Schema::table('dmc_member_surveys', function (Blueprint $table) {

            /* =========================
             * EMAIL COMMUNICATION PREFERENCES
             * ========================= */

            // Primary goal when opening emails
            $table->json('email_primary_goal')
                ->nullable()
                ->after('usage_frequency');

            // Other (specify)
            $table->string('email_primary_goal_other')
                ->nullable()
                ->after('email_primary_goal');

            // Best day to receive emails
            $table->json('email_best_day')
                ->nullable()
                ->after('email_primary_goal_other');
        });
    }

    public function down()
    {
        Schema::table('dmc_member_surveys', function (Blueprint $table) {
            $table->dropColumn([
                'email_primary_goal',
                'email_primary_goal_other',
                'email_best_day',
            ]);
        });
    }
}
