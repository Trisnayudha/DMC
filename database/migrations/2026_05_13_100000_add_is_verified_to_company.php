<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsVerifiedToCompany extends Migration
{
    public function up()
    {
        Schema::table('company', function (Blueprint $table) {
            $table->boolean('is_verified')->default(false)->after('company_name');
        });
    }

    public function down()
    {
        Schema::table('company', function (Blueprint $table) {
            $table->dropColumn('is_verified');
        });
    }
}
