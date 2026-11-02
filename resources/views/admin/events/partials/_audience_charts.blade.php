{{-- Company Category Tier & Job Tier of approved registrants — same grouping and
     look as the Members page (App\Services\Events\EventAudienceStats). --}}
@php
    $audiencePct = function ($n) use ($audience) {
        return $audience['total'] ? number_format($n * 100 / $audience['total'], 1, ',', '.') : '0';
    };
@endphp
<style>
    .audience-card .card-header { min-height: 56px; }
    .audience-card .card-header h4 { font-size: 15px; }
    .audience-chart { position: relative; height: 220px; }
    .audience-card { margin-bottom: 0; }
    .audience-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 4px 24px;
        align-items: start;
    }
    .audience-grid .aud-group { min-width: 0; padding-bottom: 6px; }
    .audience-grid .aud-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 12px;
        line-height: 1.5;
        color: #3c4257;
    }
    .audience-grid .aud-item:hover { background: #f4f5f9; }
    .audience-grid .aud-dot { flex-shrink: 0; width: 10px; height: 10px; border-radius: 2px; }
    .audience-grid .aud-name { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .audience-grid .aud-head { border-bottom: 1px solid #eef0f3; border-radius: 6px 6px 0 0; }
    .audience-grid .aud-head .aud-name { font-weight: 700; }
    .audience-grid .aud-count { font-weight: 700; font-variant-numeric: tabular-nums; }
    .audience-grid .aud-pct { width: 44px; text-align: right; color: #8a8fa3; font-variant-numeric: tabular-nums; }
    .audience-grid .aud-sub { padding-left: 26px; font-size: 11.5px; color: #6c757d; }
    .audience-grid .aud-sub .aud-count { font-weight: 600; }
    @media (max-width: 767.98px) {
        .audience-grid { grid-template-columns: minmax(0, 1fr); }
    }
</style>

<div class="row">
    <div class="col-12 col-lg-6 mb-4">
        <div class="card audience-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    Company Category
                    <i class="fas fa-info-circle text-muted ml-1" style="font-size:13px;cursor:help;"
                        data-toggle="tooltip" data-html="true" data-placement="bottom"
                        title="{!! implode('<br>', \App\Support\CompanyCategoryTier::tooltipLines()) !!}"></i>
                </h4>
                <span class="badge badge-light">{{ number_format($audience['total']) }} approved registrants</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="audience-chart">
                            <canvas id="audienceCategoryChart" role="img" aria-label="Registrants per company category"></canvas>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="audience-grid">
                            @foreach ($audience['categories'] as $group)
                                <div class="aud-group">
                                    <div class="aud-item aud-head" title="{{ $group['label'] }}: {{ $group['name'] }}">
                                        <span class="aud-dot" style="background:{{ $group['color'] }};"></span>
                                        <span class="aud-name"><span class="text-muted">{{ $group['label'] }}</span>
                                            {{ $group['name'] }}</span>
                                        <span class="aud-count">{{ number_format($group['count']) }}</span>
                                        <span class="aud-pct">{{ $audiencePct($group['count']) }}%</span>
                                    </div>
                                    @foreach ($group['categories'] as $category => $count)
                                        <div class="aud-item aud-sub" title="{{ $category }}">
                                            <span class="aud-name">{{ $category }}</span>
                                            <span class="aud-count">{{ number_format($count) }}</span>
                                            <span class="aud-pct">{{ $audiencePct($count) }}%</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6 mb-4">
        <div class="card audience-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    Job Tier
                    <i class="fas fa-info-circle text-muted ml-1" style="font-size:13px;cursor:help;" data-toggle="tooltip"
                        title="Position tier from each registrant's job title, same rules as the Members page."></i>
                </h4>
                <span class="badge badge-light">{{ number_format($audience['total']) }} approved registrants</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="audience-chart">
                            <canvas id="audienceTierChart" role="img" aria-label="Registrants per job tier"></canvas>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="audience-grid">
                            @foreach ($audience['tiers'] as $tier)
                                <div class="aud-group">
                                    <div class="aud-item aud-head" title="{{ $tier['roles'] }}">
                                        <span class="aud-dot" style="background:{{ $tier['color'] }};"></span>
                                        <span class="aud-name">{{ $tier['label'] }} <span
                                                class="text-muted font-weight-normal">{{ $tier['action'] }}</span></span>
                                        <span class="aud-count">{{ number_format($tier['count']) }}</span>
                                        <span class="aud-pct">{{ $audiencePct($tier['count']) }}%</span>
                                    </div>
                                    @foreach ($tier['roleCounts'] as $role => $count)
                                        @if ($count)
                                            <div class="aud-item aud-sub" title="{{ $role }}">
                                                <span class="aud-name">{{ $role }}</span>
                                                <span class="aud-count">{{ number_format($count) }}</span>
                                                <span class="aud-pct">{{ $audiencePct($count) }}%</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
