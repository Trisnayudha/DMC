<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Exhibitors – Mining Indonesia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container py-4">

        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            <h4 class="mb-0">Exhibitors</h4>
            <span id="totalCount" class="text-muted">(loading...)</span>
            <div class="ms-auto d-flex flex-wrap gap-2">
                <input id="q" type="text" class="form-control" placeholder="Search id/name/pavilion..."
                    style="min-width:260px">
                <select id="perPage" class="form-select" style="width:120px">
                    <option value="10">10 / page</option>
                    <option value="20" selected>20 / page</option>
                    <option value="50">50 / page</option>
                    <option value="100">100 / page</option>
                </select>
                <div class="btn-group">
                    <button id="btnImportPage" class="btn btn-primary">Import Page</button>
                    <button id="btnImportAll" class="btn btn-outline-primary">Import All (Filtered)</button>
                </div>
            </div>
        </div>

        <div class="table-responsive position-relative">
            <div id="loader" class="position-absolute top-0 start-0 w-100 h-100 d-none"
                style="backdrop-filter: blur(1px);">
                <div class="d-flex justify-content-center align-items-center h-100">
                    <div class="spinner-border" role="status" aria-label="Loading"></div>
                </div>
            </div>

            <table class="table table-hover align-middle">
                <thead class="table-light position-sticky top-0">
                    <tr>
                        <th style="width:100px">ID</th>
                        <th>Name</th>
                        <th style="width:260px">Pavilion</th>
                        <th style="width:220px">Actions</th>
                    </tr>
                </thead>
                <tbody id="tbody">
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <nav class="mt-3">
            <ul id="pagination" class="pagination"></ul>
        </nav>

        <!-- Toast -->
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080">
            <div id="toast" class="toast align-items-center" role="alert" data-bs-delay="2000">
                <div class="d-flex">
                    <div id="toast-body" class="toast-body">Ready.</div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        (() => {
            const API_LIST = '/mining-indo';
            const API_IMPORT = '/exhibitors/import';
            const API_IMPORTB = '/exhibitors/import-batch';

            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            if (token) axios.defaults.headers.common['X-CSRF-TOKEN'] = token;

            const els = {
                tbody: document.getElementById('tbody'),
                total: document.getElementById('totalCount'),
                pagination: document.getElementById('pagination'),
                perPage: document.getElementById('perPage'),
                q: document.getElementById('q'),
                loader: document.getElementById('loader'),
                btnImportPage: document.getElementById('btnImportPage'),
                btnImportAll: document.getElementById('btnImportAll'),
                toast: new bootstrap.Toast(document.getElementById('toast')),
                toastBody: document.getElementById('toast-body'),
            };

            const state = {
                page: 1,
                per_page: 20,
                q: ''
            };
            let lastRows = []; // cache rows of current page

            const debounce = (fn, wait = 350) => {
                let t;
                return (...a) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn(...a), wait);
                }
            };

            function setLoading(on) {
                els.loader.classList.toggle('d-none', !on);
            }

            function notify(msg) {
                els.toastBody.textContent = msg;
                els.toast.show();
            }

            function actionButtons(row) {
                const externalLink = `https://vexpo.iee-series.com/iee/pc/exhibitor/${row.id}`;
                return `
      <div class="btn-group">
        <a class="btn btn-sm btn-outline-secondary" href="${externalLink}" target="_blank" rel="noopener">Open</a>
        <button class="btn btn-sm btn-primary" onclick="window.__importRow(${row.id})">Import</button>
      </div>
    `;
            }

            function renderRows(rows) {
                lastRows = rows.slice();
                if (!rows.length) {
                    els.tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4">No data</td></tr>`;
                    return;
                }
                els.tbody.innerHTML = rows.map(r => `
      <tr>
        <td><code>${r.id}</code></td>
        <td>${r.name ?? '-'}</td>
        <td>${r.pavilion ?? '-'}</td>
        <td>${actionButtons(r)}</td>
      </tr>
    `).join('');
            }

            function renderPagination(page, total_page) {
                const ul = els.pagination;
                ul.innerHTML = '';
                if (total_page <= 1) return;

                const add = (label, p, disabled = false, active = false) => {
                    const li = document.createElement('li');
                    li.className = `page-item ${disabled?'disabled':''} ${active?'active':''}`;
                    const a = document.createElement('a');
                    a.className = 'page-link';
                    a.href = '#';
                    a.textContent = label;
                    a.onclick = e => {
                        e.preventDefault();
                        if (!disabled && state.page !== p) {
                            state.page = p;
                            fetchList();
                        }
                    };
                    li.appendChild(a);
                    ul.appendChild(li);
                };

                add('«', Math.max(1, page - 1), page === 1);
                const w = 2,
                    start = Math.max(1, page - w),
                    end = Math.min(total_page, page + w);
                for (let i = start; i <= end; i++) add(String(i), i, false, i === page);
                add('»', Math.min(total_page, page + 1), page === total_page);
            }

            async function fetchList() {
                setLoading(true);
                try {
                    const {
                        data
                    } = await axios.get(API_LIST, {
                        params: state
                    });
                    renderRows(data.rows || []);
                    els.total.textContent = `Total: ${data.total} | Page ${data.page}/${data.total_page || 1}`;
                    renderPagination(data.page, data.total_page || 1);
                } catch (e) {
                    console.error(e);
                    els.tbody.innerHTML =
                        `<tr><td colspan="4" class="text-danger text-center">Failed to load</td></tr>`;
                } finally {
                    setLoading(false);
                }
            }

            // Single import exposed
            window.__importRow = async function(id) {
                try {
                    setLoading(true);
                    const resp = await axios.post(API_IMPORT, {
                        id
                    });
                    if (resp.data?.ok) notify(`Imported ID ${id} ✅`);
                    else notify(`Import failed for ${id}`);
                } catch (e) {
                    console.error(e);
                    notify(`Error importing ${id}`);
                } finally {
                    setLoading(false);
                }
            }

            // Import Page (all ids on current page)
            async function importPage() {
                const ids = lastRows.map(r => r.id).filter(Boolean);
                if (!ids.length) return notify('No rows to import on this page.');
                await importBatch(ids, 'page');
            }

            // Import All (Filtered) — fetch all pages to collect IDs based on current filter (q)
            async function importAllFiltered() {
                setLoading(true);
                try {
                    const ids = [];
                    // step 1: get first page to know totals
                    const first = await axios.get(API_LIST, {
                        params: {
                            page: 1,
                            per_page: state.per_page,
                            q: state.q
                        }
                    });
                    const total = first.data.total || 0;
                    const per = Math.min(200, Math.max(1, state.per_page)); // cap per request
                    const totalPages = Math.max(1, Math.ceil(total / per));

                    // collect ids page by page to avoid giant response
                    for (let p = 1; p <= totalPages; p++) {
                        const {
                            data
                        } = await axios.get(API_LIST, {
                            params: {
                                page: p,
                                per_page: per,
                                q: state.q
                            }
                        });
                        (data.rows || []).forEach(r => {
                            if (r.id) ids.push(r.id);
                        });
                    }

                    if (!ids.length) {
                        notify('No rows match current filter.');
                        return;
                    }

                    await importBatch(ids, 'all');
                } catch (e) {
                    console.error(e);
                    notify('Failed collecting all filtered IDs.');
                } finally {
                    setLoading(false);
                }
            }

            // Batch importer with chunking
            async function importBatch(allIds, label) {
                // optional: chunk to avoid too large payloads
                const chunkSize = 50;
                const chunks = [];
                for (let i = 0; i < allIds.length; i += chunkSize) chunks.push(allIds.slice(i, i + chunkSize));

                let imported = 0,
                    failed = 0;

                setLoading(true);
                try {
                    for (let i = 0; i < chunks.length; i++) {
                        const {
                            data
                        } = await axios.post(API_IMPORTB, {
                            ids: chunks[i]
                        });
                        imported += data?.imported || 0;
                        failed += data?.failed || 0;
                        notify(
                            `Importing ${label}: batch ${i+1}/${chunks.length} ✓ imported:${imported} ✗ failed:${failed}`
                            );
                    }
                    notify(`Done importing ${label}. Total ✓ ${imported}, ✗ ${failed}.`);
                } catch (e) {
                    console.error(e);
                    notify('Batch import error.');
                } finally {
                    setLoading(false);
                }
            }

            // events
            const debounceInput = (fn, ms = 350) => {
                let t;
                return (...a) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn(...a), ms);
                }
            };
            els.perPage.addEventListener('change', () => {
                state.per_page = parseInt(els.perPage.value, 10) || 20;
                state.page = 1;
                fetchList();
            });
            els.q.addEventListener('input', debounceInput(() => {
                state.q = els.q.value.trim();
                state.page = 1;
                fetchList();
            }, 300));
            els.btnImportPage.addEventListener('click', importPage);
            els.btnImportAll.addEventListener('click', importAllFiltered);

            fetchList();
        })();
    </script>
</body>

</html>
