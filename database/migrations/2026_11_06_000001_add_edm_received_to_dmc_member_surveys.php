<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('dmc_member_surveys', function (Blueprint $table) {
            $table->string('edm_received')->nullable();
        });
    }

    public function down()
    {
        Schema::table('dmc_member_surveys', function (Blueprint $table) {
            $table->dropColumn('edm_received');
        });
    }
};
