<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            // Kode voucher unik, misal "DISKON50", "PROMO123", dsb.
            $table->string('voucher_code')->unique();

            // Status voucher: active / inactive (bisa juga 'used')
            $table->enum('status', ['active', 'inactive'])->default('active');

            // Jenis perhitungan diskon: fixed nominal / percentage
            $table->enum('type', ['fixed', 'percentage'])->default('fixed');

            // Nilai nominal / persentase diskon
            // - Jika type=fixed, berarti "nominal" (misal 50000).
            // - Jika type=percentage, berarti "nominal" = 10 (untuk diskon 10%).
            $table->integer('nominal')->default(0);

            // (Opsional) Batas penggunaan voucher
            // Null menandakan tidak ada batasan
            $table->integer('max_uses')->nullable();

            // (Opsional) Berapa kali voucher sudah dipakai
            $table->integer('used_count')->default(0);

            // (Opsional) Periode berlaku voucher
            $table->dateTime('valid_from')->nullable();
            $table->dateTime('valid_until')->nullable();

            // Timestamps created_at dan updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vouchers');
    }
}
