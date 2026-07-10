<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsMembershipProspectToPaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment', function (Blueprint $table) {
            $table->boolean('is_membership_prospect')
                ->default(false)
                ->after('is_mining'); // penanda: boleh ditawarkan membership saat check-in
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment', function (Blueprint $table) {
            if (Schema::hasColumn('payment', 'is_membership_prospect')) {
                $table->dropColumn('is_membership_prospect');
            }
        });
    }
}
