{{-- Modal: detail satu grup Company Category Tier (atau satu kategori) dari chart
     Company Category Tier — category detail, subcategory & daftar company dari
     Company Database. --}}
<div class="modal fade" id="companyCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-building mr-1"></i><span id="cc-modal-kind">Company Category</span> — <span id="cc-modal-category">-</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="cc-modal-loading" class="text-center text-muted py-5">
                    <span class="spinner-border spinner-border-sm mr-2"></span>Memuat...
                </div>
                <div id="cc-modal-content" style="display:none;">
                    <div class="reg-stat-row mb-3">
                        <div class="reg-stat-card reg-stat-total">
                            <div class="reg-stat-value" id="cc-modal-total">0</div>
                            <div class="reg-stat-label">Total Company</div>
                        </div>
                        <div class="reg-stat-card" style="background:#eaf7ee;">
                            <div class="reg-stat-value" id="cc-modal-verified" style="color:#0a7a0a;">0</div>
                            <div class="reg-stat-label">Verified</div>
                        </div>
                    </div>

                    <div id="cc-modal-category-section" class="mb-3" style="display:none;">
                        <div class="reg-section-label">Category Detail <span class="font-weight-normal text-lowercase">(klik untuk filter)</span></div>
                        <div class="reg-chip-row" id="cc-modal-category-chips"></div>
                    </div>

                    <div id="cc-modal-subcategory-section" class="mb-3" style="display:none;">
                        <div class="reg-section-label">Subcategory <span class="font-weight-normal text-lowercase">(klik untuk filter; satu company bisa punya lebih dari satu)</span></div>
                        <div class="reg-chip-row" id="cc-modal-subcategory-chips"></div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-2" style="gap:8px;">
                        <h6 class="mb-0">Daftar Company <small class="text-muted font-weight-normal" id="cc-modal-list-note"></small></h6>
                        <div class="reg-search-wrap">
                            <i class="fas fa-search"></i>
                            <input type="text" id="cc-modal-search" class="form-control form-control-sm"
                                placeholder="Cari company, kota, negara...">
                        </div>
                    </div>

                    <div class="table-responsive reg-breakdown-table-wrap">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th>Company</th>
                                    <th>Category Detail</th>
                                    <th>Subcategory</th>
                                    <th>Kota / Negara</th>
                                    <th class="text-center">Record</th>
                                    <th class="text-center">Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="cc-modal-tbody"></tbody>
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
            var detailUrl = @json(route('admin.company_database.category_detail'));
            var state = { companies: [], subcategoryNames: {}, categoryFilter: '', subcategoryFilter: '' };
            var request = null;

            var esc = function(s) { return $('<span>').text(s == null ? '' : String(s)).html(); };
            var fmt = function(n) { return Number(n).toLocaleString('id-ID'); };

            function chip(group, value, label, count, active) {
                return $('<button type="button" class="reg-chip cc-filter-chip"></button>')
                    .attr({ 'data-group': group, 'data-value': value })
                    .toggleClass('active', active)
                    .html(esc(label) + ' <span class="reg-chip-pct">' + fmt(count) + '</span>');
            }

            function renderChips(res) {
                // Category Detail — hanya berguna kalau isinya lebih dari satu kategori (mode grup).
                var $cat = $('#cc-modal-category-chips').empty();
                if (res.category_breakdown.length > 1) {
                    $cat.append(chip('category', '', 'Semua', res.total, true));
                    res.category_breakdown.forEach(function(c) {
                        $cat.append(chip('category', c.name, c.name, c.count, false));
                    });
                    $('#cc-modal-category-section').show();
                } else {
                    $('#cc-modal-category-section').hide();
                }

                var $sub = $('#cc-modal-subcategory-chips').empty();
                if (res.subcategories.length) {
                    $sub.append(chip('subcategory', '', 'Semua', res.total, true));
                    res.subcategories
                        .slice()
                        .sort(function(a, b) { return b.count - a.count; })
                        .forEach(function(s) { $sub.append(chip('subcategory', String(s.id), s.name, s.count, false)); });
                    if (res.without_subcategory > 0) {
                        $sub.append(chip('subcategory', 'none', 'Tanpa subcategory', res.without_subcategory, false));
                    }
                    $('#cc-modal-subcategory-section').show();
                } else {
                    $('#cc-modal-subcategory-section').hide();
                }
            }

            function renderRows() {
                var q = $('#cc-modal-search').val().toLowerCase().trim();
                var sub = state.subcategoryFilter;
                var rows = state.companies.filter(function(c) {
                    if (state.categoryFilter && c.category !== state.categoryFilter) return false;
                    if (sub === 'none' && c.subcategory_ids.length) return false;
                    if (sub && sub !== 'none' && c.subcategory_ids.indexOf(Number(sub)) === -1) return false;
                    if (!q) return true;
                    return [c.company_name, c.city, c.country].join(' ').toLowerCase().indexOf(q) !== -1;
                });

                var html = rows.map(function(c, i) {
                    var subs = c.subcategory_ids.map(function(id) { return state.subcategoryNames[id]; }).filter(Boolean);
                    var place = [c.city, c.country].filter(function(v) { return v && v.trim() !== '-'; }).join(', ');
                    return '<tr>'
                        + '<td class="text-muted">' + (i + 1) + '</td>'
                        + '<td><span class="cell-truncate font-weight-bold" title="' + esc(c.company_name) + '">' + esc(c.company_name) + '</span></td>'
                        + '<td><span class="cell-truncate" title="' + esc(c.category) + '">' + esc(c.category) + '</span></td>'
                        + '<td>' + (subs.length ? subs.map(function(s) { return '<span class="badge badge-light mr-1">' + esc(s) + '</span>'; }).join('') : '<span class="text-muted">—</span>') + '</td>'
                        + '<td><span class="cell-truncate" title="' + esc(place) + '">' + (place ? esc(place) : '<span class="text-muted">—</span>') + '</span></td>'
                        + '<td class="text-center">' + fmt(c.total_records) + '</td>'
                        + '<td class="text-center">' + (c.is_verified
                            ? '<span class="reg-status-badge" style="background:#0ca30c;">Verified</span>'
                            : '<span class="reg-status-badge" style="background:#adb5bd;">Unverified</span>') + '</td>'
                        + '<td class="text-right"><a href="' + esc(c.database_url) + '" target="_blank" rel="noopener" title="Buka di Company Database"><i class="fas fa-external-link-alt"></i></a></td>'
                        + '</tr>';
                }).join('');

                $('#cc-modal-tbody').html(html || '<tr><td colspan="8" class="text-center text-muted py-4">Tidak ada company yang cocok.</td></tr>');
                $('#cc-modal-list-note').text('(' + fmt(rows.length) + ' dari ' + fmt(state.companies.length) + ')');
            }

            /** params: { tier: '<nama grup>' } atau { category: '<nama kategori>' } */
            window.openCompanyCategoryDetail = function(params) {
                $('#cc-modal-kind').text(params.tier ? 'Company Category Tier' : 'Company Category');
                $('#cc-modal-category').text(params.tier || params.category);
                $('#cc-modal-content').hide();
                $('#cc-modal-loading').html('<span class="spinner-border spinner-border-sm mr-2"></span>Memuat...').show();
                $('#cc-modal-search').val('');
                state.categoryFilter = '';
                state.subcategoryFilter = '';
                $('#companyCategoryModal').modal('show');

                if (request) request.abort();
                request = $.getJSON(detailUrl, params)
                    .done(function(res) {
                        state.companies = res.companies;
                        state.subcategoryNames = {};
                        res.subcategories.forEach(function(s) { state.subcategoryNames[s.id] = s.name; });

                        $('#cc-modal-total').text(fmt(res.total));
                        $('#cc-modal-verified').text(fmt(res.verified));
                        renderChips(res);
                        renderRows();
                        $('#cc-modal-loading').hide();
                        $('#cc-modal-content').show();
                    })
                    .fail(function(xhr, status) {
                        if (status === 'abort') return;
                        $('#cc-modal-loading').text('Gagal memuat detail kategori.');
                    });
            };

            $(document).on('click', '.cc-filter-chip', function() {
                var group = $(this).attr('data-group');
                state[group === 'category' ? 'categoryFilter' : 'subcategoryFilter'] = String($(this).attr('data-value'));
                $('.cc-filter-chip[data-group="' + group + '"]').removeClass('active');
                $(this).addClass('active');
                renderRows();
            });

            $('#cc-modal-search').on('input', renderRows);
        })();
    </script>
@endpush
