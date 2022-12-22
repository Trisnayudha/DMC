<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentUsersVa extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_users_va', function (Blueprint $table) {
            $table->id();
            $table->integer('payment_id')->nullable();
            $table->string('is_closed')->nullable();
            $table->string('status')->nullable();
            $table->string('currency')->nullable();
            $table->string('country')->nullable();
            $table->string('owner_id')->nullable();
            $table->string('bank_code')->nullable();
            $table->string('merchant_code')->nullable();
            $table->string('account_number')->nullable();
            $table->string('expected_amount')->nullable();
            $table->string('expiration_date')->nullable();
            $table->string('is_single_use')->nullable();
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
        Schema::dropIfExists('payment_users_va');
    }
}
