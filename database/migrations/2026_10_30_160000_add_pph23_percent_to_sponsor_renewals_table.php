<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPph23PercentToSponsorRenewalsTable extends Migration
{
    /**
     * PPh 23 yang dipotong sponsor dari DPP (amount sebelum VAT). Default 0 = tidak
     * dipotong. Dipakai untuk hitungan Net Received di Edit Contract & email payment.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sponsor_renewals', function (Blueprint $table) {
            $table->decimal('pph23_percent', 5, 2)->default(0)->after('vat_percent');
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::table('sponsor_renewals', function (Blueprint $table) {
            $table->dropColumn('pph23_percent');
        });
    }
}
