{{-- JS panel "Statistik & Chart": dipanggil sekali lewat window.initMemberStats(data)
     setelah HTML panel (_stats_panel) dimasukkan & Chart.js v4 selesai dimuat.
     data = payload JSON dari UsersController@stats. --}}
<script>
    window.initMemberStats = function(data) {
        new Chart(document.getElementById('validate48hChart'), {
            type: 'doughnut',
            data: {
                labels: ['Validated ≤ 48h', 'Validated > 48h'],
                datasets: [{
                    data: [data.validation.within48h, data.validation.after48h],
                    backgroundColor: ['#0ca30c', '#d03b3b']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        (function() {
            var periods = data.registrations; // { weekly|monthly|yearly: { labels, counts, ranges } }
            var toggleButtons = {
                weekly:  document.getElementById('reg-toggle-weekly'),
                monthly: document.getElementById('reg-toggle-monthly'),
                yearly:  document.getElementById('reg-toggle-yearly'),
            };
            var currentPeriodKey = 'weekly';

            // Safety net for the stuck-backdrop/unscrollable-page case: once no
            // modal is left open, force-clear anything Bootstrap might have
            // left behind (a duplicate .modal-backdrop, or the body's
            // scroll-lock state) instead of trusting a single hide() call to
            // have fully cleaned up.
            $('#registrationBreakdownModal').on('hidden.bs.modal', function() {
                if ($('.modal.show').length === 0) {
                    $('body').removeClass('modal-open').css({ overflow: '', 'padding-right': '' });
                    $('.modal-backdrop').remove();
                }
            });

            var registrationsChart = new Chart(document.getElementById('registrationsChart'), {
                type: 'bar',
                data: {
                    labels: periods.weekly.labels,
                    datasets: [{
                        label: 'New Registrations',
                        data: periods.weekly.counts,
                        backgroundColor: '#2a78d6',
                        borderRadius: 4,
                        maxBarThickness: 28,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                    onClick: function(evt) {
                        var points = registrationsChart.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, false);
                        if (!points.length) return;
                        var index = points[0]._index !== undefined ? points[0]._index : points[0].index;
                        var range = periods[currentPeriodKey].ranges[index];
                        var label = periods[currentPeriodKey].labels[index];
                        if (range) showRegistrationBreakdown(label, range.from, range.to);
                    }
                }
            });

            function selectPeriod(key) {
                currentPeriodKey = key;
                Object.keys(toggleButtons).forEach(function(k) {
                    toggleButtons[k].classList.toggle('btn-primary', k === key);
                    toggleButtons[k].classList.toggle('btn-outline-primary', k !== key);
                });
                registrationsChart.data.labels = periods[key].labels;
                registrationsChart.data.datasets[0].data = periods[key].counts;
                registrationsChart.update();
            }

            Object.keys(toggleButtons).forEach(function(key) {
                toggleButtons[key].addEventListener('click', function() { selectPeriod(key); });
            });

            var regBreakdownMembers = []; // current period's member list, filtered client-side (no re-fetch)
            var regBreakdownTotal = 0;

            function escapeHtml(s) {
                return $('<div>').text(s || '').html();
            }

            // Same 4 buckets/colors as the "Verification Status" doughnut on
            // this same page — a status not explicitly active/declined/
            // deactivated is "waiting" (never verified one way or the other).
            var statusMeta = {
                active:      { label: 'Verified',    color: '#0ca30c' },
                waiting:     { label: 'Waiting',      color: '#fab219' },
                declined:    { label: 'Declined',     color: '#d03b3b' },
                deactivated: { label: 'Deactivated',  color: '#898781' },
            };
            function statusBucket(status) {
                status = (status || '').toLowerCase();
                return statusMeta[status] ? status : 'waiting';
            }

            function renderRegBreakdownTable() {
                var q = $('#reg-breakdown-search').val().trim().toLowerCase();
                var leadsOnly = $('#reg-breakdown-leads-only').is(':checked');
                var statusFilter = $('#reg-breakdown-status-filter').val();

                var filtered = regBreakdownMembers.filter(function(m) {
                    if (leadsOnly && !m.is_lead) return false;
                    if (statusFilter && statusBucket(m.status_member) !== statusFilter) return false;
                    if (!q) return true;
                    var haystack = [m.name, m.email, m.company_name, m.source].join(' ').toLowerCase();
                    return haystack.indexOf(q) !== -1;
                });

                var $tbody = $('#reg-breakdown-members-tbody').empty();

                if (filtered.length === 0) {
                    $('#reg-breakdown-no-match').show();
                } else {
                    $('#reg-breakdown-no-match').hide();
                    $.each(filtered, function(i, m) {
                        var leadBadge = m.is_lead
                            ? '<span class="badge badge-primary" title="Potential lead — Explore Marketing"><i class="fas fa-bullseye"></i></span>'
                            : '<span class="text-muted">-</span>';
                        var bucket = statusBucket(m.status_member);
                        var statusBadge = '<span class="reg-status-badge" style="background:' + statusMeta[bucket].color + ';">' + statusMeta[bucket].label + '</span>';
                        $tbody.append(
                            '<tr>' +
                                '<td><span class="cell-truncate font-weight-bold" title="' + escapeHtml(m.name) + '">' + escapeHtml(m.name || '-') + '</span><br>' +
                                    '<small class="cell-truncate text-muted" title="' + escapeHtml(m.email) + '">' + escapeHtml(m.email) + '</small></td>' +
                                '<td><span class="cell-truncate" title="' + escapeHtml(m.company_name) + '">' + escapeHtml(m.company_name || '-') + '</span></td>' +
                                '<td>' + escapeHtml(m.source) + '</td>' +
                                '<td>' + statusBadge + '</td>' +
                                '<td class="text-center">' + leadBadge + '</td>' +
                                '<td class="text-nowrap"><small>' + (m.created_at || '-') + '</small></td>' +
                            '</tr>'
                        );
                    });
                }

                var note = filtered.length !== regBreakdownMembers.length
                    ? filtered.length + ' dari ' + regBreakdownMembers.length + ' ditampilkan'
                    : (regBreakdownMembers.length + ' member' + (regBreakdownMembers.length !== regBreakdownTotal ? ' (dari ' + regBreakdownTotal + ' total, terbaru dulu)' : ''));
                $('#reg-breakdown-members-note').text(note);
            }

            $(document).on('input', '#reg-breakdown-search', renderRegBreakdownTable);
            $(document).on('change', '#reg-breakdown-leads-only', renderRegBreakdownTable);
            $(document).on('change', '#reg-breakdown-status-filter', renderRegBreakdownTable);

            function showRegistrationBreakdown(label, dateFrom, dateTo) {
                $('#reg-breakdown-period').text(label);
                $('#reg-breakdown-loading').show();
                $('#reg-breakdown-empty').hide();
                $('#reg-breakdown-content').hide();
                $('#reg-breakdown-chips').empty();
                $('#reg-breakdown-status-chips').empty();
                $('#reg-breakdown-leads-split').text('');
                $('#reg-breakdown-members-tbody').empty();
                $('#reg-breakdown-members-note').text('');
                $('#reg-breakdown-search').val('');
                $('#reg-breakdown-leads-only').prop('checked', false);
                $('#reg-breakdown-status-filter').val('');
                regBreakdownMembers = [];
                regBreakdownTotal = 0;

                // Guard against re-triggering show() on an already-open modal —
                // the chart stays clickable underneath while it's open/mid-
                // transition, so a second bar click here is easy to do by
                // accident. Bootstrap 4 doesn't dedupe that: it stacks a second
                // .modal-backdrop, and closing only tears down one of them,
                // leaving the other's opacity + the page's scroll-lock stuck
                // behind. If it's already open, just refresh the content in
                // place instead of calling show() again.
                if (!$('#registrationBreakdownModal').hasClass('show')) {
                    $('#registrationBreakdownModal').modal('show');
                }

                $.ajax({
                    url: '{{ route('users.registration_source_breakdown') }}',
                    method: 'GET',
                    data: { date_from: dateFrom, date_to: dateTo },
                    dataType: 'json'
                }).done(function(res) {
                    $('#reg-breakdown-loading').hide();

                    if (!res || !res.success || !res.breakdown || res.breakdown.length === 0) {
                        $('#reg-breakdown-empty').show();
                        return;
                    }

                    regBreakdownMembers = res.members || [];
                    regBreakdownTotal = res.members_total || regBreakdownMembers.length;

                    $('#reg-breakdown-total').text(res.total);
                    $('#reg-breakdown-leads').text(res.leads_count || 0);
                    var leadsPct = res.total > 0 ? Math.round((res.leads_count / res.total) * 100) : 0;
                    $('#reg-breakdown-leads-pct').text('(' + leadsPct + '%)');

                    // Still viable (active/waiting) vs already dead (declined) —
                    // a declined applicant can't realistically be pursued as a
                    // sponsorship lead anymore, so this splits the number above
                    // instead of leaving "Potential Leads" looking bigger than
                    // it actually, practically is. Wording kept report-ready
                    // (standard CRM terms), not casual shorthand.
                    if ((res.leads_count || 0) > 0) {
                        $('#reg-breakdown-leads-split').html(
                            '<span class="viable">' + (res.leads_viable || 0) + ' Active Prospects</span>' +
                            (res.leads_dead ? ' &nbsp;·&nbsp; <span class="dead">' + res.leads_dead + ' Disqualified</span>' : '')
                        );
                    }

                    $.each(res.breakdown, function(i, row) {
                        var pct = res.total > 0 ? Math.round((row.total / res.total) * 100) : 0;
                        $('#reg-breakdown-chips').append(
                            '<span class="reg-chip">' +
                                '<i class="' + row.icon + '" style="color:' + row.color + ';"></i>' +
                                row.label + ' <strong>' + row.total + '</strong> <span class="reg-chip-pct">' + pct + '%</span>' +
                            '</span>'
                        );
                    });

                    var statusOrder = ['active', 'waiting', 'declined', 'deactivated'];
                    $.each(statusOrder, function(i, key) {
                        var count = (res.status && res.status[key === 'active' ? 'verified' : key]) || 0;
                        if (count === 0) return;
                        var pct = res.total > 0 ? Math.round((count / res.total) * 100) : 0;
                        $('#reg-breakdown-status-chips').append(
                            '<span class="reg-chip">' +
                                '<span class="reg-chip-dot" style="background:' + statusMeta[key].color + ';"></span>' +
                                statusMeta[key].label + ' <strong>' + count + '</strong> <span class="reg-chip-pct">' + pct + '%</span>' +
                            '</span>'
                        );
                    });

                    renderRegBreakdownTable();
                    $('#reg-breakdown-content').show();
                }).fail(function() {
                    $('#reg-breakdown-loading').hide();
                    $('#reg-breakdown-empty').text('Gagal memuat breakdown.').show();
                });
            }
        })();

        // Member composition: Job Tier & Company Category × Job Tier.
        (function() {
            var composition = data.memberComposition;
            var tiers = composition.tiers;
            var pct = function(n, of) { return (of ? Math.round(n * 1000 / of) / 10 : 0).toLocaleString('id-ID'); };
            var fmt = function(n) { return Number(n).toLocaleString('id-ID'); };

            // Satu angka di ujung tiap bar horizontal (total segmen yang sedang
            // tampil) — bukan angka di setiap segmen. Ikut berubah kalau tier
            // disembunyikan lewat legend.
            var endLabels = {
                id: 'endLabels',
                afterDatasetsDraw: function(chart, args, opts) {
                    var ctx = chart.ctx;
                    ctx.save();
                    ctx.font = '600 11px ' + Chart.defaults.font.family;
                    ctx.fillStyle = '#495057';
                    ctx.textBaseline = 'middle';
                    chart.data.labels.forEach(function(label, i) {
                        var sum = 0, x = chart.scales.x.getPixelForValue(0), y = null;
                        chart.data.datasets.forEach(function(ds, d) {
                            var meta = chart.getDatasetMeta(d);
                            if (meta.hidden || !meta.data[i]) return;
                            sum += ds.data[i];
                            x = Math.max(x, meta.data[i].x);
                            y = meta.data[i].y;
                        });
                        if (y !== null && sum > 0) ctx.fillText(opts.format(sum), x + 6, y);
                    });
                    ctx.restore();
                }
            };

            var axisStyle = {
                grid: { color: '#eef0f3', drawTicks: false },
                border: { display: false },
                ticks: { color: '#6c757d', font: { size: 10 }, precision: 0, callback: function(v) { return fmt(v); } }
            };

            new Chart(document.getElementById('jobTierChart'), {
                type: 'bar',
                data: {
                    labels: tiers.map(function(t) { return t.label; }),
                    datasets: [{
                        label: 'Member Active',
                        data: tiers.map(function(t) { return t.count; }),
                        backgroundColor: tiers.map(function(t) { return t.color; }),
                        borderRadius: 4,
                        maxBarThickness: 26,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: { padding: { right: 45 } },
                    scales: {
                        x: axisStyle,
                        y: { grid: { display: false }, border: { display: false }, ticks: { color: '#343a40', font: { size: 11, weight: '600' } } }
                    },
                    plugins: {
                        legend: { display: false },
                        endLabels: { format: function(n) { return pct(n, composition.total) + '%'; } },
                        tooltip: {
                            callbacks: {
                                title: function(items) { var t = tiers[items[0].dataIndex]; return t.label + ' — ' + t.roles; },
                                label: function(item) { return ' ' + fmt(item.raw) + ' member (' + pct(item.raw, composition.total) + '%)'; },
                                footer: function() { return 'Klik untuk detail'; }
                            }
                        }
                    },
                    onClick: function(evt, elements) {
                        if (elements.length) window.openJobTierDetail({ tier: tiers[elements[0].index].tier });
                    }
                },
                plugins: [endLabels]
            });

            $('#job-tier-list').on('click', '.company-category-item', function() {
                window.openJobTierDetail({ tier: Number($(this).attr('data-tier')), role: $(this).attr('data-role') || null });
            });

            var categories = composition.categories;
            var categoryTotals = categories.map(function(c, i) {
                return tiers.reduce(function(sum, t) { return sum + t.byCategory[i]; }, 0);
            });

            // Card ini collapse by default — chart digambar saat pertama dibuka supaya
            // ukurannya benar (canvas di elemen tersembunyi lebarnya 0).
            var categoryTierChartDrawn = false;
            $('#company-category-tier-collapse')
                .on('shown.bs.collapse', function() {
                    $('#company-category-tier-toggle span').text('Hide');
                    $('#company-category-tier-toggle i').attr('class', 'fas fa-eye-slash mr-1');
                    if (!categoryTierChartDrawn) {
                        categoryTierChartDrawn = true;
                        drawCategoryTierChart();
                    }
                })
                .on('hidden.bs.collapse', function() {
                    $('#company-category-tier-toggle span').text('Show');
                    $('#company-category-tier-toggle i').attr('class', 'fas fa-eye mr-1');
                });

            function drawCategoryTierChart() {
                new Chart(document.getElementById('companyCategoryTierChart'), {
                    type: 'bar',
                    data: {
                        labels: categories,
                        datasets: tiers.map(function(t) {
                            return {
                                label: t.label,
                                data: t.byCategory,
                                backgroundColor: t.color,
                                // 2px celah putih antar segmen supaya tier bersebelahan tetap terpisah.
                                borderColor: '#ffffff',
                                borderWidth: { right: 2 },
                                borderSkipped: false,
                                maxBarThickness: 16,
                            };
                        })
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: { padding: { right: 40 } },
                        interaction: { mode: 'index', axis: 'y', intersect: false },
                        scales: {
                            x: Object.assign({ stacked: true }, axisStyle),
                            y: {
                                stacked: true,
                                grid: { display: false },
                                border: { display: false },
                                ticks: {
                                    color: '#343a40',
                                    font: { size: 11 },
                                    callback: function(value) {
                                        var label = this.getLabelForValue(value);
                                        return label.length > 32 ? label.slice(0, 31) + '…' : label;
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: { position: 'top', align: 'end', labels: { boxWidth: 10, boxHeight: 10, font: { size: 11 } } },
                            endLabels: { format: fmt },
                            tooltip: {
                                callbacks: {
                                    label: function(item) {
                                        var t = tiers[item.datasetIndex];
                                        return ' ' + t.label + ': ' + fmt(item.raw) + ' (' + pct(item.raw, categoryTotals[item.dataIndex]) + '%)';
                                    },
                                    footer: function(items) {
                                        return 'Total: ' + fmt(categoryTotals[items[0].dataIndex]) + ' member';
                                    }
                                }
                            }
                        }
                    },
                    plugins: [endLabels]
                });
            }
        })();

        // Company Category Tier (Company Database) — pie 5 grup besar (pola sama dengan
        // event detail IndonesiaMiner); daftar samping = grup + kategori di dalamnya.
        (function() {
            // Warna brand desain "Member Compositions" (hitam, merah, abu, marun) + tan
            // senada untuk grup ke-5. Urutan dipilih supaya slice bersebelahan (termasuk
            // slice terakhir ↔ pertama) tidak sama-sama gelap.
            var palette = ['#1d1d1f', '#8e0f15', '#4b5058', '#3c1016', '#a8896c'];
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

                    $('#company-category-total').text(fmt(total) + ' company');
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
                            + '<span class="cc-name">' + esc(t.name) + '</span>'
                            + '<span class="cc-count">' + fmt(t.count) + '</span>'
                            + '<span class="cc-pct">' + pct(t.count, total) + '%</span>'
                            + '</button>' + categories + '</div>';
                    }).join(''));

                    $('#company-category-loading').hide();
                    $('#company-category-content').show();

                    new Chart(document.getElementById('companyCategoryChart'), {
                        type: 'pie',
                        data: {
                            labels: tiers.map(function(t) { return t.name; }),
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
        })();

        new Chart(document.getElementById('statusBreakdownChart'), {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Pending', 'Declined', 'Deactivated'],
                datasets: [{
                    data: [
                        data.status.active, data.status.pending, data.status.declined, data.status.deactivated
                    ],
                    backgroundColor: ['#0ca30c', '#fab219', '#d03b3b', '#898781']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } }
                }
            }
        });
    };
</script>
