<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Restruktur Category 2 company:
 *
 * 1. "Supplier/Distributor/Manufacturer" dipecah jadi "Supplier",
 *    "Distributor & Channel Partner" dan "Manufacturer". Kategori lama TIDAK
 *    dihapus/di-rename — dinonaktifkan (hilang dari dropdown signup/API), dan
 *    company yang memakainya tetap di kategori lama sampai admin memilih ulang
 *    lewat Company Database (tidak bisa ditebak otomatis).
 *
 * 2. "Technology" di-rename jadi "Technology Provider" (1:1) — termasuk nilai
 *    string di tabel yang menyimpan nama kategori, supaya tidak ada nilai
 *    "Technology" yatim yang nanti tersalin balik ke company.
 */
class RestructureSupplierAndTechnologyCategories extends Migration
{
    const SPLIT_FROM = 'Supplier/Distributor/Manufacturer';
    const SPLIT_TO   = ['Supplier', 'Distributor & Channel Partner', 'Manufacturer'];

    const RENAME_FROM = 'Technology';
    const RENAME_TO   = 'Technology Provider';

    /** Tabel yang menyimpan nama kategori sebagai string di kolom company_category. */
    const CATEGORY_VALUE_TABLES = ['company', 'booking_contact', 'xtwp_users_dmc', 'sponsors'];

    public function up()
    {
        DB::transaction(function () {
            $this->splitSupplierCategory();
            $this->renameCategory(self::RENAME_FROM, self::RENAME_TO);
        });
    }

    public function down()
    {
        $inUse = DB::table('company')->whereIn('company_category', self::SPLIT_TO)->exists();
        if ($inUse) {
            throw new RuntimeException('Kategori hasil pecahan sudah dipakai company — pindahkan dulu sebelum rollback.');
        }

        DB::transaction(function () {
            $this->renameCategory(self::RENAME_TO, self::RENAME_FROM);

            $position = (int) DB::table('company_categories')->whereIn('name', self::SPLIT_TO)->min('sort_order');
            DB::table('company_categories')->whereIn('name', self::SPLIT_TO)->delete();
            DB::table('company_categories')
                ->where('sort_order', '>', $position)
                ->decrement('sort_order', count(self::SPLIT_TO));

            DB::table('company_categories')
                ->where('name', self::SPLIT_FROM)
                ->update(['is_active' => true, 'updated_at' => now()]);
        });
    }

    private function splitSupplierCategory(): void
    {
        if (DB::table('company_categories')->whereIn('name', self::SPLIT_TO)->exists()) {
            return; // sudah pernah jalan
        }

        $old = DB::table('company_categories')->where('name', self::SPLIT_FROM)->first();
        $position = $old ? (int) $old->sort_order : (int) DB::table('company_categories')->max('sort_order');

        // Sisipkan tepat setelah kategori lama.
        DB::table('company_categories')
            ->where('sort_order', '>', $position)
            ->increment('sort_order', count(self::SPLIT_TO));

        foreach (self::SPLIT_TO as $i => $name) {
            DB::table('company_categories')->insert([
                'name'       => $name,
                'sort_order' => $position + $i + 1,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('company_categories')
            ->where('name', self::SPLIT_FROM)
            ->update(['is_active' => false, 'updated_at' => now()]);
    }

    private function renameCategory(string $from, string $to): void
    {
        $exists = DB::table('company_categories')->where('name', $from)->exists();
        $taken  = DB::table('company_categories')->where('name', $to)->exists();
        if (!$exists || $taken) {
            return; // sudah pernah jalan / tidak ada yang perlu di-rename
        }

        DB::table('company_categories')->where('name', $from)->update(['name' => $to, 'updated_at' => now()]);

        foreach (self::CATEGORY_VALUE_TABLES as $table) {
            if (Schema::hasColumn($table, 'company_category')) {
                DB::table($table)->where('company_category', $from)->update(['company_category' => $to]);
            }
        }
    }
}
