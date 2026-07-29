<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTwoStepVerifiedToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('two_step_verified')->default(false)->after('verified_at');
            $table->timestamp('two_step_verified_at')->nullable()->after('two_step_verified');
            $table->string('two_step_verified_by')->nullable()->after('two_step_verified_at');
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
            $table->dropColumn(['two_step_verified', 'two_step_verified_at', 'two_step_verified_by']);
        });
    }
}
