<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJobTitleFieldsToMemberCompanyFollowUpsTable extends Migration
{
    public function up()
    {
        Schema::table('member_company_follow_ups', function (Blueprint $table) {
            $table->string('previous_job_title')->nullable()->after('previous_company_name');
            $table->string('new_job_title')->nullable()->after('new_company_name');
        });
    }

    public function down()
    {
        Schema::table('member_company_follow_ups', function (Blueprint $table) {
            $table->dropColumn(['previous_job_title', 'new_job_title']);
        });
    }
}
