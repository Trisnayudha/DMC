<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanySubcategoryCompanyTable extends Migration
{
    public function up()
    {
        Schema::create('company_subcategory_company', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('company_subcategory_id');
            $table->timestamps();

            // Satu company tidak boleh punya subcategory yang sama dua kali
            $table->unique(['company_id', 'company_subcategory_id'], 'company_subcat_pivot_unique');

            $table->foreign('company_id')
                ->references('id')->on('company')
                ->onDelete('cascade');

            $table->foreign('company_subcategory_id')
                ->references('id')->on('company_subcategories')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('company_subcategory_company');
    }
}
