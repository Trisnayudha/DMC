<?php

namespace App\Imports;

use App\Models\Company\CompanyModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CompanyDatabaseImport implements ToCollection, WithHeadingRow
{
    private $updated = 0;
    private $skipped = 0;
    private $errors = [];

    private $updatableFields = [
        'prefix',
        'company_name',
        'company_website',
        'company_category',
        'address',
        'city',
        'portal_code',
        'full_office_number',
        'country',
    ];

    public function collection(Collection $rows)
    {
        $currentTarget = [];
        $currentSubcategoryRaw = null; // ikut carry-forward (merge) seperti $currentTarget

        foreach ($rows as $i => $row) {
            $rowNum = $i + 2;

            $oldName = trim($row['old_company_name'] ?? '');

            $rowData = [];
            foreach ($this->updatableFields as $field) {
                $val = trim($row[$field] ?? '');
                if ($val !== '') {
                    $rowData[$field] = $val;
                }
            }

            if (!empty($rowData)) {
                $currentTarget = array_merge($currentTarget, $rowData);
            }

            $subRaw = trim($row['company_subcategory'] ?? '');
            if ($subRaw !== '') {
                $currentSubcategoryRaw = $subRaw;
            }

            if ($oldName === '') {
                continue;
            }

            $hasSubcategory = ($currentSubcategoryRaw !== null && $currentSubcategoryRaw !== '');

            // Subcategory-only pun sah (tag subcategory tanpa ubah field lain).
            if (empty($currentTarget) && !$hasSubcategory) {
                $this->errors[] = "Row {$rowNum}: no target data defined yet.";
                $this->skipped++;
                continue;
            }

            $matches = CompanyModel::whereRaw('LOWER(TRIM(company_name)) = ?', [strtolower($oldName)])
                ->get(['id', 'company_category']);

            if ($matches->isEmpty()) {
                $this->errors[] = "Row {$rowNum}: \"{$oldName}\" not found in database.";
                $this->skipped++;
                continue;
            }

            $payload = $currentTarget;
            $payload['is_verified'] = true;
            $payload['verified_at'] = now();

            CompanyModel::whereRaw('LOWER(TRIM(company_name)) = ?', [strtolower($oldName)])
                ->update($payload);

            $this->updated += $matches->count();

            // Subcategory hanya diproses jika kolomnya diisi (kolom kosong = tidak diubah).
            if ($currentSubcategoryRaw !== null && $currentSubcategoryRaw !== '') {
                $overrideCategory = isset($currentTarget['company_category'])
                    ? trim((string) $currentTarget['company_category'])
                    : '';
                $this->syncSubcategories($matches, $overrideCategory, $currentSubcategoryRaw, $rowNum);
            }
        }
    }

    /**
     * Pasangkan subcategory (multi, dipisah koma) ke company yang cocok.
     * Nama di-resolve terhadap MASTER kategori efektif company
     * (override dari import bila ada, kalau tidak pakai kategori existing).
     * Nama yang tidak ada di master di-SKIP & dilaporkan — tidak auto-create.
     */
    private function syncSubcategories($matches, string $overrideCategory, string $subRaw, int $rowNum): void
    {
        $names = collect(explode(',', $subRaw))
            ->map(fn($n) => trim($n))
            ->filter()
            ->unique()
            ->values();

        if ($names->isEmpty()) {
            return;
        }

        // Kelompokkan company id per kategori efektif — supaya scoping benar
        // walau ada company senama dengan kategori berbeda.
        $idsByCategory = [];
        foreach ($matches as $company) {
            $categoryName = $overrideCategory !== ''
                ? $overrideCategory
                : trim((string) $company->company_category);

            if ($categoryName === '') {
                $this->errors[] = "Row {$rowNum}: company id {$company->id} tanpa category — subcategory dilewati.";
                continue;
            }

            $idsByCategory[$categoryName][] = $company->id;
        }

        foreach ($idsByCategory as $categoryName => $companyIds) {
            $categoryId = DB::table('company_categories')->where('name', $categoryName)->value('id');
            if (!$categoryId) {
                $this->errors[] = "Row {$rowNum}: category \"{$categoryName}\" tidak dikenal — subcategory dilewati.";
                continue;
            }

            $masterByLower = [];
            $master = DB::table('company_subcategories')
                ->where('company_category_id', $categoryId)
                ->get(['id', 'name']);
            foreach ($master as $m) {
                $masterByLower[strtolower($m->name)] = $m->id;
            }

            $resolvedIds = [];
            foreach ($names as $name) {
                $key = strtolower($name);
                if (isset($masterByLower[$key])) {
                    $resolvedIds[] = $masterByLower[$key];
                } else {
                    $this->errors[] = "Row {$rowNum}: subcategory \"{$name}\" tidak ada di master category \"{$categoryName}\" — dilewati.";
                }
            }

            // Kalau tak satu pun nama yang valid, jangan hapus pivot existing
            // (cegah kehilangan data gara-gara salah ketik).
            if (empty($resolvedIds)) {
                continue;
            }

            $this->applyPivot($companyIds, $resolvedIds);
        }
    }

    /**
     * Set pivot untuk sekumpulan company: hapus lama lalu insert pilihan baru.
     */
    private function applyPivot(array $companyIds, array $subcategoryIds): void
    {
        $companyIds = array_values(array_unique(array_filter($companyIds)));
        $subcategoryIds = array_values(array_unique($subcategoryIds));
        if (empty($companyIds)) {
            return;
        }

        $now = now();
        $pivotRows = [];
        foreach ($companyIds as $companyId) {
            foreach ($subcategoryIds as $subcategoryId) {
                $pivotRows[] = [
                    'company_id'             => $companyId,
                    'company_subcategory_id' => $subcategoryId,
                    'created_at'             => $now,
                    'updated_at'             => $now,
                ];
            }
        }

        DB::table('company_subcategory_company')->whereIn('company_id', $companyIds)->delete();
        if (!empty($pivotRows)) {
            DB::table('company_subcategory_company')->insert($pivotRows);
        }
    }

    public function getUpdated(): int
    {
        return $this->updated;
    }

    public function getSkipped(): int
    {
        return $this->skipped;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
