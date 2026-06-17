<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuotationNumberToSponsorRenewals extends Migration
{
    public function up()
    {
        Schema::table('sponsor_renewals', function (Blueprint $table) {
            $table->string('quotation_number', 30)->nullable()->unique()->after('id');
            $table->date('quotation_date')->nullable()->after('quotation_number');
        });
    }

    public function down()
    {
        Schema::table('sponsor_renewals', function (Blueprint $table) {
            $table->dropColumn(['quotation_number', 'quotation_date']);
        });
    }
}
