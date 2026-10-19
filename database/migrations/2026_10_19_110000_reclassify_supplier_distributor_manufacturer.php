<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pindahkan company yang masih di kategori lama (nonaktif)
 * "Supplier/Distributor/Manufacturer" ke Supplier / Distributor & Channel
 * Partner / Manufacturer / Technology Provider, sesuai mapping per nama company
 * di database/data/category2_reclassify_2026_10_19.php.
 *
 * - Hanya baris yang MASIH di kategori lama yang diubah — company yang sudah
 *   dipilih ulang admin tidak ditimpa.
 * - Dicocokkan per LOWER(TRIM(company_name)), sama seperti Company Database,
 *   jadi semua baris dengan nama yang sama ikut berubah.
 * - Ikut diterapkan ke booking_contact & xtwp_users_dmc (Data Tampung), karena
 *   keduanya bisa tersalin ke company.
 * - Hanya kolom company_category: updated_at, is_verified, dll. tidak disentuh.
 */
class ReclassifySupplierDistributorManufacturer extends Migration
{
    const OLD_CATEGORY = 'Supplier/Distributor/Manufacturer';
    const MAPPING_FILE = 'data/category2_reclassify_2026_10_19.php';
    const TABLES = ['company', 'booking_contact', 'xtwp_users_dmc'];

    public function up()
    {
        DB::transaction(function () {
            foreach ($this->keysByCategory() as $category => $keys) {
                $this->moveCategory($keys, self::OLD_CATEGORY, $category);
            }
        });
    }

    /**
     * Kembalikan ke kategori lama hanya untuk nama yang ada di mapping dan
     * kategorinya masih sama dengan hasil mapping.
     */
    public function down()
    {
        DB::transaction(function () {
            foreach ($this->keysByCategory() as $category => $keys) {
                $this->moveCategory($keys, $category, self::OLD_CATEGORY);
            }
        });
    }

    /** @return array<string, string[]> kategori baru => daftar key nama company */
    private function keysByCategory(): array
    {
        $grouped = [];
        foreach (require database_path(self::MAPPING_FILE) as $key => $category) {
            $grouped[$category][] = (string) $key;
        }

        return $grouped;
    }

    private function moveCategory(array $keys, string $from, string $to): void
    {
        foreach (self::TABLES as $table) {
            if (!Schema::hasColumn($table, 'company_name') || !Schema::hasColumn($table, 'company_category')) {
                continue;
            }

            foreach (array_chunk($keys, 200) as $chunk) {
                DB::table($table)
                    ->where('company_category', $from)
                    ->whereIn(DB::raw('LOWER(TRIM(company_name))'), $chunk)
                    ->update(['company_category' => $to]);
            }
        }
    }
}
