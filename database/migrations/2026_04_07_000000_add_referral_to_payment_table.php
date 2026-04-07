<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReferralToPaymentTable extends Migration
{
    public function up()
    {
        Schema::table('payment', function (Blueprint $table) {
            $table->string('referral')->nullable()->after('is_mining');
        });
    }

    public function down()
    {
        Schema::table('payment', function (Blueprint $table) {
            $table->dropColumn('referral');
        });
    }
}
