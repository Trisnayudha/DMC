{{-- Modal: detail satu Job Tier dari chart Job Tier — rincian role (sesuai definisi
     tier) & company category, plus daftar member Active di tier itu
     (JobTierDetailController). --}}
<div class="modal fade" id="jobTierModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-tie mr-1"></i>Job Tier — <span id="jt-modal-title">-</span>
                    <small class="text-muted d-block mt-1" style="font-size:12px;" id="jt-modal-roles"></small>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="jt-modal-loading" class="text-center text-muted py-5">
                    <span class="spinner-border spinner-border-sm mr-2"></span>Memuat...
                </div>
                <div id="jt-modal-content" style="display:none;">
                    <div class="reg-stat-row mb-3">
                        <div class="reg-stat-card reg-stat-total">
                            <div class="reg-stat-value" id="jt-modal-total">0</div>
                            <div class="reg-stat-label">Member Active</div>
                        </div>
                        <div class="reg-stat-card reg-stat-leads">
                            <div class="reg-stat-value" id="jt-modal-manual">0</div>
                            <div class="reg-stat-label">Tier Diset Manual</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="reg-section-label">Role <span class="font-weight-normal text-lowercase">(klik untuk filter)</span></div>
                        <div class="reg-chip-row" id="jt-modal-role-chips"></div>
                    </div>

                    <div class="mb-3">
                        <div class="reg-section-label">Company Category <span class="font-weight-normal text-lowercase">(klik untuk filter)</span></div>
                        <div class="reg-chip-row" id="jt-modal-category-chips"></div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-2" style="gap:8px;">
                        <h6 class="mb-0">
                            Daftar Member <small class="text-muted font-weight-normal" id="jt-modal-list-note"></small>
                            <a href="#" target="_blank" rel="noopener" class="small ml-2" id="jt-modal-members-link">
                                Buka di tabel Members <i class="fas fa-external-link-alt"></i>
                            </a>
                        </h6>
                        <div class="reg-search-wrap">
                            <i class="fas fa-search"></i>
                            <input type="text" id="jt-modal-search" class="form-control form-control-sm"
                                placeholder="Cari nama, email, job title, company...">
                        </div>
                    </div>

                    <div class="table-responsive reg-breakdown-table-wrap">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Job Title</th>
                                    <th>Role</th>
                                    <th>Company</th>
                                    <th>Company Category</th>
                                    <th class="text-center">Tier</th>
                                </tr>
                            </thead>
                            <tbody id="jt-modal-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('bottom')
    <script>
        (function() {
            var detailUrl = @json(route('users.job_tier_detail'));
            // null = semua.
            var state = { members: [], roleFilter: null, categoryFilter: null };
            var request = null;

            var esc = function(s) { return $('<span>').text(s == null ? '' : String(s)).html(); };
            var fmt = function(n) { return Number(n).toLocaleString('id-ID'); };
            var cell = function(v, bold) {
                return v
                    ? '<span class="cell-truncate' + (bold ? ' font-weight-bold' : '') + '" title="' + esc(v) + '">' + esc(v) + '</span>'
                    : '<span class="text-muted">—</span>';
            };
            var stateKey = function(group) { return group === 'role' ? 'roleFilter' : 'categoryFilter'; };

            function chip(group, value, label, count) {
                var $chip = $('<button type="button" class="reg-chip cc-filter-chip jt-filter-chip"></button>')
                    .attr('data-group', group)
                    .html(esc(label) + ' <span class="reg-chip-pct">' + fmt(count) + '</span>');
                if (value !== null) $chip.attr('data-value', value);
                return $chip;
            }

            function markActiveChips() {
                $('.jt-filter-chip').each(function() {
                    var value = this.hasAttribute('data-value') ? $(this).attr('data-value') : null;
                    $(this).toggleClass('active', value === state[stateKey($(this).attr('data-group'))]);
                });
            }

            function renderChips(res) {
                var $roles = $('#jt-modal-role-chips').empty().append(chip('role', null, 'Semua', res.total));
                res.roles_breakdown.forEach(function(r) { $roles.append(chip('role', r.name, r.name, r.count)); });

                var $cats = $('#jt-modal-category-chips').empty().append(chip('category', null, 'Semua', res.total));
                res.categories.forEach(function(c) { $cats.append(chip('category', c.name, c.name, c.count)); });

                markActiveChips();
            }

            function renderRows() {
                var q = $('#jt-modal-search').val().toLowerCase().trim();
                var rows = state.members.filter(function(m) {
                    if (state.roleFilter !== null && m.role !== state.roleFilter) return false;
                    if (state.categoryFilter !== null && m.category !== state.categoryFilter) return false;
                    if (!q) return true;
                    return [m.name, m.email, m.job_title, m.company].join(' ').toLowerCase().indexOf(q) !== -1;
                });

                var html = rows.map(function(m, i) {
                    return '<tr>'
                        + '<td class="text-muted">' + (i + 1) + '</td>'
                        + '<td>' + cell(m.name, true) + '</td>'
                        + '<td>' + cell(m.email) + '</td>'
                        + '<td>' + cell(m.job_title) + '</td>'
                        + '<td>' + cell(m.role) + '</td>'
                        + '<td>' + cell(m.company) + '</td>'
                        + '<td>' + cell(m.category) + '</td>'
                        + '<td class="text-center">' + (m.manual
                            ? '<span class="reg-status-badge" style="background:#b3720c;" title="Diset manual oleh admin">Manual</span>'
                            : '<span class="reg-status-badge" style="background:#adb5bd;" title="Otomatis dari job title">Auto</span>') + '</td>'
                        + '</tr>';
                }).join('');

                $('#jt-modal-tbody').html(html || '<tr><td colspan="8" class="text-center text-muted py-4">Tidak ada member yang cocok.</td></tr>');
                $('#jt-modal-list-note').text('(' + fmt(rows.length) + ' dari ' + fmt(state.members.length) + ')');
            }

            /** params: { tier: 1-4, role: '<nama role>' | null } */
            window.openJobTierDetail = function(params) {
                $('#jt-modal-title').text('T' + params.tier);
                $('#jt-modal-roles').text('');
                $('#jt-modal-content').hide();
                $('#jt-modal-loading').html('<span class="spinner-border spinner-border-sm mr-2"></span>Memuat...').show();
                $('#jt-modal-search').val('');
                state.roleFilter = params.role || null;
                state.categoryFilter = null;
                $('#jobTierModal').modal('show');

                if (request) request.abort();
                request = $.getJSON(detailUrl, { tier: params.tier })
                    .done(function(res) {
                        state.members = res.members;
                        $('#jt-modal-title').text(res.label);
                        $('#jt-modal-roles').text(res.roles + ' · ' + res.action);
                        $('#jt-modal-total').text(fmt(res.total));
                        $('#jt-modal-manual').text(fmt(res.manual));
                        $('#jt-modal-members-link').attr('href', res.members_url);
                        renderChips(res);
                        renderRows();
                        $('#jt-modal-loading').hide();
                        $('#jt-modal-content').show();
                    })
                    .fail(function(xhr, status) {
                        if (status === 'abort') return;
                        $('#jt-modal-loading').text('Gagal memuat detail job tier.');
                    });
            };

            $('#jobTierModal').on('click', '.jt-filter-chip', function() {
                state[stateKey($(this).attr('data-group'))] = this.hasAttribute('data-value') ? $(this).attr('data-value') : null;
                markActiveChips();
                renderRows();
            });

            $('#jt-modal-search').on('input', renderRows);
        })();
    </script>
@endpush
