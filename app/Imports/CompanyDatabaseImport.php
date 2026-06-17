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

    public function collection(Collection $rows)
    {
        $currentPrefix = null;
        $currentName   = null;

        foreach ($rows as $i => $row) {
            $rowNum = $i + 2;

            $prefix      = trim($row['prefix'] ?? '');
            $companyName = trim($row['company_name'] ?? '');
            $oldName     = trim($row['old_company_name'] ?? '');

            if ($prefix !== '' || $companyName !== '') {
                $currentPrefix = $prefix;
                $currentName   = $companyName;
            }

            if ($oldName === '') {
                continue;
            }

            if ($currentName === null || $currentName === '') {
                $this->errors[] = "Row {$rowNum}: no target company_name defined yet.";
                $this->skipped++;
                continue;
            }

            $affected = CompanyModel::whereRaw('LOWER(TRIM(company_name)) = ?', [strtolower(trim($oldName))])
                ->count();

            if ($affected === 0) {
                $this->errors[] = "Row {$rowNum}: \"{$oldName}\" not found in database.";
                $this->skipped++;
                continue;
            }

            $updateData = ['company_name' => $currentName];
            if ($currentPrefix !== '') {
                $updateData['prefix'] = $currentPrefix;
            }

            CompanyModel::whereRaw('LOWER(TRIM(company_name)) = ?', [strtolower(trim($oldName))])
                ->update($updateData);

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
