<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sponsor_renewals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sponsor_id');
            $table->year('renewal_year');
            $table->string('contract_start', 7); // format Y-m
            $table->string('contract_end', 7);   // format Y-m
            $table->string('package')->nullable();
            $table->string('renewal_status')->default('renewed'); // renewed, pending, not_renewed
            $table->boolean('is_current')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('sponsor_id')->references('id')->on('sponsors')->onDelete('cascade');
            $table->index(['renewal_year', 'renewal_status']);
            $table->index(['sponsor_id', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsor_renewals');
    }
};
