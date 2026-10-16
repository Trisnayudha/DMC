<?php

namespace App\Support;

use App\Models\Company\CompanyModel;

/**
 * Pengelompokan company_category ke 5 grup besar ("Company Category Tier"),
 * mengikuti chart "Distribusi Perusahaan per Category" di event detail
 * IndonesiaMiner (indonesiaminer-backend-admin, ChartDataController::CATEGORY_BUCKETS).
 * Kategori di luar daftar — termasuk "Others" & "Not set" — masuk grup
 * terakhir (catch-all), sama seperti referensinya.
 */
class CompanyCategoryTier
{
    const TIERS = [
        'Minerals Producers & Processing, Power Plant, Smelter, Coal Mining, Contractor' => [
            'Minerals Producers', 'Coal Mining', 'Mining Contractor', 'Smelter', 'Power Plant',
        ],
        'Supplier, Distributor, Manufacturer, Technology' => [
            'Supplier/Distributor/Manufacturer', 'Technology',
        ],
        'Services, Logistics, Shipping, Facilities Management' => [
            'Services/Logistics/Shipping/Facilities Management',
        ],
        'Association, Organization, Government, Media, Academic' => [
            'Association/Organization/Government/Academic', 'Media',
        ],
        'Consultants, Investor, Financial Services, Trading, Law Firm' => [
            'Consultants', 'Investor', 'Financial Services', 'Coal & Minerals Trading', 'Law Firm',
        ],
    ];

    /** Nama grup untuk satu label kategori (hasil CompanyModel::categoryLabel). */
    public static function tierFor(string $categoryLabel): string
    {
        static $lookup = null;

        if ($lookup === null) {
            $lookup = [];
            foreach (self::TIERS as $tier => $categories) {
                foreach ($categories as $category) {
                    $lookup[strtolower(CompanyModel::categoryLabel($category))] = $tier;
                }
            }
        }

        return $lookup[strtolower($categoryLabel)] ?? array_key_last(self::TIERS);
    }

    public static function exists(string $tier): bool
    {
        return isset(self::TIERS[$tier]);
    }
}
