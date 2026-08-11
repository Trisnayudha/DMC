<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberLeadFollowUpsTable extends Migration
{
    /**
     * Auto-created by UsersController::verifyMember() when a verified member's
     * company has explore ("Explore Marketing") set — mirrors the shape of
     * sponsor_followups / member_company_follow_ups, both already proven in
     * this app. pic_id references cms_users (no DB-level FK, matching the
     * app-level-FK convention already used throughout this codebase).
     */
    public function up()
    {
        Schema::create('member_lead_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('pic_id')->nullable();
            $table->string('pic_name')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->string('channel')->nullable(); // call | whatsapp | email | other
            $table->text('notes')->nullable();
            $table->string('result')->default('pending'); // pending | win | loss
            $table->timestamp('first_follow_up_at')->nullable();
            $table->timestamp('second_follow_up_at')->nullable();
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->string('created_by_name')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('result');
        });
    }

    public function down()
    {
        Schema::dropIfExists('member_lead_follow_ups');
    }
}
