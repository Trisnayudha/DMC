<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Invoice yang dibuat Finance dan dibayar lewat VA Mandiri (Xendit Payments API v3).
 * Terpisah dari tabel payment (registrasi tiket per peserta) — lunas/expired
 * di-update oleh XenditCallbackController lewat reference_id berawalan "FINV-".
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('finance_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 30)->unique();
            $table->string('type', 20); // sponsor_renewal | event_ticket | custom
            $table->string('status', 20)->default('pending')->index(); // pending | paid | expired | cancelled

            $table->string('bill_to_name');
            $table->string('bill_to_company')->nullable();
            $table->string('bill_to_email');
            $table->string('bill_to_cc')->nullable();
            $table->string('bill_to_phone', 30)->nullable();
            $table->text('bill_to_address')->nullable();

            $table->json('items'); // [{description, qty, unit_price, amount}]
            $table->unsignedBigInteger('subtotal');
            $table->decimal('vat_percent', 5, 2)->default(0);
            $table->unsignedBigInteger('vat_amount')->default(0);
            $table->unsignedBigInteger('total_amount');
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('sponsor_id')->nullable()->index();
            $table->unsignedBigInteger('sponsor_renewal_id')->nullable()->index();
            $table->unsignedBigInteger('events_id')->nullable()->index();

            $table->date('issued_date');
            $table->date('due_date');

            // VA Mandiri aktif; reference_id berganti tiap kali VA dibuat ulang (FINV-{id}-{n}).
            $table->string('va_bank', 20)->default('MANDIRI');
            $table->string('va_number', 40)->nullable();
            $table->string('reference_id', 64)->nullable()->unique();
            $table->string('payment_request_id', 64)->nullable();
            $table->unsignedInteger('va_attempts')->default(0);
            $table->timestamp('va_expires_at')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('paid_amount')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_invoices');
    }
};
