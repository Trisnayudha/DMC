<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Renewal form (proposal) yang di-generate SEBELUM follow-up dimulai.
 * Satu form per sponsor per tahun renewal. Menyimpan nomor form, KMK rate,
 * dan nilai proposal (USD/IDR) yang dipakai di renewal form PDF.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sponsor_renewal_forms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sponsor_id');
            $table->year('renewal_year');
            $table->string('form_number', 30)->nullable()->unique();
            $table->unsignedInteger('kmk_rate')->nullable();
            $table->decimal('amount_usd', 10, 2)->nullable();
            $table->decimal('amount_idr', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->date('generated_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('sponsor_id')->references('id')->on('sponsors')->onDelete('cascade');
            $table->unique(['sponsor_id', 'renewal_year']);
            $table->index('renewal_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsor_renewal_forms');
    }
};
