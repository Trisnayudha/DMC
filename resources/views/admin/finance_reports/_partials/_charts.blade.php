{{-- Dua grafik batang per bulan: Total Amount & Actual Amount, tahun terpilih vs tahun pembanding.
     Tooltip: nilai kedua tahun + % = tahun terpilih ÷ tahun pembanding. Klik batang → filter bulan itu.
     Chart.js v2.8 (dimuat global di layouts.inspire.master). Warna mengikuti tahun, sama di dua grafik. --}}
@php
    $chartData = [
        'labels'   => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        'year'     => $filters['year'],
        'compare'  => $filters['compare'],
        'amount'   => array_values(array_map(function ($m) { return $m['amount']; }, $months)),
        'amountVs' => array_values(array_map(function ($m) { return $m['amount']; }, $compareMonths)),
        'actual'   => array_values(array_map(function ($m) { return $m['actual']; }, $months)),
        'actualVs' => array_values(array_map(function ($m) { return $m['actual']; }, $compareMonths)),
        'monthUrl' => route('admin.finance_reports.index', array_filter(['year' => $filters['year'], 'compare' => $filters['compare'], 'keyword' => $filters['keyword']])),
    ];
    $sum = function ($key, $source) { return collect($source)->sum($key); };
    $charts = [
        ['id' => 'chartAmount', 'title' => 'Total Amount per Month', 'key' => 'amount'],
        ['id' => 'chartActual', 'title' => 'Actual Amount per Month', 'key' => 'actual'],
    ];
@endphp
<div class="row">
    @foreach ($charts as $chart)
        @php
            $cur = $sum($chart['key'], $months);
            $vs  = $sum($chart['key'], $compareMonths);
        @endphp
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:6px;">
                    <h4 class="mb-0">{{ $chart['title'] }}</h4>
                    <div class="d-flex align-items-center" style="gap:12px;font-size:12px;color:#6c757d;">
                        <span><span class="fr-legend-swatch" style="background:#6777ef;"></span>{{ $filters['year'] }}</span>
                        <span><span class="fr-legend-swatch" style="background:#b8bfd6;"></span>{{ $filters['compare'] }}</span>
                        <span class="text-dark font-weight-600" title="{{ $filters['year'] }} ÷ {{ $filters['compare'] }}">
                            {{ $vs > 0 ? number_format($cur / $vs * 100, 0, ',', '.') . '%' : 'n/a' }} vs {{ $filters['compare'] }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div style="position:relative;height:260px;">
                        <canvas id="{{ $chart['id'] }}" role="img"
                            aria-label="{{ $chart['title'] }}, {{ $filters['year'] }} compared with {{ $filters['compare'] }}"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<style>
    .fr-legend-swatch { display:inline-block; width:10px; height:10px; border-radius:2px; margin-right:5px; vertical-align:-1px; }
</style>

@push('bottom')
<script>
(function () {
    if (typeof Chart === 'undefined') { return; }
    var d = {!! json_encode($chartData) !!};

    function rupiah(v) { return 'Rp ' + Math.round(v).toLocaleString('id-ID'); }
    // Axis ticks ringkas: Rp 50M (million) / Rp 1.2B (billion).
    function shortRupiah(v) {
        if (v >= 1e9) { return 'Rp ' + (v / 1e9).toLocaleString('en-US', { maximumFractionDigits: 1 }) + 'B'; }
        if (v >= 1e6) { return 'Rp ' + (v / 1e6).toLocaleString('en-US', { maximumFractionDigits: 0 }) + 'M'; }
        return 'Rp ' + v.toLocaleString('id-ID');
    }
    // % = tahun terpilih ÷ tahun pembanding.
    function ratio(cur, vs) {
        if (!vs) { return cur ? 'n/a (no ' + d.compare + ' data)' : '—'; }
        return Math.round(cur / vs * 100).toLocaleString('id-ID') + '% of ' + d.compare;
    }

    function draw(id, current, previous) {
        var el = document.getElementById(id);
        if (!el) { return; }
        new Chart(el, {
            type: 'bar',
            data: {
                labels: d.labels,
                datasets: [
                    { label: String(d.compare), data: previous, backgroundColor: '#b8bfd6', hoverBackgroundColor: '#a3abc6',
                      borderColor: '#ffffff', borderWidth: 1, barPercentage: 0.9, categoryPercentage: 0.7 },
                    { label: String(d.year), data: current, backgroundColor: '#6777ef', hoverBackgroundColor: '#5563d8',
                      borderColor: '#ffffff', borderWidth: 1, barPercentage: 0.9, categoryPercentage: 0.7 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false }, // legenda ada di header kartu
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: '#ffffff',
                    titleFontColor: '#34395e',
                    bodyFontColor: '#34395e',
                    footerFontColor: '#34395e',
                    borderColor: '#e4e6fc',
                    borderWidth: 1,
                    xPadding: 10,
                    yPadding: 8,
                    callbacks: {
                        title: function (items) { return items.length ? d.labels[items[0].index] : ''; },
                        label: function (item, data) {
                            return ' ' + data.datasets[item.datasetIndex].label + ': ' + rupiah(item.yLabel);
                        },
                        footer: function (items) {
                            if (!items.length) { return ''; }
                            var i = items[0].index;
                            return ratio(current[i], previous[i]);
                        }
                    }
                },
                hover: { mode: 'index', intersect: false },
                scales: {
                    xAxes: [{ gridLines: { display: false } }],
                    yAxes: [{
                        ticks: { beginAtZero: true, maxTicksLimit: 5, callback: shortRupiah, fontColor: '#98a6ad' },
                        gridLines: { color: '#f1f3f9', zeroLineColor: '#e4e6fc', drawBorder: false }
                    }]
                },
                // Klik bulan → kartu & tabel difilter ke bulan itu.
                onClick: function (evt, elements) {
                    if (!elements || !elements.length) { return; }
                    var month = elements[0]._index + 1;
                    window.location = d.monthUrl + (d.monthUrl.indexOf('?') === -1 ? '?' : '&') + 'month=' + month;
                }
            }
        });
        el.style.cursor = 'pointer';
    }

    draw('chartAmount', d.amount, d.amountVs);
    draw('chartActual', d.actual, d.actualVs);
})();
</script>
@endpush
