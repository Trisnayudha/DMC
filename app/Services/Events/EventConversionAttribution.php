<?php

namespace App\Services\Events;

use App\Models\Events\Events;
use App\Models\Events\UserRegister;
use App\Models\MemberSource;
use App\Models\PartnershipEvent\PartnershipEventVisitor;
use App\Models\Payments\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Who counts as an event conversion, and which event gets the credit.
 *
 * A person is a conversion when they:
 *  1. actually attended — scanned check-in (DMC owned) or a booth visitor row (Partnership);
 *  2. were not a member before the event — joined membership on or after the event date,
 *     in the same year as the event;
 *  3. are Active now (Pending Review is tracked separately as pipeline, not a conversion);
 *  4. are counted once: when they attended several qualifying events, the last event
 *     before they joined gets the credit.
 *
 * Partnership events only count members from the partnership channel: source EP/<code>
 * or 'Event Partnership' (Register as Member at the booth). Website/apps/social sign-ups
 * after a booth visit are not counted there (they can still count for a DMC owned event).
 * A member who registered through an EP link linked to an event in Member Sources always
 * counts for that event, booth list or not.
 *
 * Registration without check-in does not count, so a DMC owned event with no scans has
 * no conversions (see hasCheckInData()).
 */
class EventConversionAttribution
{
    const CACHE_MINUTES = 5;

    /** @var array<int, array> per year: ['events' => [event_id => ['active' => [...], 'pending' => [...]]]] */
    private $years = [];

    /** User ids credited to this event, by status ('active' | 'pending'). */
    public function forEvent(Events $event): array
    {
        $date = $this->eventDate($event);
        if ($date === null) {
            return ['active' => [], 'pending' => []];
        }

        $year = $this->forYear($date->year);

        return $year['events'][$event->id] ?? ['active' => [], 'pending' => []];
    }

    public function forYear(int $year): array
    {
        if (!isset($this->years[$year])) {
            $this->years[$year] = Cache::remember('dmc_event_conv_attribution_v4_' . $year, now()->addMinutes(self::CACHE_MINUTES), function () use ($year) {
                return $this->build($year);
            });
        }

        return $this->years[$year];
    }

    public function forget(int $year): void
    {
        unset($this->years[$year]);
        Cache::forget('dmc_event_conv_attribution_v4_' . $year);
    }

    /** Event date used for the "joined on or after the event" rule. */
    public function eventDate(Events $event): ?Carbon
    {
        $value = $event->start_date ?: $event->end_date;

        return $value ? Carbon::parse($value)->startOfDay() : null;
    }

    private function build(int $year): array
    {
        $events = Events::where(function ($q) use ($year) {
            $q->whereYear('start_date', $year)->orWhereYear('end_date', $year);
        })->get(['id', 'event_type', 'start_date', 'end_date']);

        $eventDates = [];
        $partnershipEvents = [];
        foreach ($events as $event) {
            $date = $this->eventDate($event);
            if ($date !== null && $date->year === $year) {
                $eventDates[$event->id] = $date;
                if ($event->event_type === 'Partnership Event') {
                    $partnershipEvents[$event->id] = true;
                }
            }
        }
        if (!$eventDates) {
            return ['events' => []];
        }

        $result = [];

        // Registered through an event's own link (EP/<code> linked to the event in Member
        // Sources): that event's conversion — no booth list needed — unless they were already
        // a member before the event (see wasMemberBefore()).
        $linkEvents = $this->registrationLinkEvents();
        if ($linkEvents) {
            $linkUsers = User::whereIn(DB::raw('LOWER(source)'), array_keys($linkEvents))
                ->whereIn('status_member', ['active', 'pending'])
                ->get(['id', 'source', 'status_member', 'verified_at']);
            $memberTicketsBefore = $this->firstMemberTicketAt($linkUsers->pluck('id')->all());

            foreach ($linkUsers as $user) {
                $eventId = $linkEvents[strtolower(trim((string) $user->source))] ?? null;
                if ($eventId === null || !isset($eventDates[$eventId])) {
                    continue;
                }
                if ($this->wasMemberBefore($user, $eventDates[$eventId], $memberTicketsBefore[$user->id] ?? null)) {
                    continue;
                }
                $result[$eventId][$user->status_member === 'active' ? 'active' : 'pending'][] = $user->id;
            }
        }

        $attendedByUser = $this->attendance($events->whereIn('id', array_keys($eventDates)));

        $columns = ['id', 'source', 'status_member', 'created_at'];
        if (User::hasMemberRegisteredAtColumn()) {
            $columns[] = 'member_registered_at';
        }

        User::whereIn('id', array_keys($attendedByUser))
            ->whereIn('status_member', ['active', 'pending'])
            ->get($columns)
            ->each(function ($user) use ($attendedByUser, $eventDates, $partnershipEvents, $linkEvents, &$result) {
                $source = strtolower(trim((string) $user->source));

                // Credited through their registration link (this year or another) — never twice.
                if (isset($linkEvents[$source])) {
                    return;
                }

                // Partnership events only count members who joined through the partnership
                // channel (EP/… link or "Register as Member" at the booth), not website/apps/etc.
                $isPartnershipSource = strpos($source, 'ep/') === 0 || $source === 'event partnership';

                $joinedRaw = $user->member_registered_at ?? $user->created_at;
                if (!$joinedRaw) {
                    return;
                }
                $joined = Carbon::parse($joinedRaw)->startOfDay();

                // Last qualifying event before joining (same year, joined on/after the event date).
                $creditedId = null;
                foreach (array_unique($attendedByUser[$user->id]) as $eventId) {
                    $date = $eventDates[$eventId];
                    if (isset($partnershipEvents[$eventId]) && !$isPartnershipSource) {
                        continue;
                    }
                    if ($date->year !== $joined->year || $joined->lt($date)) {
                        continue;
                    }
                    if ($creditedId === null || $date->gt($eventDates[$creditedId])
                        || ($date->eq($eventDates[$creditedId]) && $eventId > $creditedId)) {
                        $creditedId = $eventId;
                    }
                }

                if ($creditedId !== null) {
                    $status = $user->status_member === 'active' ? 'active' : 'pending';
                    $result[$creditedId][$status][] = $user->id;
                }
            });

        foreach ($result as $eventId => $byStatus) {
            $result[$eventId] = ['active' => $byStatus['active'] ?? [], 'pending' => $byStatus['pending'] ?? []];
        }

        return ['events' => $result];
    }

