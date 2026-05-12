<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExportedAtToXtwpUsersDmc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('xtwp_users_dmc', function (Blueprint $table) {
            $table->timestamp('exported_at')->nullable()->after('source');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('xtwp_users_dmc', function (Blueprint $table) {
            $table->dropColumn('exported_at');
        });
    }
}
