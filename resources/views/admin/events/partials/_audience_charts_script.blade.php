{{-- Draws the charts of _audience_charts (Chart.js v2). Call drawAudienceCharts(audience)
     once the canvases are visible — a hidden canvas has no size. --}}
<script>
    function drawAudienceCharts(audience) {
        var pct = function(n) {
            return audience.total ? (Math.round(n * 1000 / audience.total) / 10).toLocaleString('id-ID') : '0';
        };
        var tooltip = function(labels) {
            return {
                callbacks: {
                    label: function(item, data) {
                        var n = data.datasets[0].data[item.index];
                        return ' ' + labels[item.index] + ': ' + n + ' (' + pct(n) + '%)';
                    }
                }
            };
        };

        var groups = audience.categories;
        new Chart(document.getElementById('audienceCategoryChart'), {
            type: 'doughnut',
            data: {
                labels: groups.map(function(g) { return g.label; }),
                datasets: [{
                    data: groups.map(function(g) { return g.count; }),
                    backgroundColor: groups.map(function(g) { return g.color; }),
                    borderColor: '#ffffff',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutoutPercentage: 55,
                legend: { display: false },
                tooltips: tooltip(groups.map(function(g) { return g.label + ' ' + g.name; })),
            }
        });

        var tiers = audience.tiers;
        new Chart(document.getElementById('audienceTierChart'), {
            type: 'horizontalBar',
            data: {
                labels: tiers.map(function(t) { return t.label; }),
                datasets: [{
                    data: tiers.map(function(t) { return t.count; }),
                    backgroundColor: tiers.map(function(t) { return t.color; }),
                    maxBarThickness: 26,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                tooltips: tooltip(tiers.map(function(t) { return t.label + ' ' + t.roles; })),
                scales: {
                    xAxes: [{ ticks: { beginAtZero: true, precision: 0 }, gridLines: { color: '#eef0f3' } }],
                    yAxes: [{ gridLines: { display: false } }]
                }
            }
        });
    }
</script>
