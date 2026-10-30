<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailedColumnsToSponsorRenewalFormsTable extends Migration
{
    /**
     * Jejak email renewal form ke secretariat: kapan terakhir terkirim, ke siapa,
     * dan berapa kali (generate + update). Ditampilkan di Step 1 modal follow-up.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sponsor_renewal_forms', function (Blueprint $table) {
            $table->timestamp('emailed_at')->nullable()->after('notes');
            $table->string('emailed_to', 500)->nullable()->after('emailed_at');
            $table->unsignedSmallInteger('email_count')->default(0)->after('emailed_to');
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::table('sponsor_renewal_forms', function (Blueprint $table) {
            $table->dropColumn(['emailed_at', 'emailed_to', 'email_count']);
        });
    }
}
