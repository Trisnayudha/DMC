<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRevisionFieldsToDmcMemberSurveys extends Migration
{
    public function up()
    {
        Schema::table('dmc_member_surveys', function (Blueprint $table) {
            $table->string('event_types_other')->nullable();
            $table->string('nominee_reason')->nullable();
            $table->json('event_improvement_aspects')->nullable();
            $table->string('event_improvement_other')->nullable();
            $table->json('email_frequency')->nullable();
        });
    }

    public function down()
    {
        Schema::table('dmc_member_surveys', function (Blueprint $table) {
            $table->dropColumn(['event_types_other', 'nominee_reason', 'event_improvement_aspects', 'event_improvement_other', 'email_frequency']);
        });
    }
}
