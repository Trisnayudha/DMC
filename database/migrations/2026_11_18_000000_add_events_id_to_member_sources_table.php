<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Links a member source (registration link, e.g. EP/MI2026) to the event it belongs to,
 * so members who registered through an EP/ link are credited as that event's conversions.
 */
class AddEventsIdToMemberSourcesTable extends Migration
{
    /** Existing EP codes → partnership event id. */
    const EXISTING = [
        'MI2026'         => 72, // Mining Indonesia 2026
        'CTICMMS2026'    => 70, // International Critical Minerals & Metals Summit: Indonesia 2026 (booth visitors are recorded here)
        'BALIKPAPAN2026' => 67, // Mining Balikpapan
    ];

    public function up()
    {
        if (!Schema::hasColumn('member_sources', 'events_id')) {
            Schema::table('member_sources', function (Blueprint $table) {
                $table->unsignedBigInteger('events_id')->nullable()->after('form_type')->index();
            });
        }

        foreach (self::EXISTING as $code => $eventId) {
            if (!DB::table('events')->where('id', $eventId)->exists()) {
                continue;
            }
            DB::table('member_sources')
                ->where('category', 'EP')
                ->whereRaw('UPPER(code) = ?', [$code])
                ->whereNull('events_id')
                ->update(['events_id' => $eventId]);
        }
    }

    public function down()
    {
        if (Schema::hasColumn('member_sources', 'events_id')) {
            Schema::table('member_sources', function (Blueprint $table) {
                $table->dropIndex(['events_id']);
                $table->dropColumn('events_id');
            });
        }
    }
}
