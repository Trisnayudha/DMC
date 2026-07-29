<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeactivationReasonToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('deactivation_reason')->nullable()->after('two_step_verified_by');
            $table->timestamp('deactivated_at')->nullable()->after('deactivation_reason');
            $table->string('deactivated_by')->nullable()->after('deactivated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['deactivation_reason', 'deactivated_at', 'deactivated_by']);
        });
    }
}
