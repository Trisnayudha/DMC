<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('event_questions', function (Blueprint $table) {
            $table->string('code_payment')->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table('event_questions', function (Blueprint $table) {
            $table->dropIndex(['code_payment']);
            $table->dropColumn('code_payment');
        });
    }
};
