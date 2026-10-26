<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lead temperature (HOT / WARM / COLD) from the SOP scoring checklist — same
 * model as IM-DMU's LeadScoringService: six on/off categories summing to 100,
 * see App\Services\LeadTemperatureService. temperature + temperature_score are
 * stored (not computed on the fly) so the list can filter on them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_lead_follow_ups', function (Blueprint $table) {
            $table->string('temperature', 10)->default('cold')->index()->after('result');
            $table->unsignedTinyInteger('temperature_score')->default(0)->after('temperature');
            $table->boolean('score_company_title')->default(false)->after('temperature_score');
            $table->boolean('score_attendance_history')->default(false)->after('score_company_title');
            $table->boolean('score_digital_engagement')->default(false)->after('score_attendance_history');
            $table->boolean('score_followup_response')->default(false)->after('score_digital_engagement');
            $table->boolean('score_price_agenda_inquiry')->default(false)->after('score_followup_response');
            $table->boolean('score_budget_timeline')->default(false)->after('score_price_agenda_inquiry');
        });
    }

    public function down(): void
    {
        Schema::table('member_lead_follow_ups', function (Blueprint $table) {
            $table->dropIndex(['temperature']);
            $table->dropColumn([
                'temperature',
                'temperature_score',
                'score_company_title',
                'score_attendance_history',
                'score_digital_engagement',
                'score_followup_response',
                'score_price_agenda_inquiry',
                'score_budget_timeline',
            ]);
        });
    }
};
