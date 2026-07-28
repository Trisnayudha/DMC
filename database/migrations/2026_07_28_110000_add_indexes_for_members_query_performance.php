<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesForMembersQueryPerformance extends Migration
{
    /**
     * admin/users was doing full table scans on every join/filter here —
     * confirmed via EXPLAIN (type: ALL on users + profiles, no keys used).
     * These columns are indexed nowhere despite being the exact join/where
     * targets for the Members list, its ~15 stat counters, and export.
     */
    public function up()
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->index('users_id');
            $table->index('company_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('status_member');
            $table->index('isStatus');
        });

        Schema::table('company', function (Blueprint $table) {
            $table->index('explore');
        });
    }

    public function down()
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropIndex(['users_id']);
            $table->dropIndex(['company_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['status_member']);
            $table->dropIndex(['isStatus']);
        });

        Schema::table('company', function (Blueprint $table) {
            $table->dropIndex(['explore']);
        });
    }
}
