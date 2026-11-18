{{-- Job tier (T1–T4) of a registrant, editable like on the Members page: stored tier when
     set (active members, or a manual override), otherwise derived from the job title.
     Saved through users.update.job_tier. Expects $row (needs member_id). --}}
@php
    $tierNo = \App\Support\JobTierClassifier::resolve($row->job_tier ?? null, $row->job_title);
    $tierManual = !empty($row->job_tier) && !empty($row->job_tier_manual);
@endphp
<select class="job-tier-select{{ $tierManual ? ' is-manual' : '' }}"
    data-url="{{ route('users.update.job_tier', $row->member_id) }}"
    data-current="{{ $tierNo }}"
    style="background-color: {{ \App\Support\JobTierClassifier::color($tierNo) }}; color: {{ \App\Support\JobTierClassifier::textColor($tierNo) }};"
    title="{{ \App\Support\JobTierClassifier::tooltip($tierNo, $tierManual) }}" data-toggle="tooltip">
    @foreach (\App\Support\JobTierClassifier::TIERS as $tierOption => $tierMeta)
        <option value="{{ $tierOption }}" {{ $tierOption === $tierNo ? 'selected' : '' }}>T{{ $tierOption }}&nbsp;&nbsp;&nbsp;— {{ $tierMeta['roles'] }}</option>
    @endforeach
    <option value="auto">Auto — from job title</option>
</select>
