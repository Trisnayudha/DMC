<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Position tier (1-4) dari job title — lihat App\Support\JobTierClassifier.
 * Beda dengan users.tier (reguler/black). job_tier_manual = admin sudah
 * override, jadi tidak dihitung ulang otomatis saat job_title berubah.
 *
 * Isi data lama: php artisan profiles:backfill-job-tier
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->unsignedTinyInteger('job_tier')->nullable()->after('job_title');
            $table->boolean('job_tier_manual')->default(false)->after('job_tier');
            $table->index('job_tier');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropIndex(['job_tier']);
            $table->dropColumn(['job_tier', 'job_tier_manual']);
        });
    }
};
