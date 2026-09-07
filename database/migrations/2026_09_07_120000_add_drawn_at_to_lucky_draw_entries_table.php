<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDrawnAtToLuckyDrawEntriesTable extends Migration
{
    /**
     * Business card capture was decoupled from the actual draw (camera
     * auto-uploads the card first via a separate request, creating a row
     * before the visitor ever clicks "Draw Now") — so a row can sit with no
     * item AND no draw yet. drawn_at marks the moment the draw itself
     * happened; null means "card uploaded but never drawn" (pending), not
     * "drew and lost" (zonk). See LuckyDrawEntry::scopePending()/scopeLost().
     */
    public function up()
    {
        Schema::table('lucky_draw_entries', function (Blueprint $table) {
            $table->timestamp('drawn_at')->nullable()->after('business_card_path');
        });
    }

    public function down()
    {
        Schema::table('lucky_draw_entries', function (Blueprint $table) {
            $table->dropColumn('drawn_at');
        });
    }
}
