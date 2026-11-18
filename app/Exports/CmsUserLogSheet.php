<?php

namespace App\Exports;

use App\Models\CmsUserLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CmsUserLogSheet extends DefaultValueBinder implements FromQuery, WithHeadings, WithMapping,
    WithTitle, WithColumnFormatting, WithColumnWidths, WithStyles, WithCustomValueBinder, WithStrictNullComparison
{
    private $rows;
    private $menus;
    private $summaryAdmins;

    public function __construct(Builder $rows, array $menus, ?Collection $summaryAdmins = null)
    {
        $this->rows = $rows;
        $this->menus = $menus;
        $this->summaryAdmins = $summaryAdmins;
    }

    public function query()
    {
        return $this->rows;
    }

    public function title(): string
    {
        return $this->summaryAdmins !== null ? 'Summary' : 'Activity details';
    }

    public function headings(): array
    {
        if ($this->summaryAdmins !== null) {
            return ['Admin ID', 'CMS admin', 'Menu', 'Server requests', 'Browser interactions', 'Total', 'Last activity (WIB)'];
        }
        return ['Log ID', 'Time (WIB)', 'Admin ID', 'CMS admin', 'Menu', 'Source', 'Activity', 'Control',
            'HTTP method', 'Route', 'Record IDs', 'Submitted fields', 'Outcome', 'HTTP status', 'Page log ID'];
    }

    public function map($row): array
    {
        $menu = $this->menus[$row->menu] ?? $row->menu;
        if ($this->summaryAdmins !== null) {
            return [
                (string) $row->cms_user_id, $this->summaryAdmins[$row->cms_user_id] ?? 'Admin', $menu,
                (int) $row->server_total, $row->total - $row->server_total, (int) $row->total,
                Date::dateTimeToExcel($row->last_activity),
            ];
        }

        $ids = [];
        foreach ($row->record_ids ?? [] as $key => $value) {
            $ids[] = $key . ' #' . $value;
        }
        return [
            (string) $row->id, Date::dateTimeToExcel($row->occurred_at), (string) $row->cms_user_id, $row->admin_name, $menu,
            $row->method ? 'Server' : 'Browser', CmsUserLog::ACTIONS[$row->action] ?? $row->action, $row->control,
            $row->method, $row->route, implode(', ', $ids), implode(', ', $row->fields ?? []),
            $row->outcomeLabel(), $row->http_status === null ? null : (int) $row->http_status,
            $row->page_log_id === null ? null : (string) $row->page_log_id,
        ];
    }

    public function columnFormats(): array
    {
        $timeColumn = $this->summaryAdmins !== null ? 'G' : 'B';
        return [$timeColumn => '[$-409]d mmmm yyyy hh:mm "WIB"'];
    }

    public function columnWidths(): array
    {
        if ($this->summaryAdmins !== null) {
            return ['A' => 14, 'B' => 25, 'C' => 40, 'D' => 20, 'E' => 24, 'F' => 12, 'G' => 32];
        }
        return ['A' => 14, 'B' => 32, 'C' => 14, 'D' => 25, 'E' => 40, 'F' => 14, 'G' => 24, 'H' => 28,
            'I' => 16, 'J' => 45, 'K' => 30, 'L' => 42, 'M' => 23, 'N' => 16, 'O' => 16];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter($sheet->calculateWorksheetDataDimension());
        return [1 => ['font' => ['bold' => true]]];
    }

    // Audit text, including admin names, must remain literal even when it starts with "=".
    public static function dataTypeForValue($value)
    {
        return is_string($value) ? DataType::TYPE_STRING : parent::dataTypeForValue($value);
    }
}
