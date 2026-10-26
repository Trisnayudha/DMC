{{-- Filters: search + quick pills (follow-up, temperature) + PIC / date, with removable active-filter chips --}}
@php
    // Current query with some keys replaced (null = removed) — every pill and
    // chip is a plain link, so filters stay bookmarkable / shareable.
    $leadFilterUrl = function (array $set) {
        $query = array_filter(array_merge(request()->query(), $set), function ($v) {
            return $v !== null && $v !== '';
        });
        return url('admin/leads') . ($query ? '?' . http_build_query($query) : '');
    };

    $dateFields = \App\Http\Controllers\Admin\MemberLeadFollowUpController::DATE_FIELDS;
    $picName = optional($pics->firstWhere('id', (int) $picId))->name;

    $activeChips = [];
    if ($search !== '') {
        $activeChips[] = ['label' => 'Search: "' . $search . '"', 'url' => $leadFilterUrl(['search' => null])];
    }
    if ($followUp === 'today' || $followUp === 'need') {
        $activeChips[] = ['label' => $followUp === 'today' ? 'Due today' : 'Need follow-up', 'url' => $leadFilterUrl(['follow_up' => null])];
    }
    if (in_array($temperature, ['hot', 'warm', 'cold'], true)) {
        $activeChips[] = ['label' => 'Temperature: ' . strtoupper($temperature), 'url' => $leadFilterUrl(['temperature' => null])];
    }
    if ($picName) {
        $activeChips[] = ['label' => 'PIC: ' . $picName, 'url' => $leadFilterUrl(['pic_id' => null])];
    }
    if ($dateFrom || $dateTo) {
        $fmt = function ($d) { return $d ? \Illuminate\Support\Carbon::parse($d)->format('d M Y') : '…'; };
        $activeChips[] = [
            'label' => $dateFields[$dateField] . ': ' . $fmt($dateFrom) . ' – ' . $fmt($dateTo),
            'url'   => $leadFilterUrl(['date_field' => null, 'date_from' => null, 'date_to' => null]),
        ];
    }
@endphp

