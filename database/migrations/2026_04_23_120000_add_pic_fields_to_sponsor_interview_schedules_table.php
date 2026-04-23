<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('sponsor_interview_schedules')) {
            return;
        }

        Schema::table('sponsor_interview_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('sponsor_interview_schedules', 'pic_name')) {
                $table->string('pic_name')->nullable()->after('sponsor_package');
            }

            if (!Schema::hasColumn('sponsor_interview_schedules', 'pic_email')) {
                $table->string('pic_email')->nullable()->after('pic_name');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('sponsor_interview_schedules')) {
            return;
        }

        Schema::table('sponsor_interview_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('sponsor_interview_schedules', 'pic_email')) {
                $table->dropColumn('pic_email');
            }

            if (Schema::hasColumn('sponsor_interview_schedules', 'pic_name')) {
                $table->dropColumn('pic_name');
            }
        });
    }
};
