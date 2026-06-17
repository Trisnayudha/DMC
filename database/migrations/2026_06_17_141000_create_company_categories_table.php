<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateCompanyCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('company_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $categories = [
            'Coal Mining',
            'Minerals Producers',
            'Power Plant',
            'Smelter',
            'Mining Contractor',
            'Coal & Minerals Trading',
            'Supplier/Distributor/Manufacturer',
            'Technology',
            'Services/Logistics/Shipping/Facilities Management',
            'Media',
            'Association/Organization/Government/Academic',
            'Consultants',
            'Investor',
            'Financial Services',
            'Law Firm',
            'Others',
        ];

        foreach ($categories as $i => $name) {
            DB::table('company_categories')->insert([
                'name'       => $name,
                'sort_order' => $i + 1,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('company_categories');
    }
}
