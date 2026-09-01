<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVatPercentToSponsorRenewalFormsTable extends Migration
{
    /**
     * PPN/VAT itu optional — tim finance yang nentuin company mana yang kena.
     * Default 0 (nggak kena VAT), diisi lewat dropdown di modal Generate Renewal
     * Form (bukan free-text, biar nggak typo persentase-nya). Kalau > 0, renewal
     * form PDF nampilin baris VAT + Grand Total IDR/USD di bawah Total In IDR.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sponsor_renewal_forms', function (Blueprint $table) {
            $table->decimal('vat_percent', 5, 2)->default(0)->after('amount_idr');
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::table('sponsor_renewal_forms', function (Blueprint $table) {
            $table->dropColumn('vat_percent');
        });
    }
}
