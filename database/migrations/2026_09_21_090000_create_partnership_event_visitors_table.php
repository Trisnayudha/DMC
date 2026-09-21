<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Visitor yang mengunjungi booth DMC di event partnership (event bertipe
 * "Partnership Event" / "DMC Partnership Event" di tabel events). Data ini
 * sengaja dipisah dari tabel users/members — visitor booth BUKAN member.
 * events_id App-level FK saja, tanpa constraint DB (sama seperti
 * member_lead_follow_ups & tabel lain di app ini).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partnership_event_visitors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('events_id');
            $table->string('company_name')->nullable();
            $table->string('name')->nullable();
            $table->string('job_title')->nullable();
            $table->string('business_email')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('office_number')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->text('remarks')->nullable();
            $table->string('merchandise')->nullable();
            $table->timestamps();

            $table->index('events_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partnership_event_visitors');
    }
};
