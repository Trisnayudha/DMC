<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class NormalizeCompanyCategoryValues extends Migration
{
    public function up()
    {
        $mapping = [
            'Minerals Producer'                       => 'Minerals Producers',
            'Contrator'                               => 'Mining Contractor',
            'Contractor'                              => 'Mining Contractor',
            'Association / Organization / Government' => 'Association/Organization/Government/Academic',
            'Association/Organization/Government'     => 'Association/Organization/Government/Academic',
            'Logistics and Shipping'                  => 'Services/Logistics/Shipping/Facilities Management',
            'Logistic and Shipping'                   => 'Services/Logistics/Shipping/Facilities Management',
            'Investors'                               => 'Investor',
            'Consultant'                              => 'Consultants',
            'Financial Serices'                        => 'Financial Services',
            'Supplier / Distributor / Manufacturer'   => 'Supplier/Distributor/Manufacturer',
        ];

        foreach ($mapping as $old => $new) {
            DB::table('company')
                ->where('company_category', $old)
                ->update(['company_category' => $new]);
        }

        // Company names accidentally stored as category → set to 'other'
        $invalidCategories = DB::table('company')
            ->select('company_category')
            ->whereNotNull('company_category')
            ->where('company_category', '!=', '')
            ->whereNotIn('company_category', [
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
                'other',
                'Others',
            ])
            ->distinct()
            ->pluck('company_category');

        if ($invalidCategories->isNotEmpty()) {
            DB::table('company')
                ->whereIn('company_category', $invalidCategories->toArray())
                ->update([
                    'company_category' => 'other',
                    'company_other'    => DB::raw('company_category'),
                ]);
        }
    }

    public function down()
    {
        // Irreversible — old typos and inconsistencies should not be restored
    }
}
