<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSponsorkitSentAtToMemberLeadFollowUpsTable extends Migration
{
    /**
     * Lead Follow-Up flow is now 3 steps: Kirim Sponsorkit → Follow Up 1 →
     * Follow Up 2 (previously just "1st/2nd follow up", with no sponsorkit
     * step). first_follow_up_at/second_follow_up_at are reused as-is for
     * Follow Up 1/2 — only the sponsorkit step is new.
     */
    public function up()
    {
        Schema::table('member_lead_follow_ups', function (Blueprint $table) {
            $table->timestamp('sponsorkit_sent_at')->nullable()->after('deadline_at');
        });
    }

    public function down()
    {
        Schema::table('member_lead_follow_ups', function (Blueprint $table) {
            $table->dropColumn('sponsorkit_sent_at');
        });
    }
}
