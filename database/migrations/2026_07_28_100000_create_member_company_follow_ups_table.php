<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_company_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('previous_company_name')->nullable();
            $table->string('new_company_name');
            $table->text('notes')->nullable();
            $table->string('status')->default('needs_follow_up');
            $table->unsignedBigInteger('flagged_by_id')->nullable();
            $table->string('flagged_by_name')->nullable();
            $table->unsignedBigInteger('verified_by_id')->nullable();
            $table->string('verified_by_name')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_company_follow_ups');
    }
};
