<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CmsUserLogExport implements WithMultipleSheets
{
    private $sheets;

    public function __construct(Builder $activities, ?Builder $summary, Collection $admins, array $menus)
    {
        $this->sheets = [];
        if ($summary !== null) {
            $this->sheets[] = new CmsUserLogSheet($summary, $menus, $admins);
        }
        $this->sheets[] = new CmsUserLogSheet($activities, $menus);
    }

    public function sheets(): array
    {
        return $this->sheets;
    }
}
