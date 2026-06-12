@push('bottom')

    <style>

        /* Filter panel */
        .sponsor-filter-panel {
            padding: 14px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        .filter-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: #6c757d;
            margin-bottom: 4px;
        }

        /* Active filter chips */
        .active-filters {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
        }
        .filter-chip {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            transition: opacity .15s;
            color: #fff;
        }
        .filter-chip:hover { opacity: .75; color: #fff; text-decoration: none; }
        .chip-primary { background: #007bff; }
        .chip-success { background: #28a745; }
        .chip-info    { background: #17a2b8; }
        .chip-warning { background: #e0a800; color: #212529; }
        .chip-warning:hover { color: #212529; }
        .filter-chip-clear {
            font-size: 12px;
            color: #dc3545;
            text-decoration: none;
            margin-left: 2px;
        }
        .filter-chip-clear:hover { color: #a71d2a; text-decoration: none; }
    </style>

    <script>
        $(document).ready(function() {
            $('#laravel_crud').DataTable();
        });

        // Toggle sponsor active/inactive status
        $(document).ready(function() {
            $('.toggle-status').change(function() {
                let sponsorId = $(this).data('id');
                let status    = this.checked ? 'publish' : 'draft';
                $.ajax({
                    url: '/admin/sponsors/update-status/' + sponsorId,
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', status: status },
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Status updated successfully', 'Success', { positionClass: 'toast-top-right' });
                        } else {
                            toastr.error('Failed to update status', 'Error', { positionClass: 'toast-top-right' });
                        }
                    },
                    error: function() { console.error('Ajax error occurred'); }
                });
            });
        });

        // Delete sponsor
        $(document).ready(function() {
            $('.table').on('click', '.delete-sponsor', function() {
                let sponsorId = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This sponsor will be deleted!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'sponsors/' + sponsorId,
                            type: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('Deleted!', 'Sponsor deleted successfully.', 'success')
                                        .then(() => window.location.reload());
                                } else {
                                    Swal.fire('Failed!', 'An error occurred while deleting the sponsor.', 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Failed!', 'An error occurred while deleting the sponsor.', 'error');
                            }
                        });
                    }
                });
            });
        });

    </script>
@endpush
