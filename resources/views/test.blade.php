<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pengguna (Server-Side, Bootstrap, Export Excel)</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">

    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.dataTables.min.css">
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.bootstrap5.min.css">

    <style>
        body {
            background-color: #f4f4f4;
        }

        .container {
            margin-top: 30px;
            margin-bottom: 30px;
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        #totalDataCount {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 15px;
            color: #343a40;
        }

        #loading,
        #error {
            text-align: center;
            padding: 20px;
            margin-top: 20px;
        }

        #loading {
            color: #0d6efd;
            /* Bootstrap primary color */
        }

        #error {
            color: #dc3545;
            /* Bootstrap danger color */
        }

        .dt-buttons .btn {
            margin-right: 5px;
            margin-bottom: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="mb-4 text-center">Daftar Detail Pengguna</h1>

        <div id="totalDataCount"></div>
        <div id="loading" class="alert alert-info" role="alert">
            Memuat data... <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        </div>
        <div id="error" class="alert alert-danger" role="alert" style="display: none;">
            Gagal memuat data. Terjadi kesalahan saat memuat data dari server.
            @if (session('error'))
                <br>{{ session('error') }}
            @endif
        </div>

        <div class="table-responsive">
            <table id="userDataTable" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eEGFN7ZVAxhN+d/Lq" crossorigin="anonymous">
    </script>

    <script type="text/javascript" src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>

    <script type="text/javascript" src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.bootstrap5.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.print.min.js"></script>


    <script>
        const ajaxDataUrl = "{{ url('test/data') }}";
        const exportExcelUrl = "{{ route('dti.export') }}"; // URL untuk ekspor Excel

        $(document).ready(function() {
            const loadingDiv = $('#loading');
            const errorDiv = $('#error');
            const totalDataCountDiv = $('#totalDataCount');

            const definedColumns = [{
                    data: null,
                    title: 'No.',
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                    orderable: false,
                    searchable: false,
                    width: '5%',
                    className: 'text-center'
                },
                {
                    data: 'Username',
                    title: 'Username'
                },
                {
                    data: 'Nama',
                    title: 'Nama'
                },
                {
                    data: 'Email',
                    title: 'Email'
                },
                {
                    data: 'Telepon',
                    title: 'Telepon'
                },
                {
                    data: 'LastName',
                    title: 'Last Name'
                },
                {
                    data: 'RegAs',
                    title: 'Registered As'
                },
                {
                    data: 'JobTitle',
                    title: 'Job Title'
                },
                {
                    data: 'JobLevel',
                    title: 'Job Level'
                },
                {
                    data: 'JobFunction',
                    title: 'Job Function'
                },
                {
                    data: 'Company',
                    title: 'Company'
                },
                {
                    data: 'Country',
                    title: 'Country'
                },
                {
                    data: 'Photo',
                    title: 'Photo',
                    render: function(data, type, row) {
                        return data ? '<a href="' + data + '" target="_blank">Lihat Foto</a>' : '-';
                    },
                    orderable: false, // Tidak bisa diurutkan berdasarkan link
                    searchable: false // Tidak bisa dicari berdasarkan link
                },
                {
                    data: 'Linkedin',
                    title: 'Linkedin',
                    render: function(data, type, row) {
                        return data ? '<a href="' + data + '" target="_blank">Profil LinkedIn</a>' : '-';
                    },
                    orderable: false,
                    searchable: false
                }
            ];

            const tableHead = $('#userDataTable thead tr');
            definedColumns.forEach(col => {
                const th = $('<th>').text(col.title);
                tableHead.append(th);
            });

            $('#userDataTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: ajaxDataUrl,
                    type: 'GET',
                    error: function(xhr, error, thrown) {
                        loadingDiv.hide();
                        errorDiv.show();
                        console.error("AJAX error: ", thrown, xhr.responseText);
                    }
                },
                columns: definedColumns,
                language: {
                    url: '//cdn.datatables.net/plug-ins/2.0.8/i18n/id.json'
                },
                // `dom` sekarang perlu menampung tombol kustom 'B'
                // Pastikan 'B' ada di posisi yang Anda inginkan (misalnya 'lBfrtip')
                dom: 'lBfrtip',
                buttons: [{
                        // Ini adalah tombol kustom yang akan memicu ekspor dari backend
                        text: 'Export Excel (dtiExport)',
                        className: 'btn btn-success btn-sm',
                        action: function(e, dt, node, config) {
                            // Cukup redirect browser ke URL endpoint ekspor Laravel Anda
                            window.location = exportExcelUrl;
                        }
                    },
                    // Anda bisa tetap punya tombol DataTables bawaan lainnya jika mau, contoh:
                    // {
                    //     extend: 'print',
                    //     text: 'Cetak Tampilan',
                    //     className: 'btn btn-secondary btn-sm',
                    //     exportOptions: {
                    //         columns: ':visible:not(:eq(0))' // Abaikan kolom 'No.'
                    //     }
                    // }
                ],
                initComplete: function(settings, json) {
                    loadingDiv.hide();
                    totalDataCountDiv.text(`Total Data Pengguna: ${json.recordsTotal || 0}`);
                },
                drawCallback: function(settings) {
                    const api = this.api();
                    totalDataCountDiv.text(`Total Data Pengguna: ${api.ajax.json().recordsTotal || 0}`);
                }
            });
        });
    </script>
</body>

</html>
