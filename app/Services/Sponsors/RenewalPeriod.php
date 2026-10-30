<?php

namespace App\Services\Sponsors;

use App\Models\Sponsors\Sponsor;
use App\Models\Sponsors\SponsorRenewal;
use App\Models\Sponsors\SponsorRenewalForm;
use Carbon\Carbon;

/**
 * Kontrak berjalan & periode renewal untuk satu renewal form. renewal_year = tahun mulai
 * kontrak (mis. 2026 = Nov 2026 – Oct 2027). Diambil dari riwayat sponsor_renewals;
 * kalau sponsor belum renew (belum ada kontrak tahun itu), periode renewal dihitung
 * dari akhir kontrak berjalan + 1 bulan, 12 bulan. Dipakai PDF renewal form & email
 * secretariat supaya periodenya sama sebelum maupun sesudah confirm renew.
 */
class RenewalPeriod
{
    /** @return array{current: array{0: ?string, 1: ?string}, renewal: array{0: ?string, 1: ?string}} nilai "Y-m" */
    public static function forForm(Sponsor $sponsor, SponsorRenewalForm $form): array
    {
        $year = (int) $form->renewal_year;
        $byYear = SponsorRenewal::where('sponsor_id', $sponsor->id)
            ->whereIn('renewal_year', [$year - 1, $year])
            ->orderByDesc('id')
            ->get()
            ->unique('renewal_year')
            ->keyBy('renewal_year');

        $renewal  = $byYear->get($year);
        $previous = $byYear->get($year - 1);

        $current = $previous
            ? [$previous->contract_start, $previous->contract_end]
            : ($renewal ? [null, null] : [$sponsor->contract_start, $sponsor->contract_end]);

        $next = $renewal
            ? [$renewal->contract_start, $renewal->contract_end]
            : self::nextAfter($current[1]);

        return ['current' => $current, 'renewal' => $next];
    }

    /** Periode 12 bulan mulai sebulan setelah $contractEnd ("Y-m"). */
    private static function nextAfter(?string $contractEnd): array
    {
        try {
            if ($contractEnd) {
                $start = Carbon::createFromFormat('Y-m', $contractEnd)->startOfMonth()->addMonth();
                return [$start->format('Y-m'), $start->copy()->addMonths(11)->format('Y-m')];
            }
        } catch (\Throwable $e) {
            // format kontrak tidak dikenali: periode renewal kosong
        }
        return [null, null];
    }
}
