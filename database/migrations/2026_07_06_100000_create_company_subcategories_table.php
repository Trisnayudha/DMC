<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanySubcategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('company_subcategories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_category_id');
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Nama subcategory unik di dalam satu kategori (boleh sama antar kategori)
            $table->unique(['company_category_id', 'name'], 'company_subcat_cat_name_unique');

            $table->foreign('company_category_id')
                ->references('id')->on('company_categories')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('company_subcategories');
    }
}