    /**
     * Already a member before the event: verified before it, or bought a ticket with the
     * member package before it. Catches old accounts whose source was overwritten when they
     * filled in an EP/ form again (they were members, not converted by this event).
     */
    private function wasMemberBefore($user, Carbon $eventDate, ?string $firstMemberTicketAt): bool
    {
        if ($user->verified_at && Carbon::parse($user->verified_at)->lt($eventDate)) {
            return true;
        }

        return $firstMemberTicketAt !== null && Carbon::parse($firstMemberTicketAt)->lt($eventDate);
    }

    /** [user_id => earliest created_at of a 'member' package ticket]. */
    private function firstMemberTicketAt(array $userIds): array
    {
        if (!$userIds) {
            return [];
        }

        return Payment::whereIn('member_id', $userIds)
            ->where('package', 'member')
            ->groupBy('member_id')
            ->selectRaw('member_id, MIN(created_at) as first_at')
            ->pluck('first_at', 'member_id')
            ->all();
    }

    /** ['ep/mi2026' => event_id] — partnership (EP) registration links linked to an event in Member Sources. E/ links do not count: DMC owned events need a check-in. */
    protected function registrationLinkEvents(): array
    {
        if (!MemberSource::hasEventColumn()) {
            return [];
        }

        $map = [];
        foreach (MemberSource::where('category', 'EP')->whereNotNull('events_id')->get(['category', 'code', 'events_id']) as $source) {
            $map[strtolower($source->category . '/' . $source->code)] = (int) $source->events_id;
        }

        return $map;
    }

    /** [user_id => [event_id, ...]] for people who actually attended. */
    private function attendance($events): array
    {
        $attended = [];

        $ownedIds = $events->where('event_type', '!=', 'Partnership Event')->pluck('id')->all();
        if ($ownedIds) {
            UserRegister::whereIn('events_id', $ownedIds)
                ->whereNotNull('present')
                ->whereNotNull('users_id')
                ->get(['users_id', 'events_id'])
                ->each(function ($row) use (&$attended) {
                    $attended[$row->users_id][] = $row->events_id;
                });
        }

        $partnerIds = $events->where('event_type', 'Partnership Event')->pluck('id')->all();
        if ($partnerIds) {
            $visitors = PartnershipEventVisitor::whereIn('events_id', $partnerIds)
                ->whereNotNull('business_email')
                ->get(['business_email', 'events_id']);

            $eventsByEmail = [];
            foreach ($visitors as $visitor) {
                $email = strtolower(trim((string) $visitor->business_email));
                if ($email !== '') {
                    $eventsByEmail[$email][] = $visitor->events_id;
                }
            }

            foreach (array_chunk(array_keys($eventsByEmail), 1000) as $chunk) {
                User::whereIn('email', $chunk)->get(['id', 'email'])->each(function ($user) use ($eventsByEmail, &$attended) {
                    foreach ($eventsByEmail[strtolower(trim((string) $user->email))] ?? [] as $eventId) {
                        $attended[$user->id][] = $eventId;
                    }
                });
            }
        }

        return $attended;
    }
}
