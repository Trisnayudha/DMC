<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvoiceFieldsToSponsorRenewalsTable extends Migration
{
    public function up()
    {
        Schema::table('sponsor_renewals', function (Blueprint $table) {
            $table->date('invoice_date')->nullable()->after('quotation_date');
            $table->string('invoice_number', 30)->nullable()->unique()->after('invoice_date');
            $table->date('paid_date')->nullable()->after('invoice_number');
        });
    }

    public function down()
    {
        Schema::table('sponsor_renewals', function (Blueprint $table) {
            $table->dropColumn(['invoice_date', 'invoice_number', 'paid_date']);
        });
    }
}
