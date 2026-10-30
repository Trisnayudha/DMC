<?php

namespace App\Services\Sponsors;

use App\Models\Sponsors\SponsorFollowup;
use App\Models\Sponsors\SponsorRenewalForm;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Status proses renewal untuk halaman Nearing Contract, per sponsor, untuk siklus
 * setelah kontrak yang sedang berakhir (expiring_end):
 *   Paid         — kontrak baru sudah ada & paid_date terisi
 *   Invoice Sent — kontrak baru sudah dikonfirmasi (Renew Contract)
 *   Final Review — renewal form ada, tapi tidak ada update ≥ 3 hari kerja
 *   Request Form — renewal form sudah di-generate
 *   null         — belum ada proses (view pakai label urgensi Urgent/Upcoming)
 */
class RenewalPipelineStage
{
    private const FINAL_REVIEW_WORKDAYS = 3;

    /**
     * Pasang $sponsor->expiring_end ("Y-m") dan $sponsor->pipeline_stage
     * (['label', 'color', 'detail'] atau null). $sponsors harus eager-load renewals.
     */
    public static function attach(Collection $sponsors, string $from, string $to): void
    {
        if ($sponsors->isEmpty()) {
            return;
        }

        $ids       = $sponsors->pluck('id');
        $forms     = SponsorRenewalForm::whereIn('sponsor_id', $ids)->get()->keyBy(fn ($f) => $f->sponsor_id . '-' . $f->renewal_year);
        $followups = SponsorFollowup::whereIn('sponsor_id', $ids)
            ->selectRaw('sponsor_id, renewal_year, MAX(created_at) AS last_at')
            ->groupBy('sponsor_id', 'renewal_year')
            ->get()
            ->keyBy(fn ($f) => $f->sponsor_id . '-' . $f->renewal_year);

        $sponsors->each(function ($sponsor) use ($from, $to, $forms, $followups) {
            $sponsor->expiring_end   = self::expiringEnd($sponsor, $from, $to);
            $sponsor->pipeline_stage = self::stage($sponsor, $forms, $followups);
        });
    }

    /** Kontrak yang berakhir di jendela: kontrak sponsor sekarang, atau kontrak lama yang sudah di-renew. */
    private static function expiringEnd($sponsor, string $from, string $to): string
    {
        if ($sponsor->contract_end >= $from && $sponsor->contract_end <= $to) {
            return $sponsor->contract_end;
        }

        $old = $sponsor->renewals
            ->filter(fn ($r) => $r->contract_end >= $from && $r->contract_end <= $to)
            ->sortByDesc('contract_end')
            ->first();

        return $old ? $old->contract_end : $sponsor->contract_end;
    }

    private static function stage($sponsor, Collection $forms, Collection $followups): ?array
    {
        $nextStart = Carbon::createFromFormat('Y-m', $sponsor->expiring_end)->startOfMonth()->addMonth();
        $nextYear  = $nextStart->year;

        $successor = $sponsor->renewals
            ->where('renewal_status', 'renewed')
            ->filter(fn ($r) => $r->contract_start >= $nextStart->format('Y-m'))
            ->sortByDesc('contract_start')
            ->first();

        if ($successor && $successor->paid_date) {
            return ['label' => 'Paid', 'color' => '#47c363', 'detail' => 'Paid on ' . $successor->paid_date->format('d M Y')];
        }

        if ($successor) {
            $detail = $successor->invoice_sent_date
                ? 'Sent ' . $successor->invoice_sent_date->format('d M Y')
                : ($successor->invoice_number ? 'Invoice ' . $successor->invoice_number : 'Renewal confirmed');

            return ['label' => 'Invoice Sent', 'color' => '#6777ef', 'detail' => $detail];
        }

        $form = $forms->get($sponsor->id . '-' . $nextYear);
        if (!$form) {
            return null;
        }

        // Aktivitas terakhir: renewal form di-generate/di-update, atau follow-up dicatat.
        $lastActivity = $form->updated_at ?: $form->created_at;
        $followup     = $followups->get($sponsor->id . '-' . $nextYear);
        if ($followup && $followup->last_at && Carbon::parse($followup->last_at)->gt($lastActivity)) {
            $lastActivity = Carbon::parse($followup->last_at);
        }

        $idleWorkdays = $lastActivity ? $lastActivity->diffInWeekdays(now()) : 0;
        if ($idleWorkdays >= self::FINAL_REVIEW_WORKDAYS) {
            return ['label' => 'Final Review', 'color' => '#fd7e14', 'detail' => 'No update for ' . $idleWorkdays . ' working days'];
        }

        return ['label' => 'Request Form', 'color' => '#3abaf4', 'detail' => 'Renewal form ' . $form->form_number];
    }
}
