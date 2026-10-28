{{-- Ringkasan perusahaan peserta. Dihitung di browser dari baris tabel #laravel_crud
     yang lolos filter (pill Member/Non Member/Berbayar/Free/Sponsor + kotak pencarian),
     memakai data-company-key (dinormalisasi di controller) pada <td data-pkg>. --}}
<div class="card shadow-sm border-0 company-summary-card">
    <div class="card-header border-0 d-flex align-items-center justify-content-between flex-wrap"
        style="background:#fff; padding:1.1rem 1.5rem; gap:8px;">
        <div>
            <h5 class="mb-0 fw-bold" style="color:#1a202c;">
                <i class="fas fa-building text-teal mr-2"></i>Ringkasan Perusahaan
            </h5>
            <small class="text-muted" id="company-summary-caption">Menghitung…</small>
        </div>
        <button type="button" class="btn btn-sm btn-light border" data-toggle="collapse"
            data-target="#company-summary-body" aria-expanded="false" aria-controls="company-summary-body">
            <i class="fas fa-chevron-down mr-1"></i>Tampilkan / Sembunyikan
        </button>
    </div>
    <div class="collapse" id="company-summary-body">
        <div class="card-body pt-0" style="padding:0 1.5rem 1.25rem;">
            <div class="company-summary-wrap">
                <table class="table table-sm table-hover mb-0 company-summary-table">
                    <thead>
                        <tr>
                            <th width="40">#</th>
                            <th>Perusahaan</th>
                            <th class="text-center">Peserta</th>
                            <th class="text-center">Member</th>
                            <th class="text-center">Non Member</th>
                            <th class="text-center">Free/Undangan</th>
                            <th class="text-center">Sponsor</th>
                        </tr>
                    </thead>
                    <tbody id="company-summary-rows"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .company-summary-card { border-radius: 16px; margin-bottom: 1.5rem; }
    .company-summary-card .text-teal { color: #0d9488; }
    .company-summary-wrap { max-height: 340px; overflow-y: auto; border: 1px solid #eef0f4; border-radius: 10px; }
    .company-summary-table thead th {
        position: sticky; top: 0; z-index: 1; background: #f8f9fc; border-top: 0;
        font-size: .7rem; text-transform: uppercase; letter-spacing: .03em; color: #6c757d; white-space: nowrap;
    }
    .company-summary-table td { font-size: .83rem; vertical-align: middle; }
    .company-summary-table .cs-zero { color: #cbd2d9; }
    .company-summary-table tr.cs-none td { color: #9aa4b2; font-style: italic; }
</style>

@push('bottom')
    <script>
        (function() {
            function esc(text) {
                return $('<div>').text(text).html();
            }

            function cell(value, strong) {
                if (!value) return '<td class="text-center cs-zero">0</td>';
                return '<td class="text-center">' + (strong ? '<strong>' + value + '</strong>' : value) + '</td>';
            }

            function renderCompanySummary(api) {
                var companies = {};
                var noCompany = { total: 0, member: 0, nonmember: 0, free: 0, sponsor: 0 };
                var participants = 0;

                $(api.rows({ search: 'applied' }).nodes()).each(function() {
                    var $cell = $(this).find('td[data-pkg]');
                    var key = ($cell.attr('data-company-key') || '').toString();
                    var name = ($cell.attr('data-company') || '').toString();
                    var pkg = ($cell.attr('data-pkg') || '').toString();
                    var isMember = ($cell.attr('data-member') || '') === '1';
                    participants++;

                    var bucket;
                    if (key === '') {
                        bucket = noCompany;
                    } else {
                        if (!companies[key]) {
                            companies[key] = { names: {}, total: 0, member: 0, nonmember: 0, free: 0, sponsor: 0 };
                        }
                        bucket = companies[key];
                        bucket.names[name] = (bucket.names[name] || 0) + 1;
                    }
                    bucket.total++;
                    bucket[isMember ? 'member' : 'nonmember']++;
                    if (pkg.indexOf('sponsor') !== -1) bucket.sponsor++;
                    else if (pkg.indexOf('free') !== -1) bucket.free++;
                });

                // Nama tampilan = penulisan yang paling sering dipakai peserta.
                var list = Object.keys(companies).map(function(key) {
                    var c = companies[key];
                    c.label = Object.keys(c.names).sort(function(a, b) {
                        return c.names[b] - c.names[a];
                    })[0];
                    return c;
                }).sort(function(a, b) {
                    return (b.total - a.total) || a.label.localeCompare(b.label);
                });

                var html = list.map(function(c, i) {
                    return '<tr><td class="text-muted">' + (i + 1) + '</td><td>' + esc(c.label) + '</td>' +
                        cell(c.total, true) + cell(c.member) + cell(c.nonmember) + cell(c.free) + cell(c.sponsor) + '</tr>';
                }).join('');
                if (noCompany.total) {
                    html += '<tr class="cs-none"><td></td><td>Tanpa nama perusahaan</td>' +
                        cell(noCompany.total, true) + cell(noCompany.member) + cell(noCompany.nonmember) +
                        cell(noCompany.free) + cell(noCompany.sponsor) + '</tr>';
                }
                if (!html) {
                    html = '<tr><td colspan="7" class="text-center text-muted py-3">Tidak ada peserta pada filter ini.</td></tr>';
                }
                $('#company-summary-rows').html(html);

                var filterLabel = $.trim($('.pill.active').clone().children('.pill-count').remove().end().text()) || 'Semua';
                var search = api.search();
                $('#company-summary-caption').html(
                    '<strong>' + list.length + '</strong> perusahaan dari <strong>' + participants + '</strong> peserta' +
                    ' · Filter: <strong>' + esc(filterLabel) + '</strong>' +
                    (search ? ' · Cari: "' + esc(search) + '"' : '')
                );
            }

            // Dipasang sebelum DataTable di-init (init-nya di document ready, script ini
            // langsung jalan), jadi draw pertama saat init juga ikut terhitung.
            $('#laravel_crud').on('draw.dt', function() {
                renderCompanySummary($(this).DataTable());
            });
        })();
    </script>
@endpush
