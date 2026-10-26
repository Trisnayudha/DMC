<?php

namespace App\Support;

use App\Models\Company\CompanyModel;

/**
 * Pengelompokan company_category ke 6 grup besar ("Company Category Tier") —
 * satu-satunya sumber mapping untuk chart Company Category di halaman Members,
 * Dashboard Home dan Event Detail. Urutan TIERS = nomor kategori
 * (grup pertama = "Category 1", dst).
 *
 * Kategori di luar daftar — termasuk "Others" & "Not set" — masuk FALLBACK.
 */
class CompanyCategoryTier
{
    const TIERS = [
        'Minerals Producers & Processing, Power Plant, Smelter, Coal Mining, Contractor, Coal & Minerals Trading' => [
            'Minerals Producers', 'Coal Mining', 'Mining Contractor', 'Smelter', 'Power Plant',
            'Coal & Minerals Trading',
        ],
        'Manufacturer, Technology' => [
            'Manufacturer', 'Technology Provider',
            'Technology', // nama lama sebelum di-rename
        ],
        'Supplier, Distributor' => [
            'Supplier', 'Distributor & Channel Partner',
            'Supplier/Distributor/Manufacturer', // kategori lama (nonaktif), sampai di-reclassify
        ],
        'Services, Logistics, Shipping, Facilities Management' => [
            'Services/Logistics/Shipping/Facilities Management',
        ],
        'Consultants, Investor, Financial Services, Law Firm' => [
            'Consultants', 'Investor', 'Financial Services', 'Law Firm',
        ],
        'Association, Organization, Government, Media, Academic' => [
            'Association/Organization/Government/Academic', 'Media',
        ],
    ];

    /** Grup untuk Others, Not set & kategori yang tidak dikenal. */
    const FALLBACK = 'Association, Organization, Government, Media, Academic';

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

        return $lookup[strtolower($categoryLabel)] ?? self::FALLBACK;
    }

    /** "Category N" untuk nilai mentah company.company_category. */
    public static function labelForCategory(?string $rawCategory): string
    {
        return self::label(self::tierFor(CompanyModel::categoryLabel($rawCategory)));
    }

    /** "Category N" untuk nama grup. */
    public static function label(string $tier): string
    {
        return 'Category ' . (array_search($tier, array_keys(self::TIERS), true) + 1);
    }

    /** Daftar "Category N" → nama grup, urut 1..6. */
    public static function labels(): array
    {
        $labels = [];
        foreach (array_keys(self::TIERS) as $tier) {
            $labels[self::label($tier)] = $tier;
        }

        return $labels;
    }

    /** Baris tooltip "<b>Category N:</b> isi grup" (HTML) untuk chart. */
    public static function tooltipLines(): array
    {
        $lines = [];
        foreach (self::labels() as $label => $tier) {
            $lines[] = '<b>' . $label . ':</b> ' . e($tier) . ($tier === self::FALLBACK ? ', Others' : '');
        }

        return $lines;
    }

    public static function exists(string $tier): bool
    {
        return isset(self::TIERS[$tier]);
    }
}
