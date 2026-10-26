<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * member_lead_follow_ups.notes only held ONE note and was overwritten on every
 * step, so the prospect's reply after the sponsor kit / FU1 / FU2 got lost.
 * This table keeps every note as history, tagged with the step it belongs to.
 *
 * Existing single notes are copied over as a general note (step = null) — we
 * can't tell which step they were written for. The old column is left in place
 * (no longer written) so rolling back loses nothing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_lead_follow_up_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->string('step')->nullable(); // sponsorkit | follow_up_1 | follow_up_2 | null = general
            $table->text('note');
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->string('created_by_name')->nullable();
            $table->timestamps();

            $table->index('lead_id');
        });

        $leads = DB::table('member_lead_follow_ups')
            ->whereNotNull('notes')
            ->where('notes', '!=', '')
            ->get(['id', 'notes', 'pic_id', 'pic_name', 'created_by_id', 'created_by_name', 'updated_at']);

        foreach ($leads as $lead) {
            DB::table('member_lead_follow_up_notes')->insert([
                'lead_id'         => $lead->id,
                'step'            => null,
                'note'            => $lead->notes,
                'created_by_id'   => $lead->pic_id ?: $lead->created_by_id,
                'created_by_name' => $lead->pic_name ?: $lead->created_by_name,
                'created_at'      => $lead->updated_at,
                'updated_at'      => $lead->updated_at,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('member_lead_follow_up_notes');
    }
};
