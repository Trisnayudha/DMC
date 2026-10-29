<?php

use App\Models\MemberLeadFollowUp;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RecalculateMemberLeadDeadlines extends Migration
{
    public function up()
    {
        MemberLeadFollowUp::orderBy('id')->chunkById(200, function ($leads) {
            foreach ($leads as $lead) {
                if ($lead->second_follow_up_at) {
                    $deadline = null;
                } elseif ($lead->first_follow_up_at) {
                    $deadline = MemberLeadFollowUp::deadlineFor('follow_up_2', $lead->first_follow_up_at);
                } elseif ($lead->sponsorkit_sent_at) {
                    $deadline = MemberLeadFollowUp::deadlineFor('follow_up_1', $lead->sponsorkit_sent_at);
                } else {
                    $deadline = MemberLeadFollowUp::deadlineFor('sponsorkit', $lead->created_at);
                }
                DB::table('member_lead_follow_ups')->where('id', $lead->id)->update(['deadline_at' => $deadline]);
            }
        });
    }

    public function down()
    {
        // Historical deadline values cannot be reconstructed.
    }
}
