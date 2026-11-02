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
            <div class="company-summary-controls">
                <button type="button" id="company-summary-clear" class="btn btn-sm btn-outline-secondary" hidden>Hapus filter perusahaan</button>
            </div>
            <p class="text-muted small mb-2">Klik nama perusahaan untuk memfilter daftar peserta.</p>
            <div class="company-summary-wrap">
                <table id="company-summary-table" class="table table-sm table-hover mb-0 company-summary-table">
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
    .company-summary-controls { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:10px; }
    .company-summary-wrap { overflow-x: auto; border: 1px solid #eef0f4; border-radius: 10px; }
    .company-summary-table { min-width:680px; }
    .company-summary-table thead th {
        position: sticky; top: 0; z-index: 1; background: #f8f9fc; border-top: 0;
        font-size: .7rem; text-transform: uppercase; letter-spacing: .03em; color: #6c757d; white-space: nowrap;
    }
    .company-summary-table td { font-size: .83rem; vertical-align: middle; }
    .company-summary-table .cs-zero { color: #cbd2d9; }
    .company-summary-table tr.cs-none td { color: #9aa4b2; font-style: italic; }
    .company-summary-link { white-space:normal; text-align:left; }
    @media (max-width: 575px) {
        .company-summary-card .card-header { padding:1rem !important; }
        .company-summary-card .card-body { padding:0 1rem 1rem !important; }
        .company-summary-wrap .dataTables_filter, .company-summary-wrap .dataTables_length { float:none; text-align:left; }
    }
</style>

@push('bottom')
    <script>
        (function() {
            var selectedCompany = null;
            var summaryTable;
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'laravel_crud' || selectedCompany === null) return true;
                var node = settings.aoData[dataIndex].nTr;
                return ($(node).find('td[data-pkg]').attr('data-company-key') || '') === selectedCompany;
            });
            function esc(text) {
                return $('<div>').text(text).html();
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
                    c.key = key;
                    c.label = Object.keys(c.names).sort(function(a, b) {
                        return c.names[b] - c.names[a];
                    })[0];
                    return c;
                }).sort(function(a, b) {
                    return (b.total - a.total) || a.label.localeCompare(b.label);
                });

                var rows = list.map(function(c, i) {
                    return [i + 1, '<button type="button" class="btn btn-link btn-sm p-0 company-summary-link" data-company-key="' + esc(c.key) + '">' + esc(c.label) + '</button>', c.total, c.member, c.nonmember, c.free, c.sponsor];
                });
                if (noCompany.total) rows.push(['', 'Tanpa nama perusahaan', noCompany.total, noCompany.member, noCompany.nonmember, noCompany.free, noCompany.sponsor]);
                if (summaryTable) summaryTable.clear().rows.add(rows).draw(false);

                var filterLabel = $.trim($('.pill.active').clone().children('.pill-count').remove().end().text()) || 'Semua';
                var search = api.search();
                $('#company-summary-caption').html(
                    '<strong>' + Object.keys(companies).length + '</strong> perusahaan dari <strong>' + participants + '</strong> peserta' +
                    ' · Filter: <strong>' + esc(filterLabel) + '</strong>' +
                    (search ? ' · Cari: "' + esc(search) + '"' : '')
                );
                $('#company-summary-clear').prop('hidden', selectedCompany === null);
            }

            $(function() {
                summaryTable = $('#company-summary-table').DataTable({
                    pageLength: 10,
                    order: [[2, 'desc']],
                    columnDefs: [{ targets: 0, orderable: false }, { targets: [2, 3, 4, 5, 6], className: 'text-center' }],
                    language: { search: 'Cari perusahaan:', lengthMenu: 'Tampilkan _MENU_ perusahaan', info: 'Menampilkan _START_–_END_ dari _TOTAL_ perusahaan', infoEmpty: 'Tidak ada perusahaan', zeroRecords: 'Tidak ada perusahaan yang cocok', paginate: { previous: '‹', next: '›' } }
                });
                if ($.fn.DataTable.isDataTable('#laravel_crud')) renderCompanySummary($('#laravel_crud').DataTable());
            });
            $('#company-summary-body').on('shown.bs.collapse', function() { if (summaryTable) summaryTable.columns.adjust(); });
            $('#company-summary-rows').on('click', '.company-summary-link', function() {
                selectedCompany = $(this).attr('data-company-key');
                $('#laravel_crud').DataTable().draw();
            });
            $('#company-summary-clear').on('click', function() {
                selectedCompany = null;
                $('#laravel_crud').DataTable().draw();
            });

            // Dipasang sebelum DataTable di-init (init-nya di document ready, script ini
            // langsung jalan), jadi draw pertama saat init juga ikut terhitung.
            $('#laravel_crud').on('draw.dt', function() {
                renderCompanySummary($(this).DataTable());
            });
        })();
    </script>
@endpush
