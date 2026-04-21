<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('sponsor_interview_schedules')) {
            return;
        }

        if (Schema::hasColumn('sponsor_interview_schedules', 'event_slug')) {
            if ($this->indexExists('sponsor_interview_schedules', 'uniq_event_slot')) {
                Schema::table('sponsor_interview_schedules', function (Blueprint $table) {
                    $table->dropUnique('uniq_event_slot');
                });
            }

            if ($this->indexExists('sponsor_interview_schedules', 'sponsor_interview_schedules_event_slug_index')) {
                Schema::table('sponsor_interview_schedules', function (Blueprint $table) {
                    $table->dropIndex('sponsor_interview_schedules_event_slug_index');
                });
            }

            Schema::table('sponsor_interview_schedules', function (Blueprint $table) {
                $table->dropColumn('event_slug');
            });
        }

        if (!$this->indexExists('sponsor_interview_schedules', 'uniq_preferred_time_slot')) {
            Schema::table('sponsor_interview_schedules', function (Blueprint $table) {
                $table->unique('preferred_time_slot', 'uniq_preferred_time_slot');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('sponsor_interview_schedules')) {
            return;
        }

        if ($this->indexExists('sponsor_interview_schedules', 'uniq_preferred_time_slot')) {
            Schema::table('sponsor_interview_schedules', function (Blueprint $table) {
                $table->dropUnique('uniq_preferred_time_slot');
            });
        }

        if (!Schema::hasColumn('sponsor_interview_schedules', 'event_slug')) {
            Schema::table('sponsor_interview_schedules', function (Blueprint $table) {
                $table->string('event_slug')->nullable();
            });
        }

        if (!$this->indexExists('sponsor_interview_schedules', 'sponsor_interview_schedules_event_slug_index')) {
            Schema::table('sponsor_interview_schedules', function (Blueprint $table) {
                $table->index('event_slug');
            });
        }

        if (!$this->indexExists('sponsor_interview_schedules', 'uniq_event_slot')) {
            Schema::table('sponsor_interview_schedules', function (Blueprint $table) {
                $table->unique(['event_slug', 'preferred_time_slot'], 'uniq_event_slot');
            });
        }
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        $database = DB::getDatabaseName();

        $row = DB::selectOne(
            'SELECT COUNT(1) AS total FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?',
            [$database, $tableName, $indexName]
        );

        return ((int) ($row->total ?? 0)) > 0;
    }
};
