<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvoiceSentDateToSponsorRenewalsTable extends Migration
{
    /**
     * Tanggal invoice dikirim ke sponsor — beda dengan invoice_date (tanggal terbit).
     * Diisi dari modal Renew Contract / Edit Contract, ditampilkan di status renewal.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sponsor_renewals', function (Blueprint $table) {
            $table->date('invoice_sent_date')->nullable()->after('invoice_number');
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::table('sponsor_renewals', function (Blueprint $table) {
            $table->dropColumn('invoice_sent_date');
        });
    }
}
