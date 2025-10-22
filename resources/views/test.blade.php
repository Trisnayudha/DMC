<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Swapcard — People</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- DataTables + Buttons --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <style>
        body {
            background: #f8f9fa
        }

        .avatar {
            width: 38px;
            height: 38px;
            object-fit: cover;
            border-radius: 50%
        }

        .dt-buttons .btn {
            margin-right: .5rem
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0">Swapcard — People</h1>
            <span class="badge text-bg-secondary" id="totalBadge">Total: 0</span>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="alert alert-info py-2 px-3 mb-3">
                    Sumber data: <code>/json/ct/swapcard_people.json</code>
                </div>

                <div class="table-responsive">
                    <table id="peopleTable" class="table table-striped table-hover table-bordered w-100">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Photo</th>
                                <th>Full Name</th>
                                <th>Job Title</th>
                                <th>Organization</th>
                                <th>Presence</th>
                                <th>Connection</th>
                                <th>User ID</th>
                                <th>Person ID</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    {{-- JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <script>
        (async function() {
            // sesuaikan path file kamu (lihat screenshot: public/json/ct/swapcard_people.json)
            const jsonUrl = "{{ asset('json/ct/swapcard_people.json') }}";

            let data = [];
            try {
                const res = await fetch(jsonUrl, {
                    cache: 'no-store'
                });
                data = await res.json();
            } catch (e) {
                console.error('Failed to load JSON:', e);
            }

            // badge total
            document.getElementById('totalBadge').textContent = 'Total: ' + (data?.length || 0);

            // helper badge
            const badge = (txt, type) => `<span class="badge text-bg-${type}">${txt}</span>`;

            $('#peopleTable').DataTable({
                data,
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ],
                order: [
                    [2, 'asc']
                ],
                dom: "<'row mb-2'<'col-md-6'B><'col-md-6'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row mt-2'<'col-md-5'i><'col-md-7'p>>",
                buttons: [{
                        extend: 'copyHtml5',
                        className: 'btn btn-outline-secondary btn-sm',
                        exportOptions: {
                            columns: [2, 3, 4, 5, 6, 7, 8]
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        className: 'btn btn-outline-primary btn-sm',
                        title: 'swapcard_people',
                        exportOptions: {
                            columns: [2, 3, 4, 5, 6, 7, 8]
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        className: 'btn btn-outline-success btn-sm',
                        title: 'swapcard_people',
                        exportOptions: {
                            columns: [2, 3, 4, 5, 6, 7, 8]
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-outline-danger btn-sm',
                        title: 'Swapcard — People',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: [2, 3, 4, 5, 6, 7, 8]
                        }
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-outline-dark btn-sm',
                        title: 'Swapcard — People',
                        exportOptions: {
                            columns: [2, 3, 4, 5, 6, 7, 8]
                        }
                    },
                ],
                columns: [{ // #
                        data: null,
                        width: '40px',
                        className: 'text-muted',
                        render: (d, t, r, m) => m.row + 1
                    },
                    { // photo
                        data: 'photoUrl',
                        orderable: false,
                        searchable: false,
                        width: '60px',
                        render: (url) => {
                            if (url)
                        return `<img src="${url}" class="avatar" onerror="this.src='https://via.placeholder.com/38?text=%20'">`;
                            return `<img src="https://via.placeholder.com/38?text=%20" class="avatar">`;
                        }
                    },
                    {
                        data: 'fullName',
                        render: (d) => d || '-'
                    },
                    {
                        data: 'jobTitle',
                        render: (d) => d || '-'
                    },
                    {
                        data: 'organization',
                        render: (d) => d || '-'
                    },
                    { // presence -> badge
                        data: 'presenceStatus',
                        render: (d) => {
                            if (!d) return '-';
                            const up = (d + '').toUpperCase();
                            return badge(up, up === 'ONLINE' ? 'success' : 'secondary');
                        }
                    },
                    { // connection -> badge
                        data: 'connectionStatus',
                        render: (d) => {
                            if (!d) return '-';
                            const up = (d + '').toUpperCase();
                            const type = up.includes('CONNECTED') ? 'primary' : 'warning';
                            return badge(up.replace(/_/g, ' '), type);
                        }
                    },
                    {
                        data: 'userId',
                        visible: false
                    }, // ikut ekspor
                    {
                        data: 'id',
                        visible: false
                    } // ikut ekspor
                ]
            });
        })();
    </script>
</body>

</html>
