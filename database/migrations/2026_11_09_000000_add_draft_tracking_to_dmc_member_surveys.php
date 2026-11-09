<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Autosave support for the member survey.
 * - status: in_progress (draft, autosaved while filling) | completed (submitted)
 * - draft_token: random token kept in the respondent's cookie to resume the draft
 * - last_step: furthest step the respondent reached (1-4)
 * - completed_at: when the survey was submitted
 * Existing rows are all real submissions, so they default to completed.
 */
return new class extends Migration {
    public function up()
    {
        Schema::table('dmc_member_surveys', function (Blueprint $table) {
            $table->string('status', 20)->default('completed')->after('id')->index();
            $table->string('draft_token', 64)->nullable()->unique()->after('status');
            $table->unsignedTinyInteger('last_step')->nullable()->after('draft_token');
            $table->timestamp('completed_at')->nullable()->after('last_step');
        });

        DB::table('dmc_member_surveys')->whereNull('completed_at')->update(['completed_at' => DB::raw('created_at')]);
    }

    public function down()
    {
        Schema::table('dmc_member_surveys', function (Blueprint $table) {
            $table->dropUnique(['draft_token']);
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'draft_token', 'last_step', 'completed_at']);
        });
    }
};
