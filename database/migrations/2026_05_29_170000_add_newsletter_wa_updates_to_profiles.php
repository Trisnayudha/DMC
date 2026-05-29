<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewsletterWaUpdatesToProfiles extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('newsletter')->nullable()->after('job_title');
            $table->string('wa_updates')->nullable()->after('newsletter');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['newsletter', 'wa_updates']);
        });
    }
}
