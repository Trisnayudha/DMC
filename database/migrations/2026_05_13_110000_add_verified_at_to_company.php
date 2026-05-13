<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVerifiedAtToCompany extends Migration
{
    public function up()
    {
        Schema::table('company', function (Blueprint $table) {
            $table->timestamp('verified_at')->nullable()->after('is_verified');
        });
    }

    public function down()
    {
        Schema::table('company', function (Blueprint $table) {
            $table->dropColumn('verified_at');
        });
    }
}
