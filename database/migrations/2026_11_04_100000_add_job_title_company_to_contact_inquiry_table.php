<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJobTitleCompanyToContactInquiryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Form Contact Us web v2 (Name, Job Title, Company, Business Email, Phone, Message)
     * mengirim job title & company yang diketik sendiri oleh pengirim.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contact_inquiry', function (Blueprint $table) {
            $table->string('job_title')->nullable()->after('last_name');
            $table->string('company')->nullable()->after('job_title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contact_inquiry', function (Blueprint $table) {
            $table->dropColumn(['job_title', 'company']);
        });
    }
}
