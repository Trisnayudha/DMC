<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exhibitor List with DataTables</title>
    <!-- Include DataTables CSS from CDN -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <style>
        table {
            width: 100%;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }
    </style>
</head>

<body>

    <h1>Exhibitor List</h1>

    <!-- Element to display the comma-separated list of IDs -->
    <div>
        <strong>Exhibitor IDs: </strong>
        <span id="exhibitor-ids"></span> <!-- This will display the IDs -->
    </div>

    <!-- Element to display the total number of IDs -->
    <div>
        <strong>Total Exhibitor IDs: </strong>
        <span id="total-ids">0</span> <!-- This will display the total count -->
    </div>

    <!-- Button to download Excel -->
    <div>
        <button id="download-excel">Download Excel</button>
    </div>

    <table id="exhibitor-table" class="display">
        <thead>
            <tr>
                <th>#</th> <!-- Numbering column -->
                <th>ID</th> <!-- ID column -->
                <th>Name</th>
                <th>Country</th>
                <th>Pavilion</th>
                <th>Service Categories</th>
            </tr>
        </thead>
        <tbody>
            <!-- Exhibitor data will be populated by DataTables -->
        </tbody>
    </table>

    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Include DataTables JS from CDN -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <!-- Include SheetJS for Excel export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable with AJAX and custom settings
            const table = $('#exhibitor-table').DataTable({
                "processing": true,
                "serverSide": true, // Server-side processing for pagination
                "ajax": {
                    "url": "https://vexpo.iee-series.com/iee/pc/exhibitor/list", // API endpoint
                    "type": "POST",
                    "contentType": "application/json",
                    "data": function(d) {
                        return JSON.stringify({
                            pageNum: Math.ceil(d.start / d.length) + 1, // Calculate pageNum
                            pageSize: d.length // Use pageSize from DataTables
                        });
                    },
                    "headers": {
                        'x-access-token': 'your-token-here', // Replace with the actual token if needed
                        'login-device': 'PC',
                        'accept': 'application/json, text/plain, */*',
                        'origin': 'https://vexpo.iee-series.com',
                        'referer': 'https://vexpo.iee-series.com/'
                    },
                    "dataSrc": function(json) {
                        // Calculate the start index for numbering
                        let start = json.pageNum ? (json.pageNum - 1) * json.pageSize : 0;
                        let ids = [];
                        let data = json.rows.map(function(exhibitor, index) {
                            ids.push(exhibitor.id); // Collect the exhibitor IDs
                            return {
                                number: start + index +
                                    1, // Add numbering starting from the page's first item
                                id: exhibitor.id || 'N/A', // Add exhibitor ID
                                name: exhibitor.name || 'N/A',
                                country: exhibitor.country || 'N/A',
                                pavilion: exhibitor.pavilion || 'N/A',
                                serviceCategory: exhibitor.serviceCategory.join(', ') ||
                                    'N/A' // Join the serviceCategory array
                            };
                        });

                        // Display the comma-separated list of IDs
                        $('#exhibitor-ids').text(ids.join(', '));

                        // Display the total number of IDs
                        $('#total-ids').text(ids.length);

                        return data;
                    }
                },
                "pageLength": 1500, // Show 1500 entries per page
                "columns": [{
                        "data": "number"
                    }, // Column for numbering
                    {
                        "data": "id"
                    }, // Column for exhibitor ID
                    {
                        "data": "name"
                    },
                    {
                        "data": "country"
                    },
                    {
                        "data": "pavilion"
                    },
                    {
                        "data": "serviceCategory"
                    }
                ]
            });

            // Function to download Excel
            $('#download-excel').on('click', function() {
                var data = table.rows({
                    search: 'applied'
                }).data().toArray(); // Get all filtered rows
                var ws_data = [
                    ["#", "ID", "Name", "Country", "Pavilion", "Service Categories"]
                ]; // Header row

                data.forEach(function(row) {
                    ws_data.push([row.number, row.id, row.name, row.country, row.pavilion, row
                        .serviceCategory
                    ]);
                });

                // Create a new workbook and worksheet
                var wb = XLSX.utils.book_new();
                var ws = XLSX.utils.aoa_to_sheet(ws_data);

                // Append the worksheet to the workbook
                XLSX.utils.book_append_sheet(wb, ws, "Exhibitors");

                // Export the workbook to Excel
                XLSX.writeFile(wb, "Exhibitor_List.xlsx");
            });
        });
    </script>

</body>

</html>
