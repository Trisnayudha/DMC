<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVerificationLogsTable extends Migration
{
    /**
     * One row per verification attempt (not per member) — a member declined
     * and later re-reviewed gets a second row, preserving history instead of
     * overwriting it. started_at is stamped when an admin opens the verify
     * modal (see UsersController::startVerification()); finished_at/result
     * are filled in by verifyMember()/declineMember(). SLA tier and duration
     * are computed from these two timestamps, not stored.
     */
    public function up()
    {
        Schema::create('verification_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->timestamp('started_at');
            $table->unsignedBigInteger('started_by_id')->nullable();
            $table->string('started_by_name')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedBigInteger('finished_by_id')->nullable();
            $table->string('finished_by_name')->nullable();
            $table->string('result')->nullable(); // 'active' | 'declined'
            $table->timestamps();

            $table->index('user_id');
            $table->index('finished_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('verification_logs');
    }
}
