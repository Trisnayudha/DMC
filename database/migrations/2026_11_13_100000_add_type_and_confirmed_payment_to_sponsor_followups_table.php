<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeAndConfirmedPaymentToSponsorFollowupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * type: 'renewal' = follow-up by the Secretariat until the sponsor confirms renewal
     *       (all existing rows), 'invoice' = follow-up by Finance after the invoice is
     *       issued, until the sponsor pays.
     * confirmed_payment: invoice follow-up marked as paid (also fills paid_date on
     *       the renewed contract record).
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sponsor_followups', function (Blueprint $table) {
            $table->string('type', 20)->default('renewal')->after('sponsor_id');
            $table->boolean('confirmed_payment')->default(false)->after('confirmed_renewal');
            $table->index(['sponsor_id', 'renewal_year', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sponsor_followups', function (Blueprint $table) {
            $table->dropIndex(['sponsor_id', 'renewal_year', 'type']);
            $table->dropColumn(['type', 'confirmed_payment']);
        });
    }
}
