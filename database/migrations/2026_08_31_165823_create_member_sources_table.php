<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberSourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_sources', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('code');
            $table->string('name');
            $table->string('form_type')->default('individual');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['category', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_sources');
    }
}
