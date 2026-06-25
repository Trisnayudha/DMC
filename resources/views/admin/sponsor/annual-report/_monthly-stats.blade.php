{{-- ═══ MONTHLY STATISTICS — 3 view modes ═══
     1. Sponsor (YoY): bar chart this year vs comparison year
     2. Tipe Sponsor: bar chart per package (Platinum/Gold/Silver) with YoY
     3. Price: bar chart IDR value + line chart target --}}
@php
    $msMonthShort = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    $cd = $chartData;
@endphp

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
        <div>
            <h4 class="mb-0"><i class="fas fa-chart-bar mr-2 text-primary"></i>Statistics by Month — {{ $year }}</h4>
            <small class="text-muted">January – December</small>
        </div>
        <div class="d-flex align-items-center flex-wrap" style="gap:8px;">
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-primary active" id="chartModeSponsors" onclick="switchChartMode('sponsors')">Sponsor</button>
                <button type="button" class="btn btn-outline-primary" id="chartModePackage" onclick="switchChartMode('package')">Tipe Sponsor</button>
                <button type="button" class="btn btn-outline-primary" id="chartModePrice" onclick="switchChartMode('price')">Price (IDR)</button>
            </div>
            <span class="text-muted" style="font-size:11px;">vs</span>
            <select id="chartCompareYear" class="form-control form-control-sm" style="width:90px;">
                @foreach($cd['years'] as $y)
                    @if($y != $year)
                        <option value="{{ $y }}" {{ $y == ($year - 1) ? 'selected' : '' }}>{{ $y }}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>
    <div class="card-body pb-2">
        <canvas id="monthlyChart" height="90"></canvas>
    </div>
</div>

@push('bottom')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var year = {{ $year }};
    var months = {!! json_encode($msMonthShort) !!};
    var allYears = {!! json_encode($cd['years']) !!};
    var sponsorYoy = {!! json_encode($cd['sponsorYoy']) !!};
    var packageYoy = {!! json_encode($cd['packageYoy']) !!};
    var priceYoy = {!! json_encode($cd['priceYoy']) !!};
    var target = {{ $cd['target'] }};

    var ctx = document.getElementById('monthlyChart').getContext('2d');
    var chart = null;
    var currentMode = 'sponsors';

    function getCompareYear() {
        var el = document.getElementById('chartCompareYear');
        return el ? parseInt(el.value) : (year - 1);
    }

    function buildSponsorsChart() {
        var cmpYear = getCompareYear();
        return {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    {
                        label: year + ' (Sponsor)',
                        backgroundColor: '#6777ef',
                        data: sponsorYoy[year] || new Array(12).fill(0),
                        barPercentage: 0.6
                    },
                    {
                        label: cmpYear + ' (Sponsor)',
                        backgroundColor: '#cbd5e0',
                        data: sponsorYoy[cmpYear] || new Array(12).fill(0),
                        barPercentage: 0.6
                    }
                ]
            },
            options: chartOptions(false)
        };
    }

    function buildPackageChart() {
        var cmpYear = getCompareYear();
        var pkgColors = {
            platinum: { now: '#6777ef', cmp: 'rgba(103,119,239,0.35)' },
            gold:     { now: '#f39c12', cmp: 'rgba(243,156,18,0.35)' },
            silver:   { now: '#6c757d', cmp: 'rgba(108,117,125,0.35)' }
        };
        var datasets = [];
        ['platinum', 'gold', 'silver'].forEach(function(pkg) {
            var pkgLabel = pkg.charAt(0).toUpperCase() + pkg.slice(1);
            datasets.push({
                label: pkgLabel + ' ' + year,
                backgroundColor: pkgColors[pkg].now,
                data: (packageYoy[year] && packageYoy[year][pkg]) ? packageYoy[year][pkg] : new Array(12).fill(0),
                barPercentage: 0.5,
                stack: 'y' + year
            });
            datasets.push({
                label: pkgLabel + ' ' + cmpYear,
                backgroundColor: pkgColors[pkg].cmp,
                data: (packageYoy[cmpYear] && packageYoy[cmpYear][pkg]) ? packageYoy[cmpYear][pkg] : new Array(12).fill(0),
                barPercentage: 0.5,
                stack: 'y' + cmpYear
            });
        });
        return {
            type: 'bar',
            data: { labels: months, datasets: datasets },
            options: chartOptions(true)
        };
    }

    function buildPriceChart() {
        var cmpYear = getCompareYear();
        var targetLine = new Array(12).fill(target);
        return {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    {
                        label: year + ' Revenue (IDR)',
                        backgroundColor: '#47c363',
                        data: priceYoy[year] || new Array(12).fill(0),
                        barPercentage: 0.5
                    },
                    {
                        label: cmpYear + ' Revenue (IDR)',
                        backgroundColor: '#cbd5e0',
                        data: priceYoy[cmpYear] || new Array(12).fill(0),
                        barPercentage: 0.5
                    },
                    {
                        label: 'Target (Rp ' + formatRupiah(target) + ')',
                        type: 'line',
                        borderColor: '#fc544b',
                        borderWidth: 2,
                        borderDash: [6, 3],
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        fill: false,
                        data: targetLine
                    }
                ]
            },
            options: chartOptions(false, true)
        };
    }

    function chartOptions(stacked, isPrice) {
        var yTicks = { beginAtZero: true, fontSize: 11 };
        if (isPrice) {
            yTicks.callback = function(v) { return 'Rp ' + formatRupiah(v); };
        } else {
            yTicks.stepSize = 1;
        }

        var tooltipCb = {};
        if (isPrice) {
            tooltipCb.label = function(item, data) {
                var dsLabel = data.datasets[item.datasetIndex].label || '';
                return dsLabel + ': Rp ' + formatRupiah(item.yLabel);
            };
        }

        return {
            responsive: true,
            legend: { position: 'top', labels: { boxWidth: 12, fontSize: 11 } },
            tooltips: { callbacks: tooltipCb },
            scales: {
                xAxes: [{ gridLines: { display: false }, stacked: stacked }],
                yAxes: [{ ticks: yTicks, stacked: stacked }]
            }
        };
    }

    function formatRupiah(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function renderChart() {
        if (chart) chart.destroy();
        var config;
        if (currentMode === 'sponsors') config = buildSponsorsChart();
        else if (currentMode === 'package') config = buildPackageChart();
        else config = buildPriceChart();
        chart = new Chart(ctx, config);
    }

    window.switchChartMode = function(mode) {
        currentMode = mode;
        var modes = ['sponsors', 'package', 'price'];
        var ids = ['chartModeSponsors', 'chartModePackage', 'chartModePrice'];
        modes.forEach(function(m, i) {
            var btn = document.getElementById(ids[i]);
            if (m === mode) {
                btn.className = 'btn btn-primary active';
            } else {
                btn.className = 'btn btn-outline-primary';
            }
        });
        renderChart();
    };

    document.getElementById('chartCompareYear').addEventListener('change', function() {
        renderChart();
    });

    renderChart();
});
</script>
@endpush
