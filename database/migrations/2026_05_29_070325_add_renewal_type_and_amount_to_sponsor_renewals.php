<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRenewalTypeAndAmountToSponsorRenewals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sponsor_renewals', function (Blueprint $table) {
            // new, renewal, upgrade, new_member
            $table->string('renewal_type')->nullable()->after('renewal_status');
            $table->decimal('amount_usd', 10, 2)->nullable()->after('renewal_type');
            $table->decimal('amount_idr', 15, 2)->nullable()->after('amount_usd');
        });
    }

    public function down()
    {
        Schema::table('sponsor_renewals', function (Blueprint $table) {
            $table->dropColumn(['renewal_type', 'amount_usd', 'amount_idr']);
        });
    }
}
