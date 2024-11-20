<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id(); // Primary key auto-increment
            $table->unsignedBigInteger('contact_id')->unique(); // ID dari API
            $table->string('display_name');
            $table->string('avatar_url')->nullable();
            $table->text('bio')->nullable();
            $table->string('country_name')->nullable();
            $table->string('flourish_text')->nullable();
            $table->string('job_title')->nullable();
            $table->string('company_display_name')->nullable();
            $table->timestamps(); // Menambahkan kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contacts');
    }
}
