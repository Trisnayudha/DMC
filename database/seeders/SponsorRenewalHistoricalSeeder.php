<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SponsorRenewalHistoricalSeeder extends Seeder
{
    public function run(): void
    {
        $this->updateExistingRenewals();
        $this->backfill2024Renewals();
        $this->backfill2025Renewals();
        $this->insertNotRenewed();
    }

    /**
     * Tambahkan renewal_type dan amount ke existing records yang belum punya.
     */
    private function updateExistingRenewals(): void
    {
        $updates = [
            // --- 2024 (10 existing records) ---
            // Hexagon Mining: Major/Platinum, renewal, 7500 USD
            ['sponsor_id' => 1,  'contract_start' => '2024-01', 'renewal_type' => 'renewal',    'amount_usd' => 7500.00, 'amount_idr' => null],
            // Berlian Cranserco: Silver, renewal, 2500 USD
            ['sponsor_id' => 27, 'contract_start' => '2024-01', 'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => null],
            // Access World: Major/Platinum, new, 7500 USD
            ['sponsor_id' => 54, 'contract_start' => '2024-03', 'renewal_type' => 'new',        'amount_usd' => 7500.00, 'amount_idr' => null],
            // ARKO Law / ARMILA RAKO: Silver, renewal, IDR 20.053.000
            ['sponsor_id' => 19, 'contract_start' => '2024-04', 'renewal_type' => 'renewal',    'amount_usd' => null,    'amount_idr' => 20053000.00],
            // HOT Chengdu: Silver, new, 2500 USD
            ['sponsor_id' => 58, 'contract_start' => '2024-07', 'renewal_type' => 'new',        'amount_usd' => 2500.00, 'amount_idr' => null],
            // Sayap Garuda Indah: Gold, renewal, 3500 USD / 55.261.500 IDR (contract 2024-09 di DB)
            ['sponsor_id' => 44, 'contract_start' => '2024-09', 'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => 55261500.00],
            // Flextool Beton: Silver, new, 2500 USD / 38.682.500 IDR
            ['sponsor_id' => 62, 'contract_start' => '2024-10', 'renewal_type' => 'new',        'amount_usd' => 2500.00, 'amount_idr' => 38682500.00],
            // Wilo Pumps: Silver, new, 2500 USD / 38.242.500 IDR
            ['sponsor_id' => 63, 'contract_start' => '2024-10', 'renewal_type' => 'new',        'amount_usd' => 2500.00, 'amount_idr' => 38242500.00],
            // TotalEnergies: Gold, new, 3500 USD / 54.509.000 IDR
            ['sponsor_id' => 68, 'contract_start' => '2024-11', 'renewal_type' => 'new',        'amount_usd' => 3500.00, 'amount_idr' => 54509000.00],
            // Bukit Makmur: Major/Platinum, new, 7500 USD / 118.492.500 IDR
            ['sponsor_id' => 67, 'contract_start' => '2024-12', 'renewal_type' => 'new',        'amount_usd' => 7500.00, 'amount_idr' => 118492500.00],

            // --- 2025 (34 existing records - sample key entries) ---
            ['sponsor_id' => 12, 'contract_start' => '2025-01', 'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => 55296500.00],
            ['sponsor_id' => 14, 'contract_start' => '2025-01', 'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => 55576500.00],
            ['sponsor_id' => 48, 'contract_start' => '2025-01', 'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => 39497500.00],
            ['sponsor_id' => 69, 'contract_start' => '2025-01', 'renewal_type' => 'new',        'amount_usd' => 2500.00, 'amount_idr' => 39112500.00],
            ['sponsor_id' => 36, 'contract_start' => '2025-02', 'renewal_type' => 'upgrade',    'amount_usd' => 3500.00, 'amount_idr' => 56721000.00],
            ['sponsor_id' => 56, 'contract_start' => '2025-02', 'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => 56721000.00],
            ['sponsor_id' => 71, 'contract_start' => '2025-02', 'renewal_type' => 'new',        'amount_usd' => 3850.00, 'amount_idr' => 62974450.00],
            ['sponsor_id' => 72, 'contract_start' => '2025-02', 'renewal_type' => 'new',        'amount_usd' => 2750.00, 'amount_idr' => 44959750.00],
            ['sponsor_id' => 74, 'contract_start' => '2025-03', 'renewal_type' => 'new',        'amount_usd' => 3850.00, 'amount_idr' => 62701100.00],
            ['sponsor_id' =>  4, 'contract_start' => '2025-04', 'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => 57039500.00],
            ['sponsor_id' =>  8, 'contract_start' => '2025-04', 'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => 54925500.00],
            ['sponsor_id' => 26, 'contract_start' => '2025-05', 'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => 41462500.00],
            ['sponsor_id' => 75, 'contract_start' => '2025-05', 'renewal_type' => 'new',        'amount_usd' => 3850.00, 'amount_idr' => 64745450.00],
            ['sponsor_id' => 76, 'contract_start' => '2025-05', 'renewal_type' => 'new',        'amount_usd' => 2750.00, 'amount_idr' => 45350250.00],
            ['sponsor_id' => 34, 'contract_start' => '2025-06', 'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => 42132500.00],
            ['sponsor_id' =>  5, 'contract_start' => '2025-07', 'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => 57316000.00],
            ['sponsor_id' => 10, 'contract_start' => '2025-07', 'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => 57316000.00],
            ['sponsor_id' => 11, 'contract_start' => '2025-07', 'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => 57316000.00],
            ['sponsor_id' => 39, 'contract_start' => '2025-07', 'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => 40940000.00],
            ['sponsor_id' => 42, 'contract_start' => '2025-07', 'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => 57316000.00],
            ['sponsor_id' => 59, 'contract_start' => '2025-07', 'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => 40940000.00],
            ['sponsor_id' => 60, 'contract_start' => '2025-07', 'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => 40940000.00],
            ['sponsor_id' => 61, 'contract_start' => '2025-07', 'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => 57316000.00],
            ['sponsor_id' => 77, 'contract_start' => '2025-08', 'renewal_type' => 'new',        'amount_usd' => 2750.00, 'amount_idr' => 44849750.00],
            ['sponsor_id' => 40, 'contract_start' => '2025-09', 'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => 40772500.00],
            ['sponsor_id' => 43, 'contract_start' => '2025-09', 'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => 57081500.00],
            ['sponsor_id' => 24, 'contract_start' => '2025-10', 'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => 38830000.00],
            ['sponsor_id' => 52, 'contract_start' => '2025-10', 'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => 38830000.00],
            ['sponsor_id' => 64, 'contract_start' => '2025-11', 'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => 54509000.00],
            ['sponsor_id' => 65, 'contract_start' => '2025-11', 'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => 41725000.00],
            ['sponsor_id' => 66, 'contract_start' => '2025-11', 'renewal_type' => 'upgrade',    'amount_usd' => 3500.00, 'amount_idr' => 58415000.00],
            ['sponsor_id' => 78, 'contract_start' => '2025-11', 'renewal_type' => 'new',        'amount_usd' => 7500.00, 'amount_idr' => 125175000.00],
            ['sponsor_id' => 79, 'contract_start' => '2025-11', 'renewal_type' => 'new',        'amount_usd' => 7500.00, 'amount_idr' => 124605000.00],

            // --- 2026 (19 existing records) ---
            ['sponsor_id' => 15, 'contract_start' => '2026-01', 'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => 58471000.00],
            ['sponsor_id' => 20, 'contract_start' => '2026-01', 'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => 41765000.00],
            ['sponsor_id' => 46, 'contract_start' => '2026-01', 'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => 58471000.00],
            ['sponsor_id' => 47, 'contract_start' => '2026-01', 'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => 41765000.00],
            ['sponsor_id' => 50, 'contract_start' => '2026-01', 'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => 41762500.00],
            ['sponsor_id' => 80, 'contract_start' => '2026-01', 'renewal_type' => 'upgrade',    'amount_usd' => 3465.00, 'amount_idr' => 58052610.00],
            ['sponsor_id' => 81, 'contract_start' => '2026-01', 'renewal_type' => 'new',        'amount_usd' => 3465.00, 'amount_idr' => 58052610.00],
            ['sponsor_id' => 82, 'contract_start' => '2026-01', 'renewal_type' => 'new',        'amount_usd' => 3465.00, 'amount_idr' => 58052610.00],
            ['sponsor_id' => 22, 'contract_start' => '2026-02', 'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => 41762500.00],
            ['sponsor_id' => 73, 'contract_start' => '2026-02', 'renewal_type' => 'renewal',    'amount_usd' => 3465.00, 'amount_idr' => 58052610.00],
            ['sponsor_id' => 83, 'contract_start' => '2026-02', 'renewal_type' => 'new',        'amount_usd' => 3465.00, 'amount_idr' => 58052610.00],
            ['sponsor_id' => 31, 'contract_start' => '2026-03', 'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => 42310000.00],
            ['sponsor_id' => 84, 'contract_start' => '2026-02', 'renewal_type' => 'new',        'amount_usd' => 7500.00, 'amount_idr' => 125827500.00],
            ['sponsor_id' =>  7, 'contract_start' => '2026-03', 'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => 59234000.00],
            ['sponsor_id' =>  4, 'contract_start' => '2026-04', 'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => 59143000.00],
            ['sponsor_id' =>  8, 'contract_start' => '2026-04', 'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => 59143000.00],
            ['sponsor_id' => 75, 'contract_start' => '2026-05', 'renewal_type' => 'renewal',    'amount_usd' => 3850.00, 'amount_idr' => 65107350.00],
            ['sponsor_id' => 76, 'contract_start' => '2026-05', 'renewal_type' => 'renewal',    'amount_usd' => 2750.00, 'amount_idr' => 46505250.00],
            ['sponsor_id' => 85, 'contract_start' => '2026-03', 'renewal_type' => 'new',        'amount_usd' => 2750.00, 'amount_idr' => 47814250.00],
        ];

        foreach ($updates as $u) {
            DB::table('sponsor_renewals')
                ->where('sponsor_id', $u['sponsor_id'])
                ->where('contract_start', $u['contract_start'])
                ->whereNull('renewal_type')
                ->update([
                    'renewal_type' => $u['renewal_type'],
                    'amount_usd'   => $u['amount_usd'],
                    'amount_idr'   => $u['amount_idr'],
                    'updated_at'   => now(),
                ]);
        }
    }

    /**
     * Backfill 2024 renewals yang belum ada di sponsor_renewals.
     * Sudah di DB: Hexagon, Berlian(01), AccessWorld, ARKO, HOT, Sayap(09), Flextool, Wilo, Total, BukitMakmur.
     */
    private function backfill2024Renewals(): void
    {
        $records = [
            // Jan 2024 - Dec 2024
            ['sponsor_id' => 14, 'contract_start' => '2024-01', 'contract_end' => '2024-12', 'package' => 'gold',    'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => 54680500.00],
            ['sponsor_id' => 12, 'contract_start' => '2024-01', 'contract_end' => '2024-12', 'package' => 'gold',    'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => null],
            ['sponsor_id' => 15, 'contract_start' => '2024-01', 'contract_end' => '2024-12', 'package' => 'silver',  'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => null],
            ['sponsor_id' => 35, 'contract_start' => '2024-01', 'contract_end' => '2024-12', 'package' => 'silver',  'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => null],
            ['sponsor_id' => 46, 'contract_start' => '2024-01', 'contract_end' => '2024-12', 'package' => 'silver',  'renewal_type' => 'new',        'amount_usd' => 2500.00, 'amount_idr' => null],
            ['sponsor_id' => 20, 'contract_start' => '2024-01', 'contract_end' => '2024-12', 'package' => 'silver',  'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => null],
            ['sponsor_id' => 47, 'contract_start' => '2024-01', 'contract_end' => '2024-12', 'package' => 'silver',  'renewal_type' => 'new_member', 'amount_usd' => 2500.00, 'amount_idr' => null],
            ['sponsor_id' => 48, 'contract_start' => '2024-01', 'contract_end' => '2024-12', 'package' => 'silver',  'renewal_type' => 'new',        'amount_usd' => 2500.00, 'amount_idr' => null],
            ['sponsor_id' => 49, 'contract_start' => '2024-01', 'contract_end' => '2024-12', 'package' => 'silver',  'renewal_type' => 'new',        'amount_usd' => 2500.00, 'amount_idr' => null],
            ['sponsor_id' => 50, 'contract_start' => '2024-01', 'contract_end' => '2024-12', 'package' => 'silver',  'renewal_type' => 'new',        'amount_usd' => 2500.00, 'amount_idr' => null],
            ['sponsor_id' => 51, 'contract_start' => '2024-01', 'contract_end' => '2024-12', 'package' => 'silver',  'renewal_type' => 'new',        'amount_usd' => 2500.00, 'amount_idr' => null],
            // Feb 2024 - Jan 2025
            ['sponsor_id' => 53, 'contract_start' => '2024-02', 'contract_end' => '2025-01', 'package' => 'silver',  'renewal_type' => 'new',        'amount_usd' => 2500.00, 'amount_idr' => null],
            ['sponsor_id' => 30, 'contract_start' => '2024-02', 'contract_end' => '2025-01', 'package' => 'silver',  'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => null],
            ['sponsor_id' => 56, 'contract_start' => '2024-02', 'contract_end' => '2025-01', 'package' => 'gold',    'renewal_type' => 'new',        'amount_usd' => 3500.00, 'amount_idr' => null],
            ['sponsor_id' => 36, 'contract_start' => '2024-02', 'contract_end' => '2025-01', 'package' => 'silver',  'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => null],
            ['sponsor_id' => 22, 'contract_start' => '2024-02', 'contract_end' => '2025-01', 'package' => 'silver',  'renewal_type' => 'new',        'amount_usd' => 2500.00, 'amount_idr' => null], // Coates Hire = Diamond Hire Group
            // Mar 2024 - Feb 2025
            ['sponsor_id' =>  7, 'contract_start' => '2024-03', 'contract_end' => '2025-02', 'package' => 'gold',    'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => null],
            ['sponsor_id' => 31, 'contract_start' => '2024-03', 'contract_end' => '2025-02', 'package' => 'silver',  'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => null],
            // Apr 2024 - Mar 2025
            ['sponsor_id' =>  4, 'contract_start' => '2024-04', 'contract_end' => '2025-03', 'package' => 'gold',    'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => null],
            ['sponsor_id' =>  8, 'contract_start' => '2024-04', 'contract_end' => '2025-03', 'package' => 'gold',    'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => 54925500.00],
            // May 2024 - Apr 2025
            ['sponsor_id' => 26, 'contract_start' => '2024-05', 'contract_end' => '2025-04', 'package' => 'silver',  'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => null],
            // Jun 2024 - May 2025
            ['sponsor_id' => 34, 'contract_start' => '2024-06', 'contract_end' => '2025-05', 'package' => 'silver',  'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => 40053000.00],
            // Jul 2024 - Jun 2025
            ['sponsor_id' => 61, 'contract_start' => '2024-07', 'contract_end' => '2025-06', 'package' => 'gold',    'renewal_type' => 'new',        'amount_usd' => 3500.00, 'amount_idr' => 56812000.00],
            ['sponsor_id' =>  5, 'contract_start' => '2024-07', 'contract_end' => '2025-06', 'package' => 'silver',  'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => null],
            ['sponsor_id' => 11, 'contract_start' => '2024-07', 'contract_end' => '2025-06', 'package' => 'gold',    'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => null],
            ['sponsor_id' => 10, 'contract_start' => '2024-07', 'contract_end' => '2025-06', 'package' => 'gold',    'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => 56056000.00],
            ['sponsor_id' => 42, 'contract_start' => '2024-07', 'contract_end' => '2025-06', 'package' => 'gold',    'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => null],
            ['sponsor_id' => 39, 'contract_start' => '2024-07', 'contract_end' => '2025-06', 'package' => 'silver',  'renewal_type' => 'renewal',    'amount_usd' => 2300.00, 'amount_idr' => 36836800.00],
            ['sponsor_id' => 59, 'contract_start' => '2024-07', 'contract_end' => '2025-06', 'package' => 'silver',  'renewal_type' => 'new',        'amount_usd' => 2500.00, 'amount_idr' => 40040000.00],
            ['sponsor_id' => 60, 'contract_start' => '2024-07', 'contract_end' => '2025-06', 'package' => 'silver',  'renewal_type' => 'new',        'amount_usd' => 2500.00, 'amount_idr' => 41030000.00],
            // Sep 2023 - Aug 2024 (renewal_year = 2023, tapi masuk laporan 2024)
            ['sponsor_id' => 40, 'contract_start' => '2023-09', 'contract_end' => '2024-08', 'package' => 'silver',  'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => 40610000.00],
            ['sponsor_id' => 43, 'contract_start' => '2023-09', 'contract_end' => '2024-08', 'package' => 'gold',    'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => 56854000.00],
            ['sponsor_id' => 44, 'contract_start' => '2023-09', 'contract_end' => '2024-08', 'package' => 'gold',    'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => 55261500.00],
            // Oct 2024 - Sep 2025
            ['sponsor_id' => 52, 'contract_start' => '2024-10', 'contract_end' => '2025-09', 'package' => 'silver',  'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => 38830000.00],
            ['sponsor_id' => 24, 'contract_start' => '2024-10', 'contract_end' => '2025-09', 'package' => 'silver',  'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => 38830000.00],
            // Nov 2024 - Oct 2025
            ['sponsor_id' => 64, 'contract_start' => '2024-11', 'contract_end' => '2025-10', 'package' => 'gold',    'renewal_type' => 'new',        'amount_usd' => 3500.00, 'amount_idr' => 54509000.00],
            ['sponsor_id' => 65, 'contract_start' => '2024-11', 'contract_end' => '2025-10', 'package' => 'silver',  'renewal_type' => 'new',        'amount_usd' => 2500.00, 'amount_idr' => 39325500.00],
            ['sponsor_id' => 66, 'contract_start' => '2024-11', 'contract_end' => '2025-10', 'package' => 'silver',  'renewal_type' => 'new',        'amount_usd' => 2500.00, 'amount_idr' => 39397500.00],
        ];

        foreach ($records as $record) {
            $startYear = (int) substr($record['contract_start'], 0, 4);
            $this->insertIfNotExists(array_merge($record, [
                'renewal_year'   => $startYear,
                'renewal_status' => 'renewed',
                'is_current'     => 0,
            ]));
        }
    }

    /**
     * Backfill 2025 renewals yang belum ada (10 record).
     */
    private function backfill2025Renewals(): void
    {
        $records = [
            // Jan 2025 - Dec 2025
            ['sponsor_id' => 15, 'contract_start' => '2025-01', 'contract_end' => '2025-12', 'package' => 'gold',    'renewal_type' => 'upgrade',    'amount_usd' => 3500.00, 'amount_idr' => 56721000.00],
            ['sponsor_id' => 46, 'contract_start' => '2025-01', 'contract_end' => '2025-12', 'package' => 'gold',    'renewal_type' => 'upgrade',    'amount_usd' => 3500.00, 'amount_idr' => 56696500.00],
            ['sponsor_id' => 20, 'contract_start' => '2025-01', 'contract_end' => '2025-12', 'package' => 'silver',  'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => 39697500.00],
            ['sponsor_id' => 27, 'contract_start' => '2025-01', 'contract_end' => '2025-12', 'package' => 'silver',  'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => 39697500.00],
            ['sponsor_id' => 47, 'contract_start' => '2025-01', 'contract_end' => '2025-12', 'package' => 'silver',  'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => 39697500.00],
            ['sponsor_id' => 50, 'contract_start' => '2025-01', 'contract_end' => '2025-12', 'package' => 'silver',  'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => 39697500.00],
            // Feb 2025 - Jan 2026
            ['sponsor_id' => 22, 'contract_start' => '2025-02', 'contract_end' => '2026-01', 'package' => 'silver',  'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => 40515000.00],
            ['sponsor_id' => 73, 'contract_start' => '2025-02', 'contract_end' => '2026-01', 'package' => 'gold',    'renewal_type' => 'new',        'amount_usd' => 3850.00, 'amount_idr' => 62974450.00],
            // Mar 2025 - Feb 2026
            ['sponsor_id' =>  7, 'contract_start' => '2025-03', 'contract_end' => '2026-02', 'package' => 'gold',    'renewal_type' => 'renewal',    'amount_usd' => 3500.00, 'amount_idr' => 57249500.00],
            ['sponsor_id' => 31, 'contract_start' => '2025-03', 'contract_end' => '2026-02', 'package' => 'silver',  'renewal_type' => 'renewal',    'amount_usd' => 2500.00, 'amount_idr' => 40893500.00],
        ];

        foreach ($records as $record) {
            $startYear = (int) substr($record['contract_start'], 0, 4);
            $this->insertIfNotExists(array_merge($record, [
                'renewal_year'   => $startYear,
                'renewal_status' => 'renewed',
                'is_current'     => 0,
            ]));
        }
    }

    /**
     * Insert not_renewed records untuk 2024, 2025, 2026.
     * renewal_year = tahun laporan (tahun mereka seharusnya renew tapi tidak).
     * contract_start/end = periode terakhir yang mereka miliki.
     */
    private function insertNotRenewed(): void
    {
        $notRenewed = [
            // ======================== 2024 NOT RENEWED ========================
            // (kontrak 2023 mereka berakhir, tidak renew untuk 2024)
            // Dassault Systemes & Nusantara Bintang: tidak ada sponsor_id di DB, skip
            ['sponsor_id' =>  2, 'contract_start' => '2023-01', 'contract_end' => '2023-12', 'package' => 'silver',   'renewal_year' => 2024, 'notes' => 'There are some issues, such as the database provided by DMC not fully supporting their marketing campaign due to concerns about data leads'],
            ['sponsor_id' => 28, 'contract_start' => '2023-02', 'contract_end' => '2024-01', 'package' => 'silver',   'renewal_year' => 2024, 'notes' => 'They have allocated some of our budget for another events held both in Indonesia and abroad.'],
            ['sponsor_id' => 29, 'contract_start' => '2023-02', 'contract_end' => '2024-01', 'package' => 'silver',   'renewal_year' => 2024, 'notes' => 'Budget is limit'],
            ['sponsor_id' =>  3, 'contract_start' => '2023-03', 'contract_end' => '2024-02', 'package' => 'silver',   'renewal_year' => 2024, 'notes' => 'No Reason'],
            ['sponsor_id' => 37, 'contract_start' => '2023-03', 'contract_end' => '2024-02', 'package' => 'silver',   'renewal_year' => 2024, 'notes' => 'Due to some new project our funds were focusing for'],
            ['sponsor_id' => 38, 'contract_start' => '2023-04', 'contract_end' => '2024-03', 'package' => 'silver',   'renewal_year' => 2024, 'notes' => 'Due to difficult business of the past year'],
            ['sponsor_id' => 25, 'contract_start' => '2023-05', 'contract_end' => '2024-04', 'package' => 'silver',   'renewal_year' => 2024, 'notes' => 'They market focus has moved away from mining'],
            ['sponsor_id' =>  9, 'contract_start' => '2023-05', 'contract_end' => '2024-10', 'package' => 'gold',     'renewal_year' => 2024, 'notes' => 'For now, they are taking cost-saving measures, so they apologize for not requesting an extension.'],
            ['sponsor_id' => 41, 'contract_start' => '2023-05', 'contract_end' => '2024-10', 'package' => 'silver',   'renewal_year' => 2024, 'notes' => 'No Reason'],
            ['sponsor_id' => 23, 'contract_start' => '2023-10', 'contract_end' => '2024-09', 'package' => 'silver',   'renewal_year' => 2024, 'notes' => 'No Reason'],
            ['sponsor_id' => 45, 'contract_start' => '2023-11', 'contract_end' => '2024-10', 'package' => 'silver',   'renewal_year' => 2024, 'notes' => 'Due to a significant downturn in work we will not be able to continue or support of the Mining Club.'],
            ['sponsor_id' => 55, 'contract_start' => '2023-11', 'contract_end' => '2024-10', 'package' => 'gold',     'renewal_year' => 2024, 'notes' => 'They not going to be sponsor, they remain as membership. They want put money for developing their relationship and getting their product into the market.'],

            // ======================== 2025 NOT RENEWED ========================
            // (kontrak 2024 mereka berakhir, tidak renew untuk 2025)
            ['sponsor_id' => 49, 'contract_start' => '2024-01', 'contract_end' => '2024-12', 'package' => 'silver',   'renewal_year' => 2025, 'notes' => 'The budget we will use will be allocated for other activities.'],
            ['sponsor_id' => 35, 'contract_start' => '2024-01', 'contract_end' => '2024-12', 'package' => 'silver',   'renewal_year' => 2025, 'notes' => 'Have limited budget for DMC for next year and will use will be allocated for other activities.'],
            ['sponsor_id' =>  1, 'contract_start' => '2024-01', 'contract_end' => '2024-12', 'package' => 'platinum', 'renewal_year' => 2025, 'notes' => "They haven't seen the level of value they were hoping from the sponsorship investment"],
            ['sponsor_id' => 30, 'contract_start' => '2024-02', 'contract_end' => '2025-01', 'package' => 'silver',   'renewal_year' => 2025, 'notes' => 'They will focus for priorities to other things'],
            ['sponsor_id' => 53, 'contract_start' => '2024-02', 'contract_end' => '2025-01', 'package' => 'silver',   'renewal_year' => 2025, 'notes' => 'They want to off their membership because they to join another program from their management'],
            ['sponsor_id' => 51, 'contract_start' => '2024-01', 'contract_end' => '2024-12', 'package' => 'silver',   'renewal_year' => 2025, 'notes' => 'There is no specific reason. They simply wish to pause their membership until they decide on a new plan'],
            ['sponsor_id' => 54, 'contract_start' => '2024-03', 'contract_end' => '2025-02', 'package' => 'platinum', 'renewal_year' => 2025, 'notes' => 'They focus on ongoing project at Morowali'],
            ['sponsor_id' => 19, 'contract_start' => '2024-04', 'contract_end' => '2025-03', 'package' => 'silver',   'renewal_year' => 2025, 'notes' => 'They have noted that there has not been significant progress in key business areas such as lead generation and overall impact on their growth year-on-year.'],
            ['sponsor_id' => 58, 'contract_start' => '2024-07', 'contract_end' => '2025-06', 'package' => 'silver',   'renewal_year' => 2025, 'notes' => 'HOT-Mining is currently heavily engaged in the implementation of various ongoing projects'],
            ['sponsor_id' => 44, 'contract_start' => '2024-09', 'contract_end' => '2025-08', 'package' => 'gold',     'renewal_year' => 2025, 'notes' => 'The company is currently focusing its resources and investments on the development of a new business unit.'],
            ['sponsor_id' => 62, 'contract_start' => '2024-10', 'contract_end' => '2025-09', 'package' => 'silver',   'renewal_year' => 2025, 'notes' => "They don't felt any significant benefits"],
            ['sponsor_id' => 63, 'contract_start' => '2024-10', 'contract_end' => '2025-09', 'package' => 'silver',   'renewal_year' => 2025, 'notes' => "They're not renewing because it aligns with their current strategic priorities."],
            ['sponsor_id' => 68, 'contract_start' => '2024-11', 'contract_end' => '2025-10', 'package' => 'gold',     'renewal_year' => 2025, 'notes' => 'There is no budget allocated for membership next year, but they will try to join future events managed by DMC'],
            ['sponsor_id' => 67, 'contract_start' => '2024-12', 'contract_end' => '2025-11', 'package' => 'platinum', 'renewal_year' => 2025, 'notes' => 'They need to focus on strategic management and budget efficiency, and review which sponsors are effective and which are not. From a project perspective, it has not been effective yet.'],

            // ======================== 2026 NOT RENEWED ========================
            // (kontrak 2025 mereka berakhir, tidak renew untuk 2026)
            ['sponsor_id' => 48, 'contract_start' => '2025-01', 'contract_end' => '2025-12', 'package' => 'silver',   'renewal_year' => 2026, 'notes' => "There's no specific reason yet"],
            ['sponsor_id' => 14, 'contract_start' => '2025-01', 'contract_end' => '2025-12', 'package' => 'gold',     'renewal_year' => 2026, 'notes' => "There's no concern but it might be more or less effective. Because there aren't many players there when networking"],
            ['sponsor_id' => 69, 'contract_start' => '2025-01', 'contract_end' => '2025-12', 'package' => 'silver',   'renewal_year' => 2026, 'notes' => "Consider to rejoin if there's any opportunity for speaking and collaborate"],
            ['sponsor_id' => 12, 'contract_start' => '2025-01', 'contract_end' => '2025-12', 'package' => 'gold',     'renewal_year' => 2026, 'notes' => 'Due to changes in their internal structure this year, they will not be proceeding for the time being'],
            ['sponsor_id' => 56, 'contract_start' => '2025-02', 'contract_end' => '2026-01', 'package' => 'gold',     'renewal_year' => 2026, 'notes' => "There's no specific reason yet"],
            ['sponsor_id' => 36, 'contract_start' => '2025-02', 'contract_end' => '2026-01', 'package' => 'gold',     'renewal_year' => 2026, 'notes' => 'Unable to advise on when they can commit to this year due to other immediate priorities.'],
            ['sponsor_id' => 71, 'contract_start' => '2025-02', 'contract_end' => '2026-01', 'package' => 'gold',     'renewal_year' => 2026, 'notes' => "In this hard situation they said there's project on hold and they hold their budget as well"],
            ['sponsor_id' => 72, 'contract_start' => '2025-02', 'contract_end' => '2026-01', 'package' => 'silver',   'renewal_year' => 2026, 'notes' => "There's no specific reason yet"],
            ['sponsor_id' => 74, 'contract_start' => '2025-03', 'contract_end' => '2026-02', 'package' => 'gold',     'renewal_year' => 2026, 'notes' => "There's no specific reason yet"],
        ];

        foreach ($notRenewed as $record) {
            $this->insertIfNotExists(array_merge($record, [
                'renewal_status' => 'not_renewed',
                'renewal_type'   => null,
                'amount_usd'     => null,
                'amount_idr'     => null,
                'is_current'     => 0,
            ]));
        }
    }

    private function insertIfNotExists(array $data): void
    {
        $exists = DB::table('sponsor_renewals')
            ->where('sponsor_id', $data['sponsor_id'])
            ->where('contract_start', $data['contract_start'])
            ->where('contract_end', $data['contract_end'])
            ->where('renewal_status', $data['renewal_status'])
            ->exists();

        if (!$exists) {
            DB::table('sponsor_renewals')->insert(array_merge($data, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
