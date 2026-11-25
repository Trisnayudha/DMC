<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cms_users', function (Blueprint $table) {
            $table->boolean('otp_enabled')->default(false);
            $table->string('login_otp_hash')->nullable();
            $table->timestamp('login_otp_sent_at')->nullable();
            $table->unsignedTinyInteger('login_otp_attempts')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('cms_users', function (Blueprint $table) {
            $table->dropColumn(['otp_enabled', 'login_otp_hash', 'login_otp_sent_at', 'login_otp_attempts']);
        });
    }
};
