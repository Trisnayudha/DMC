<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSponsorLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Audit log semua perubahan data sponsor (sponsor + PIC, billing, renewal,
     * benefit, address, advertising, representative, dst). Diisi otomatis oleh
     * trait LogsSponsorActivity di model sponsor, plus log eksplisit untuk aksi
     * yang menulis ke tabel umum (mis. daftarkan representative ke event).
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sponsor_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sponsor_id')->nullable()->index();
            $table->string('subject', 40)->index();       // Sponsor, PIC, Billing, Renewal, ...
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('action', 40)->index();        // created | updated | deleted | aksi khusus
            $table->string('description')->nullable();
            $table->json('changes')->nullable();          // {field: {old, new}}
            $table->unsignedBigInteger('cms_user_id')->nullable()->index();
            $table->string('cms_user_name')->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sponsor_logs');
    }
}
