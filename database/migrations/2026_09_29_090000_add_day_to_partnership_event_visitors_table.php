<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sebelumnya hari kunjungan (day 1-4) cuma diketik manual di field Remarks
 * (mis. "Mi26 - day1 - visitor") — tidak konsisten & tidak bisa difilter.
 * Kolom ini jadi field terpisah, diisi lewat dropdown di form input visitor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partnership_event_visitors', function (Blueprint $table) {
            $table->unsignedTinyInteger('day')->nullable()->after('merchandise');
        });
    }

    public function down(): void
    {
        Schema::table('partnership_event_visitors', function (Blueprint $table) {
            $table->dropColumn('day');
        });
    }
};
