<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>API Scraper UI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container py-5">
        <h2 class="mb-4">API Scraper UI</h2>

        <!-- Input Controls -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label for="baseUrl" class="form-label">Base URL</label>
                <input type="text" id="baseUrl" class="form-control"
                    value="https://api.bm-dticx.com/v1/userlain?filter=&p=0&where=">
            </div>
            <div class="col-md-2">
                <label for="startPage" class="form-label">Start Page (p)</label>
                <input type="number" id="startPage" class="form-control" value="0" min="0">
            </div>
            <div class="col-md-2">
                <label for="endPage" class="form-label">End Page (p)</label>
                <input type="number" id="endPage" class="form-control" value="10" min="0">
            </div>
            <div class="col-md-2">
                <label for="intervalMs" class="form-label">Interval (ms)</label>
                <input type="number" id="intervalMs" class="form-control" value="1000" min="100">
            </div>
            <div class="col-md-2 d-grid">
                <button id="startScrapeBtn" class="btn btn-warning mt-4">Start Scraping</button>
            </div>
        </div>

        <!-- Alert Placeholder -->
        <div id="scrapeAlertContainer"></div>

        <!-- Pagination & Save Controls -->
        <div class="row mb-3" id="paginationControls" style="display:none;">
            <div class="col-md-4 d-flex gap-2">
                <button id="prevBtn" class="btn btn-outline-secondary">Prev</button>
                <button id="nextBtn" class="btn btn-outline-secondary">Next</button>
                <button id="saveBtn" class="btn btn-success ms-auto" disabled>Save to DB</button>
            </div>
        </div>

        <!-- Results Table -->
        <div class="table-responsive">
            <table class="table table-striped" id="resultTable">
                <thead>
                    <tr id="tableHead"></tr>
                </thead>
                <tbody id="tableBody"></tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const baseUrlInput = document.getElementById('baseUrl');
            const startPageInput = document.getElementById('startPage');
            const endPageInput = document.getElementById('endPage');
            const intervalInput = document.getElementById('intervalMs');
            const startBtn = document.getElementById('startScrapeBtn');
            const alertHolder = document.getElementById('scrapeAlertContainer');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const saveBtn = document.getElementById('saveBtn');
            const pagControls = document.getElementById('paginationControls');
            const tableHead = document.getElementById('tableHead');
            const tableBody = document.getElementById('tableBody');

            let latestData = [];
            let currentPage = parseInt(startPageInput.value, 10);

            function delay(ms) {
                return new Promise(r => setTimeout(r, ms));
            }

            function renderTable(data) {
                tableHead.innerHTML = '';
                tableBody.innerHTML = '';
                if (!Array.isArray(data) || data.length === 0) {
                    tableHead.innerHTML = '<th>No data</th>';
                    return;
                }
                const keys = Object.keys(data[0]);
                keys.forEach(key => {
                    const th = document.createElement('th');
                    th.textContent = key;
                    tableHead.append(th);
                });
                data.forEach(item => {
                    const tr = document.createElement('tr');
                    keys.forEach(key => {
                        const td = document.createElement('td');
                        td.textContent = item[key] ?? '';
                        tr.append(td);
                    });
                    tableBody.append(tr);
                });
            }

            async function fetchData(page) {
                const urlBase = baseUrlInput.value.split('?')[0];
                const params = new URLSearchParams(baseUrlInput.value.split('?')[1]);
                params.set('p', page);
                const url = `${urlBase}?${params.toString()}`;
                startBtn.disabled = true;
                startBtn.textContent = `Loading ${page}…`;

                const response = await fetch(url, {
                    headers: {
                        'Authorization': `Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE3ODUzNzk5OTUsInVzZXJfaWQiOjg3NzJ9.E7endpDTfE6DI985LV4o9v8nrTnCJySDaY_-2uVsjzM`,
                        'Accept': 'application/json'
                    }
                });
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                const json = await response.json();
                latestData = json;
                renderTable(json);
                currentPage = page;
                pagControls.style.display = 'flex';
                saveBtn.disabled = false;
            }

            async function startScraping() {
                const start = parseInt(startPageInput.value, 10);
                const end = parseInt(endPageInput.value, 10);
                const interval = parseInt(intervalInput.value, 10);
                let successCount = 0,
                    failCount = 0;

                startBtn.disabled = true;
                alertHolder.innerHTML = '';
                for (let p = start; p <= end; p++) {
                    try {
                        await fetchData(p);
                        successCount++;
                    } catch (e) {
                        console.error('Error page', p, e);
                        failCount++;
                    }
                    await delay(interval);
                }

                startBtn.disabled = false;
                startBtn.textContent = 'Start Scraping';

                alertHolder.innerHTML = `
      <div class="alert alert-info alert-dismissible fade show" role="alert">
        <h4 class="alert-heading">Scraping Selesai!</h4>
        <p>Berhasil: <strong>${successCount}</strong> page(s)<br>
           Gagal: <strong>${failCount}</strong> page(s)</p>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>`;
            }

            startBtn.addEventListener('click', startScraping);
            prevBtn.addEventListener('click', () => {
                if (currentPage > 0) fetchData(currentPage - 1);
            });
            nextBtn.addEventListener('click', () => fetchData(currentPage + 1));

            saveBtn.addEventListener('click', async () => {
                saveBtn.disabled = true;
                const res = await fetch("/api-ui/save", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        rows: latestData
                    })
                });
                const result = await res.json();
                alertHolder.innerHTML = `
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        Berhasil disimpan: <strong>${result.success_count}</strong> gagal: <strong>${result.fail_count}</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>`;
                saveBtn.disabled = false;
            });
        });
    </script>
</body>

</html>
