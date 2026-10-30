{{-- Chart Company Category Tier (Company Database): pie 6 grup + daftar kategori,
     klik → modal detail (_modal_company_category). Dipakai di Members & Company Database.
     Butuh Chart.js v4 — dikirim sebagai parameter karena layout memuat v2 di window.Chart. --}}
<script>
    window.initCompanyCategoryTierChart = function(Chart) {
        // Warna brand desain "Member Compositions" (hitam, merah, abu, marun) + tan
        // & abu muda senada untuk grup ke-5 dan ke-6. Urutan dipilih supaya slice bersebelahan (termasuk
        // slice terakhir ↔ pertama) tidak sama-sama gelap.
        var palette = ['#1d1d1f', '#8e0f15', '#4b5058', '#3c1016', '#a8896c', '#c4c6ca'];
        var fmt = function(n) { return Number(n).toLocaleString('id-ID'); };
        var pct = function(n, of) { return (of ? Math.round(n * 1000 / of) / 10 : 0).toLocaleString('id-ID'); };
        var esc = function(s) { return $('<span>').text(s).html(); };

        // Label persentase di tengah tiap slice; teks putih/gelap dipilih dari
        // terang-gelapnya warna slice supaya tetap terbaca.
        var sliceLabels = {
            id: 'sliceLabels',
            afterDatasetsDraw: function(chart) {
                var ds = chart.data.datasets[0];
                var sum = ds.data.reduce(function(s, v) { return s + v; }, 0);
                var ctx = chart.ctx;
                ctx.save();
                ctx.font = '700 12px ' + Chart.defaults.font.family;
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                chart.getDatasetMeta(0).data.forEach(function(arc, i) {
                    if (!sum || ds.data[i] / sum < 0.03) return; // slice terlalu kecil untuk label
                    var hex = ds.backgroundColor[i].replace('#', '');
                    var r = parseInt(hex.substr(0, 2), 16), g = parseInt(hex.substr(2, 2), 16), b = parseInt(hex.substr(4, 2), 16);
                    ctx.fillStyle = (0.299 * r + 0.587 * g + 0.114 * b) > 150 ? '#1f2328' : '#ffffff';
                    var pos = arc.tooltipPosition();
                    ctx.fillText(pct(ds.data[i], sum) + '%', pos.x, pos.y);
                });
                ctx.restore();
            }
        };

        // Nama grup di luar pie dengan garis penunjuk (callout) — supaya chart
        // bisa langsung di-screenshot untuk management tanpa daftar samping.
        // Label di sisi yang sama digeser vertikal kalau bertumpuk.
        var calloutLabels = {
            id: 'calloutLabels',
            afterDatasetsDraw: function(chart) {
                var ctx = chart.ctx;
                var ds = chart.data.datasets[0];
                var sum = ds.data.reduce(function(s, v) { return s + v; }, 0);
                var maxWidth = 165, lineHeight = 13, elbow = 14, run = 10;
                var font = '11px ' + Chart.defaults.font.family;
                var boldFont = '700 11px ' + Chart.defaults.font.family;

                var wrap = function(text) {
                    ctx.font = boldFont;
                    var words = text.split(' '), lines = [], line = '';
                    words.forEach(function(w) {
                        var test = line ? line + ' ' + w : w;
                        if (ctx.measureText(test).width > maxWidth && line) { lines.push(line); line = w; }
                        else { line = test; }
                    });
                    if (line) lines.push(line);
                    return lines;
                };

                var labels = chart.getDatasetMeta(0).data.map(function(arc, i) {
                    var mid = (arc.startAngle + arc.endAngle) / 2;
                    var cos = Math.cos(mid), sin = Math.sin(mid);
                    var lines = wrap(chart.data.labels[i]);
                    var count = pct(ds.data[i], sum) + '%';
                    return {
                        right: cos >= 0,
                        color: ds.backgroundColor[i],
                        x0: arc.x + cos * arc.outerRadius, y0: arc.y + sin * arc.outerRadius,
                        x1: arc.x + cos * (arc.outerRadius + elbow), y1: arc.y + sin * (arc.outerRadius + elbow),
                        lines: lines, count: count,
                        height: (lines.length + 1) * lineHeight,
                    };
                });

                // Geser label per sisi supaya tidak saling menimpa.
                [true, false].forEach(function(side) {
                    var group = labels.filter(function(l) { return l.right === side; })
                        .sort(function(a, b) { return a.y1 - b.y1; });
                    var bottom = -Infinity;
                    group.forEach(function(l) {
                        l.ty = Math.max(l.y1, bottom + l.height / 2 + 6);
                        bottom = l.ty + l.height / 2;
                    });
                });

                ctx.save();
                labels.forEach(function(l) {
                    var x2 = l.x1 + (l.right ? run : -run);
                    ctx.strokeStyle = l.color;
                    ctx.lineWidth = 1.5;
                    ctx.beginPath();
                    ctx.moveTo(l.x0, l.y0);
                    ctx.lineTo(l.x1, l.ty);
                    ctx.lineTo(x2, l.ty);
                    ctx.stroke();

                    var tx = x2 + (l.right ? 4 : -4);
                    var top = l.ty - l.height / 2 + lineHeight / 2;
                    ctx.textAlign = l.right ? 'left' : 'right';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = '#343a40';
                    ctx.font = boldFont;
                    l.lines.forEach(function(line, j) { ctx.fillText(line, tx, top + j * lineHeight); });
                    ctx.fillStyle = '#6c757d';
                    ctx.font = font;
                    ctx.fillText(l.count, tx, top + l.lines.length * lineHeight);
                });
                ctx.restore();
            }
        };

        $.getJSON(@json(route('admin.company_database.category_breakdown')))
            .done(function(res) {
                var tiers = res.tiers;
                var total = res.total;

                $('#company-category-total').text(fmt(total) + ' company verified');
                $('#company-category-list').html(tiers.map(function(t, i) {
                    var categories = t.categories.map(function(c) {
                        return '<button type="button" class="company-category-item cc-sub" data-category="' + esc(c.name) + '"'
                            + ' title="' + esc(c.name) + ' — klik untuk detail">'
                            + '<span class="cc-name">' + esc(c.name)
                            + (c.has_subcategories ? ' <i class="fas fa-sitemap text-muted ml-1" title="Punya subcategory"></i>' : '') + '</span>'
                            + '<span class="cc-count">' + fmt(c.count) + '</span>'
                            + '<span class="cc-pct">' + pct(c.count, total) + '%</span>'
                            + '</button>';
                    }).join('');
                    return '<div class="cc-tier-group">'
                        + '<button type="button" class="company-category-item cc-tier" data-tier="' + esc(t.name) + '"'
                        + ' title="' + esc(t.name) + ' — klik untuk detail">'
                        + '<span class="cc-dot" style="background:' + palette[i] + ';"></span>'
                        + '<span class="cc-name"><span class="text-muted">' + esc(t.label) + '</span> ' + esc(t.name) + '</span>'
                        + '<span class="cc-count">' + fmt(t.count) + '</span>'
                        + '<span class="cc-pct">' + pct(t.count, total) + '%</span>'
                        + '</button>' + categories + '</div>';
                }).join(''));

                $('#company-category-loading').hide();
                $('#company-category-content').show();

                new Chart(document.getElementById('companyCategoryChart'), {
                    type: 'pie',
                    data: {
                        labels: tiers.map(function(t) { return t.label + ': ' + t.name; }),
                        datasets: [{
                            data: tiers.map(function(t) { return t.count; }),
                            backgroundColor: palette.slice(0, tiers.length),
                            borderColor: '#ffffff',
                            borderWidth: 2,
                            hoverOffset: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        // Ruang kiri-kanan untuk label callout nama grup.
                        layout: { padding: { left: 190, right: 190, top: 24, bottom: 24 } },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(item) {
                                        return ' ' + fmt(item.raw) + ' company (' + pct(item.raw, total) + '%)';
                                    },
                                    footer: function() { return 'Klik untuk detail'; }
                                }
                            }
                        },
                        onClick: function(evt, elements) {
                            if (elements.length) window.openCompanyCategoryDetail({ tier: tiers[elements[0].index].name });
                        }
                    },
                    plugins: [sliceLabels, calloutLabels]
                });
            })
            .fail(function() {
                $('#company-category-loading').text('Gagal memuat data company category.');
            });

        $('#company-category-list').on('click', '.company-category-item', function() {
            var tier = $(this).attr('data-tier');
            window.openCompanyCategoryDetail(tier ? { tier: tier } : { category: $(this).attr('data-category') });
        });
    };
</script>
