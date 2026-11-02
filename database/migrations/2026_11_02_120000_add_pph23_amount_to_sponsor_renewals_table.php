<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPph23AmountToSponsorRenewalsTable extends Migration
{
    /**
     * Nominal PPh 23 (IDR) yang benar-benar dipotong sponsor — diisi/dikoreksi
     * finance langsung, bukan hasil persen bulat, karena sering ada selisih
     * pembulatan. pph23_percent tetap ada, diturunkan dari nominal ini.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sponsor_renewals', function (Blueprint $table) {
            $table->decimal('pph23_amount', 15, 2)->nullable()->after('pph23_percent');
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::table('sponsor_renewals', function (Blueprint $table) {
            $table->dropColumn('pph23_amount');
        });
    }
}
