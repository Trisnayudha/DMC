<?php

namespace App\Imports;

use App\Models\PartnershipEvent\PartnershipEventVisitor;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Import visitor booth partnership event dari Excel. Heading row otomatis
 * dinormalisasi Maatwebsite jadi snake_case, jadi kolom "Company Name",
 * "Business Email", dst di spreadsheet asal langsung cocok dengan field
 * di bawah tanpa perlu format ulang file-nya. Kolom "No" diabaikan (cuma
 * nomor urut display, tidak disimpan).
 *
 * Dedup key: business_email, kalau kosong fallback ke mobile_number — di-
 * scope per event (events_id). Row dengan email/nomor HP yang sudah ada di
 * event yang sama akan UPDATE record lama (bukan insert baru) — termasuk
 * kalau ada email/nomor kembar dalam satu file yang sama. Row tanpa email
 * MAUPUN nomor HP selalu jadi record baru (tidak ada key untuk matching).
 *
 * Visitor yang sama bisa muncul beberapa kali dalam satu event (mis. hadir
 * day1 & day3 — remarks-nya beda per hari). Field remarks & merchandise
 * SENGAJA di-merge (append, bukan ditimpa) saat update, supaya histori per
 * hari tidak hilang. Field lain (company_name, name, dst) tetap ditimpa
 * dengan nilai terbaru dari row yang match.
 */
class PartnershipEventVisitorImport implements ToCollection, WithHeadingRow
{
    private int $eventsId;
    private int $created = 0;
    private int $updated = 0;
    private int $skipped = 0;
    private array $errors = [];

    private array $fields = [
        'company_name',
        'name',
        'job_title',
        'business_email',
        'mobile_number',
        'office_number',
        'website',
        'address',
        'remarks',
        'merchandise',
    ];

    public function __construct(int $eventsId)
    {
        $this->eventsId = $eventsId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $i => $row) {
            $rowNum = $i + 2;

            $data = [];
            foreach ($this->fields as $field) {
                $val = trim((string) ($row[$field] ?? ''));
                if ($val !== '') {
                    $data[$field] = $val;
                }
            }

            // Butuh minimal SATU identitas — Company Name/Name/Business Email/
            // Mobile Number — bukan cuma Company Name/Name. Visitor yang cuma
            // kepegang nomor HP-nya (nama/company belum sempat ditulis) tetap
            // harus kesave, bukan cuma yang punya email.
            $hasIdentity = !empty($data['company_name'])
                || !empty($data['name'])
                || !empty($data['business_email'])
                || !empty($data['mobile_number']);

            if (!$hasIdentity) {
                if (!empty($data)) {
                    $this->errors[] = "Row {$rowNum}: dilewati, Company Name/Name/Business Email/Mobile Number semuanya kosong.";
                    $this->skipped++;
                }
                continue;
            }

            if (!empty($data['company_name'])) {
                $data['company_name'] = PartnershipEventVisitor::normalizeCompanyName($data['company_name']);
            }

            $existing = null;
            if (!empty($data['business_email'])) {
                $existing = PartnershipEventVisitor::where('events_id', $this->eventsId)
                    ->whereRaw('LOWER(business_email) = ?', [strtolower($data['business_email'])])
                    ->first();
            } elseif (!empty($data['mobile_number'])) {
                $existing = PartnershipEventVisitor::where('events_id', $this->eventsId)
                    ->where(function ($q) {
                        $q->whereNull('business_email')->orWhere('business_email', '');
                    })
                    ->where('mobile_number', $data['mobile_number'])
                    ->first();
            }

            if ($existing) {
                foreach (['remarks', 'merchandise'] as $mergeField) {
                    if (!empty($data[$mergeField])) {
                        $data[$mergeField] = $this->mergeText($existing->$mergeField, $data[$mergeField]);
                    }
                }

                $existing->update($data);
                $this->updated++;
                continue;
            }

            $data['events_id'] = $this->eventsId;
            PartnershipEventVisitor::create($data);
            $this->created++;
        }
    }

    /**
     * Gabung nilai lama + baru dengan "; ", skip kalau nilai baru sudah
     * ada di dalam teks lama (hindari duplikat waktu file yang sama
     * di-import ulang).
     */
    private function mergeText(?string $existingValue, string $newValue): string
    {
        $existingValue = trim((string) $existingValue);

        if ($existingValue === '') {
            return $newValue;
        }

        if (stripos($existingValue, $newValue) !== false) {
            return $existingValue;
        }

        return $existingValue . '; ' . $newValue;
    }

    public function getCreated(): int
    {
        return $this->created;
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
