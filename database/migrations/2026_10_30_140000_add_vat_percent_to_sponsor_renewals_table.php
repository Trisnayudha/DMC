<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVatPercentToSponsorRenewalsTable extends Migration
{
    /**
     * VAT/PPN kontrak — diisi dari modal Renew Contract (prefill dari renewal form).
     * Default 0 = tidak kena VAT, sama seperti sponsor_renewal_forms.vat_percent.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sponsor_renewals', function (Blueprint $table) {
            $table->decimal('vat_percent', 5, 2)->default(0)->after('amount_idr');
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::table('sponsor_renewals', function (Blueprint $table) {
            $table->dropColumn('vat_percent');
        });
    }
}
