<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Some leads (Explore Marketing interest) turn out to be competitors or just
 * not serious ("iseng") — management doesn't want the sponsor kit sent to
 * them. This flag lets a PIC mark a lead as ineligible so the "Kirim
 * Sponsorkit" action is blocked for it, without having to mark it Loss
 * (Loss implies it was genuinely pursued and didn't convert — this is a
 * different, earlier disqualification).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_lead_follow_ups', function (Blueprint $table) {
            $table->boolean('do_not_send')->default(false)->after('result');
            $table->string('do_not_send_reason')->nullable()->after('do_not_send');
            $table->unsignedBigInteger('do_not_send_by_id')->nullable()->after('do_not_send_reason');
            $table->string('do_not_send_by_name')->nullable()->after('do_not_send_by_id');
            $table->timestamp('do_not_send_at')->nullable()->after('do_not_send_by_name');
        });
    }

    public function down(): void
    {
        Schema::table('member_lead_follow_ups', function (Blueprint $table) {
            $table->dropColumn(['do_not_send', 'do_not_send_reason', 'do_not_send_by_id', 'do_not_send_by_name', 'do_not_send_at']);
        });
    }
};
