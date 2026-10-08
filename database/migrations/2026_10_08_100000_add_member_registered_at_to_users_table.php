<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Orang yang sudah terdaftar di event (akun sementara) lalu mendaftar jadi
 * member memakai ulang baris users yang sama, sehingga users.created_at tetap
 * tanggal daftar event. Kolom ini menyimpan kapan dia mendaftar jadi member;
 * NULL = pakai created_at (semua data lama, tidak berubah).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('member_registered_at')->nullable()->after('verified_at');
            $table->index('member_registered_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['member_registered_at']);
            $table->dropColumn('member_registered_at');
        });
    }
};
