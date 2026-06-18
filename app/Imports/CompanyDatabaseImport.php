<?php

namespace App\Imports;

use App\Models\Company\CompanyModel;
use Illuminate\Support\Collection;
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

            if ($oldName === '') {
                continue;
            }

            if (empty($currentTarget)) {
                $this->errors[] = "Row {$rowNum}: no target data defined yet.";
                $this->skipped++;
                continue;
            }

            $affected = CompanyModel::whereRaw('LOWER(TRIM(company_name)) = ?', [strtolower($oldName)])
                ->count();

            if ($affected === 0) {
                $this->errors[] = "Row {$rowNum}: \"{$oldName}\" not found in database.";
                $this->skipped++;
                continue;
            }

            $currentTarget['is_verified'] = true;
            $currentTarget['verified_at'] = now();

            CompanyModel::whereRaw('LOWER(TRIM(company_name)) = ?', [strtolower($oldName)])
                ->update($currentTarget);

            $this->updated += $affected;
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
