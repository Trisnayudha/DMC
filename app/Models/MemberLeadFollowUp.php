<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberLeadFollowUp extends Model
{
    protected $table = 'member_lead_follow_ups';

    const RESULT_PENDING = 'pending';
    const RESULT_WIN = 'win';
    const RESULT_LOSS = 'loss';

    protected $fillable = [
        'user_id',
        'pic_id',
        'pic_name',
        'deadline_at',
        'sponsorkit_sent_at',
        'channel',
        'notes',
        'result',
        'first_follow_up_at',
        'second_follow_up_at',
        'created_by_id',
        'created_by_name',
        'do_not_send',
        'do_not_send_reason',
        'do_not_send_by_id',
        'do_not_send_by_name',
        'do_not_send_at',
    ];

    protected $casts = [
        'deadline_at' => 'datetime',
        'sponsorkit_sent_at' => 'datetime',
        'first_follow_up_at' => 'datetime',
        'second_follow_up_at' => 'datetime',
        'do_not_send' => 'boolean',
        'do_not_send_at' => 'datetime',
    ];

    /**
     * SLA (dalam jam kerja, Sabtu/Minggu tidak dihitung) untuk menyelesaikan
     * step berikutnya: lead masuk → Send Sponsor Kit 48 jam, Sponsor Kit →
     * Follow-up 1 3 hari, Follow-up 1 → Follow-up 2 5 hari.
     */
    const SLA_HOURS = [
        'sponsorkit'  => 48,
        'follow_up_1' => 3 * 24,
        'follow_up_2' => 5 * 24,
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Riwayat notes/balasan per step, terbaru di atas. */
    public function notesHistory()
    {
        return $this->hasMany(MemberLeadFollowUpNote::class, 'lead_id')->orderBy('created_at', 'desc')->orderBy('id', 'desc');
    }

    /**
     * Pending leads past their deadline. Leads flagged "Do Not Send" are
     * intentionally not being pursued, so they're never over SLA.
     */
    public function scopeOverSla($query)
    {
        return $query->where('member_lead_follow_ups.result', self::RESULT_PENDING)
            ->where('member_lead_follow_ups.do_not_send', false)
            ->whereNotNull('member_lead_follow_ups.deadline_at')
            ->where('member_lead_follow_ups.deadline_at', '<', now());
    }

    public function isOverSla(): bool
    {
        return $this->result === self::RESULT_PENDING
            && !$this->do_not_send
            && $this->deadline_at
            && $this->deadline_at->isPast();
    }

    /** Deadline for $stepKey counted from $start, or null once there's no step left. */
    public static function deadlineFor(?string $stepKey, $start)
    {
        return $stepKey ? self::slaDeadline($start, self::SLA_HOURS[$stepKey]) : null;
    }

    /**
     * Lead Follow-Up hanya untuk member yang sudah approved (status_member =
     * active). Lead lama milik member yang belum/tidak approved (pending,
     * declined, deactivated) disembunyikan — kecuali sudah ada aktivitas
     * (kit terkirim atau sudah win/loss) supaya riwayatnya tidak hilang.
     */
    public function scopeApprovedMember($query)
    {
        return $query->where(function ($q) {
            $q->whereHas('user', function ($u) {
                $u->where('status_member', 'active');
            })
                ->orWhereNotNull('member_lead_follow_ups.sponsorkit_sent_at')
                ->orWhere('member_lead_follow_ups.result', '!=', self::RESULT_PENDING);
        });
    }

    /**
     * Flow: Send Sponsor Kit → Follow-up 1 → Follow-up 2. Returns the key of
     * whichever step hasn't happened yet, or null once all three are done.
     * Drives both the row's action button label and which timestamp
     * logStep() fills in next.
     */
    public function nextStepKey(): ?string
    {
        if (!$this->sponsorkit_sent_at) {
            // Flagged as "do not send" (competitor/unqualified) — never
            // surface the Send Sponsor Kit step, even if it's technically next.
            return $this->do_not_send ? null : 'sponsorkit';
        }
        if (!$this->first_follow_up_at) {
            return 'follow_up_1';
        }
        if (!$this->second_follow_up_at) {
            return 'follow_up_2';
        }
        return null;
    }

    /**
     * SLA deadline that only counts weekday time — Sabtu/Minggu libur, jadi
     * jamnya tidak dihitung. Contoh: 48 jam dari Jumat 10:00 → Selasa 10:00;
     * dari Sabtu 15:00 → Rabu 00:00 (mulai dihitung Senin 00:00).
     */
    public static function slaDeadline($start, int $hours)
    {
        $date = $start->copy();
        $remaining = $hours * 3600;

        while (true) {
            if ($date->isWeekend()) {
                $date = $date->next(\Carbon\Carbon::MONDAY)->startOfDay();
            }

            $endOfDay = $date->copy()->addDay()->startOfDay();
            $available = $endOfDay->getTimestamp() - $date->getTimestamp();

            if ($remaining <= $available) {
                return $date->addSeconds($remaining);
            }

            $remaining -= $available;
            $date = $endOfDay;
        }
    }

    public static function stepLabel(string $key): string
    {
        return [
            'sponsorkit'   => 'Send Sponsor Kit',
            'follow_up_1'  => 'Follow-up 1',
            'follow_up_2'  => 'Follow-up 2',
        ][$key] ?? $key;
    }
}
