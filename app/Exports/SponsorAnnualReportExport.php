<?php

namespace App\Exports;

use App\Models\Sponsors\SponsorRenewal;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SponsorAnnualReportExport implements FromArray, WithEvents
{
    protected int $year;
    protected array $builtRows = [];

    public function __construct(int $year)
    {
        $this->year = $year;
    }

    public function array(): array
    {
        if (!empty($this->builtRows)) {
            return $this->builtRows;
        }

        $renewed = SponsorRenewal::with('sponsor')
            ->where('renewal_year', $this->year)
            ->where('renewal_status', 'renewed')
            ->orderBy('contract_start')
            ->get();

        $notRenewed = SponsorRenewal::with('sponsor')
            ->where('renewal_year', $this->year)
            ->where('renewal_status', 'not_renewed')
            ->orderBy('contract_start')
            ->get();

        $rows = [];

        // Header rows
        $rows[] = ['SPONSORS REPORT', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['DJAKARTA MINING CLUB', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = [(string) $this->year, '', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', '', '', '', '', '', ''];

        // Section headers (col A-E = Renewed, col G = separator, H-K = Not Renewed)
        $rows[] = ['Renewed & New Sponsor', '', '', '', '', '', '', 'Not Renewed', '', '', ''];

        // Column headers
        $rows[] = ['No.', 'Periode', 'Company', 'Status', 'Final Confirmation', '', '', 'No.', 'Periode', 'Company', 'Final Confirmation'];

        $maxRows = max($renewed->count(), $notRenewed->count(), 1);

        for ($i = 0; $i < $maxRows; $i++) {
            $row = ['', '', '', '', '', '', '', '', '', '', ''];

            if (isset($renewed[$i])) {
                $r = $renewed[$i];
                $row[0] = $i + 1;
                $row[1] = $this->formatPeriode($r->contract_start, $r->contract_end);
                $row[2] = $r->sponsor->name ?? '-';
                $row[3] = $this->formatStatus($r->renewal_type);
                $row[4] = $this->formatConfirmation($r->package, $r->amount_usd, $r->amount_idr, $r->notes);
            }

            if (isset($notRenewed[$i])) {
                $n = $notRenewed[$i];
                $row[7]  = $i + 1;
                $row[8]  = $this->formatPeriode($n->contract_start, $n->contract_end);
                $row[9]  = $n->sponsor->name ?? '-';
                $row[10] = $n->notes ?? '';
            }

            $rows[] = $row;
        }

        $this->builtRows = $rows;
        return $rows;
    }

    public function registerEvents(): array
    {
        $year    = $this->year;
        $lastRow = count($this->array());

        return [
            AfterSheet::class => function (AfterSheet $event) use ($year, $lastRow) {
                $sheet = $event->sheet->getDelegate();

                // Merge title rows A1:K1, A2:K2, A3:K3
                foreach ([1, 2, 3] as $row) {
                    $sheet->mergeCells("A{$row}:K{$row}");
                }

                // Merge section header row 5
                $sheet->mergeCells('A5:E5');
                $sheet->mergeCells('H5:K5');

                // Style: title row 1
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Style: title rows 2-3
                $sheet->getStyle('A2:A3')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Style: section header row 5 (Renewed & New Sponsor)
                $sheet->getStyle('A5:E5')->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E5799']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // Style: section header row 5 (Not Renewed)
                $sheet->getStyle('H5:K5')->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFC0392B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // Style: column header row 6
                $sheet->getStyle('A6:K6')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9E1F2']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF9DC3E6']],
                    ],
                ]);

                // Light border on data rows
                if ($lastRow > 6) {
                    $sheet->getStyle("A7:E{$lastRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['argb' => 'FFCCCCCC']],
                        ],
                    ]);
                    $sheet->getStyle("H7:K{$lastRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['argb' => 'FFCCCCCC']],
                        ],
                    ]);
                }

                // Auto-size columns A-K
                foreach (range('A', 'K') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Row height row 5 (section headers)
                $sheet->getRowDimension(5)->setRowHeight(20);

                // Wrap text for Final Confirmation and notes columns
                $sheet->getStyle("E7:E{$lastRow}")->getAlignment()->setWrapText(true);
                $sheet->getStyle("K7:K{$lastRow}")->getAlignment()->setWrapText(true);

                // Set fixed width for long text columns after auto-size
                $sheet->getColumnDimension('E')->setAutoSize(false)->setWidth(45);
                $sheet->getColumnDimension('K')->setAutoSize(false)->setWidth(45);

                // Sheet name
                $event->sheet->getDelegate()->setTitle('Sponsors ' . $year);
            },
        ];
    }

    private function formatPeriode(?string $start, ?string $end): string
    {
        if (!$start || !$end) return '-';

        $months = [
            '01' => 'January', '02' => 'February', '03' => 'March',
            '04' => 'April',   '05' => 'May',       '06' => 'June',
            '07' => 'July',    '08' => 'August',    '09' => 'September',
            '10' => 'October', '11' => 'November',  '12' => 'December',
        ];

        [$sy, $sm] = explode('-', $start);
        [$ey, $em] = explode('-', $end);

        return ($months[$sm] ?? $sm) . ' ' . $sy . ' - ' . ($months[$em] ?? $em) . ' ' . $ey;
    }

    private function formatStatus(?string $type): string
    {
        $map = [
            'new'        => 'New Sponsor',
            'new_member' => 'New Member',
            'upgrade'    => 'Renewal - Upgrade',
        ];
        return $map[$type] ?? 'Renewal';
    }

    private function formatConfirmation(?string $package, ?float $amountUsd, ?float $amountIdr, ?string $notes): string
    {
        if ($notes) {
            return $notes;
        }

        $packageMap   = ['platinum' => 'Major', 'gold' => 'Gold', 'silver' => 'Silver'];
        $packageLabel = $packageMap[$package] ?? ucfirst($package ?? '');

        $parts = ["Confirmed - {$packageLabel} Sponsorship"];

        if ($amountUsd) {
            $parts[] = 'USD ' . number_format($amountUsd, 0, '.', '.');
        }
        if ($amountIdr) {
            $parts[] = 'IDR ' . number_format($amountIdr, 0, '.', '.');
        }

        return implode(' / ', $parts);
    }
}
