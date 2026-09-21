<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `source` and `hear` were conflated: the "How did you hear about us?"
 * marketing answer (Website/Linkedin/Instagram/Event/Other) was being sent by
 * the frontend under the `source` param and silently overwrote the real
 * registration-channel value. `hear` is a new, separate column for that
 * marketing answer, so `source` can stay limited to the actual channels
 * (Website, Apps, Event Partnership, Linkedin, plus the existing
 * scanner/EP-code special cases).
 *
 * Existing rows have no separate `hear` data — whatever was in `source`
 * before this fix is the closest we have to it (that's literally the bug),
 * so backfill `hear` from `source` as a starting point rather than leaving
 * old rows blank. Only new registrations going forward get the two split
 * correctly by the app code.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('hear')->nullable()->after('source');
        });

        Schema::table('xtwp_users_dmc', function (Blueprint $table) {
            $table->string('hear')->nullable()->after('source');
        });

        DB::table('users')->whereNotNull('source')->update(['hear' => DB::raw('source')]);
        DB::table('xtwp_users_dmc')->whereNotNull('source')->update(['hear' => DB::raw('source')]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('hear');
        });

        Schema::table('xtwp_users_dmc', function (Blueprint $table) {
            $table->dropColumn('hear');
        });
    }
};
