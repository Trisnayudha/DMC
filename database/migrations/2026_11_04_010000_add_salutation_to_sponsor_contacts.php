<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSalutationToSponsorContacts extends Migration
{
    public function up()
    {
        foreach (['sponsors_pic', 'sponsors_billing'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('salutation', 5)->nullable();
            });
        }
    }

    public function down()
    {
        foreach (['sponsors_pic', 'sponsors_billing'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('salutation');
            });
        }
    }
}
