{{-- Job tier (T1–T4) of a registrant: stored tier for active members (may be a manual
     override from the Members page), otherwise derived from the job title. Expects $row. --}}
@php
    $tierNo = \App\Support\JobTierClassifier::resolve($row->job_tier ?? null, $row->job_title);
    $tierManual = !empty($row->job_tier) && !empty($row->job_tier_manual);
    $tierTitle = \App\Support\JobTierClassifier::description($tierNo) . ' · '
        . ($tierManual ? 'set manually on the Members page' : 'automatically assigned from job title');
@endphp
<span class="badge" data-toggle="tooltip" title="{{ $tierTitle }}"
    style="background-color: {{ \App\Support\JobTierClassifier::color($tierNo) }}; color: {{ \App\Support\JobTierClassifier::textColor($tierNo) }};">
    T{{ $tierNo }}@if ($tierManual)*@endif
</span>
