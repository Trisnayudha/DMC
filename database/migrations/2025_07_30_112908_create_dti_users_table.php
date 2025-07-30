<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDtiUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dti_users', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('username')->unique();
            $table->string('nama')->nullable();
            $table->string('lastname')->nullable();
            $table->string('email')->unique();
            $table->string('telepon')->nullable();
            $table->string('reg_as')->nullable();
            $table->string('job_title')->nullable();
            $table->string('job_level')->nullable();
            $table->string('job_function')->nullable();
            $table->string('company')->nullable();
            $table->string('country')->nullable();
            $table->string('photo')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('category')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dti_users');
    }
}