<form action="{{ url('admin/leads') }}" method="GET" id="lead-filter-form" class="lead-filters mb-3">
    <input type="hidden" name="result" value="{{ $result }}">
    @if ($followUp)
        <input type="hidden" name="follow_up" value="{{ $followUp }}">
    @endif
    @if ($temperature)
        <input type="hidden" name="temperature" value="{{ $temperature }}">
    @endif

    {{-- Row 1: search · PIC · date range · apply --}}
    <div class="lf-row">
        <label class="lf-field lf-search">
            <i class="fas fa-search"></i>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search name, email, or company…">
        </label>

        <label class="lf-field">
            <i class="fas fa-user-tie"></i>
            <select name="pic_id" class="lead-auto-submit">
                <option value="">All PIC</option>
                @foreach ($pics as $pic)
                    <option value="{{ $pic->id }}" {{ (string) $picId === (string) $pic->id ? 'selected' : '' }}>{{ $pic->name }}</option>
                @endforeach
            </select>
        </label>

        <div class="lf-field lf-date">
            <i class="far fa-calendar-alt"></i>
            <select name="date_field" title="Date column">
                @foreach ($dateFields as $value => $label)
                    <option value="{{ $value }}" {{ $dateField === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <span class="lf-sep"></span>
            <input type="date" name="date_from" value="{{ $dateFrom }}" title="From">
            <span class="lf-arrow">→</span>
            <input type="date" name="date_to" value="{{ $dateTo }}" title="To">
        </div>

        <button type="submit" class="lf-apply"><i class="fas fa-filter mr-1"></i> Apply</button>
    </div>

    {{-- Row 2: quick filters (one click, click again to turn off) --}}
    <div class="lf-row mt-2">
        <span class="lf-label">Quick</span>
        <a href="{{ $leadFilterUrl(['follow_up' => $followUp === 'today' ? null : 'today']) }}"
            class="lf-pill {{ $followUp === 'today' ? 'active' : '' }}">
            <i class="far fa-calendar-check"></i> Due today <span class="lf-count">{{ $countDueToday }}</span>
        </a>
        <a href="{{ $leadFilterUrl(['follow_up' => $followUp === 'need' ? null : 'need']) }}"
            class="lf-pill lf-pill-danger {{ $followUp === 'need' ? 'active' : '' }}"
            title="Due today + overdue" data-toggle="tooltip">
            <i class="fas fa-exclamation-circle"></i> Need follow-up <span class="lf-count">{{ $countNeedFollowUp }}</span>
        </a>

        <span class="lf-divider"></span>

        <span class="lf-label">Temp</span>
        @foreach (['hot' => 'HOT', 'warm' => 'WARM', 'cold' => 'COLD'] as $value => $label)
            <a href="{{ $leadFilterUrl(['temperature' => $temperature === $value ? null : $value]) }}"
                class="lf-pill lf-temp-{{ $value }} {{ $temperature === $value ? 'active' : '' }}">
                <span class="lf-dot"></span>{{ $label }}
            </a>
        @endforeach

        @if ($activeChips)
            <span class="lf-divider"></span>
            @foreach ($activeChips as $chip)
                <a href="{{ $chip['url'] }}" class="lf-chip" title="Remove filter">
                    {{ $chip['label'] }} <i class="fas fa-times"></i>
                </a>
            @endforeach
            <a href="{{ url('admin/leads?result=' . $result) }}" class="lf-clear">Clear all</a>
        @endif
    </div>
</form>

<style>
    /* Self-contained controls — Stisla forces big heights on .form-control /
       .input-group, so these don't use them; everything here is 36px tall. */
    .lead-filters {
        background: #f8f9fc;
        border: 1px solid #edf0f5;
        border-radius: 10px;
        padding: 12px;
    }
    .lead-filters .lf-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }
    .lead-filters .lf-field {
        display: inline-flex;
        align-items: center;
        height: 36px;
        margin: 0;
        padding: 0 10px;
        background: #fff;
        border: 1px solid #e2e6ee;
        border-radius: 8px;
        transition: border-color .12s, box-shadow .12s;
    }
    .lead-filters .lf-field:focus-within {
        border-color: #6777ef;
        box-shadow: 0 0 0 3px rgba(103, 119, 239, .12);
    }
    .lead-filters .lf-field > i {
        color: #a0a8b8;
        font-size: 12px;
        margin-right: 8px;
    }
    .lead-filters .lf-field input,
    .lead-filters .lf-field select {
        height: 34px;
        border: none;
        outline: none;
        background: transparent;
        font-size: 13px;
        color: #34395e;
        padding: 0;
    }
    .lead-filters .lf-field select {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        padding-right: 16px;
        cursor: pointer;
        background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%23a0a8b8' stroke-width='1.5' fill='none'/%3E%3C/svg%3E") no-repeat right center;
    }
    .lead-filters .lf-search {
        flex: 1 1 260px;
        max-width: 420px;
    }
    .lead-filters .lf-search input {
        width: 100%;
    }
    .lead-filters .lf-date input[type="date"] {
        width: 128px;
    }
    .lead-filters .lf-sep {
        width: 1px;
        height: 18px;
        background: #e2e6ee;
        margin: 0 10px;
    }
    .lead-filters .lf-arrow {
        color: #a0a8b8;
        margin: 0 6px;
        font-size: 12px;
    }
    .lead-filters .lf-apply {
        height: 36px;
        padding: 0 16px;
        border: none;
        border-radius: 8px;
        background: #6777ef;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        box-shadow: 0 2px 6px rgba(103, 119, 239, .35);
        cursor: pointer;
    }
    .lead-filters .lf-apply:hover {
        background: #5566e6;
    }
    .lead-filters .lf-label {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #a0a8b8;
    }
    .lead-filters .lf-divider {
        width: 1px;
        height: 20px;
        background: #e2e6ee;
        margin: 0 4px;
    }
    .lead-filters .lf-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 28px;
        padding: 0 12px;
        border: 1px solid #e2e6ee;
        border-radius: 999px;
        background: #fff;
        color: #5a6474;
        font-size: 12px;
        font-weight: 600;
        line-height: 1;
        white-space: nowrap;
        text-decoration: none;
        transition: all .12s;
    }
    .lead-filters .lf-pill:hover {
        border-color: #6777ef;
        color: #6777ef;
        text-decoration: none;
    }
    .lead-filters .lf-pill.active {
        background: #6777ef;
        border-color: #6777ef;
        color: #fff;
    }
    .lead-filters .lf-count {
        min-width: 20px;
        padding: 2px 6px;
        border-radius: 999px;
        background: #eef0f6;
        color: #5a6474;
        font-size: 10.5px;
        text-align: center;
    }
    .lead-filters .lf-pill.active .lf-count {
        background: rgba(255, 255, 255, .25);
        color: #fff;
    }
    .lead-filters .lf-pill-danger.active {
        background: #fc544b;
        border-color: #fc544b;
    }
    .lead-filters .lf-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: currentColor;
    }
    .lead-filters .lf-temp-hot  { color: #a96b45; }
    .lead-filters .lf-temp-warm { color: #2f6b66; }
    .lead-filters .lf-temp-cold { color: #4f5b5c; }
    .lead-filters .lf-temp-hot.active  { background: #a96b45; border-color: #a96b45; color: #fff; }
    .lead-filters .lf-temp-warm.active { background: #2f6b66; border-color: #2f6b66; color: #fff; }
    .lead-filters .lf-temp-cold.active { background: #4f5b5c; border-color: #4f5b5c; color: #fff; }
    .lead-filters .lf-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 24px;
        padding: 0 10px;
        border-radius: 999px;
        background: #e8eafd;
        color: #4a57c9;
        font-size: 11.5px;
        font-weight: 600;
        text-decoration: none;
    }
    .lead-filters .lf-chip:hover {
        background: #d9ddfb;
        text-decoration: none;
    }
    .lead-filters .lf-chip i {
        font-size: 9px;
    }
    .lead-filters .lf-clear {
        font-size: 12px;
        color: #fc544b;
    }
</style>
