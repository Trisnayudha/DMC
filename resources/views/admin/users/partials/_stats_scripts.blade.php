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

        window.initCompanyCategoryTierChart(Chart);

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
