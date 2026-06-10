@push('bottom')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <style>
        .action-icon-btn {
            width: 30px;
            height: 30px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

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

        function fetchKmkRate() {
            $('#modalKmkRate').val('').attr('placeholder', 'Loading...');
            $.get('/admin/sponsors/kmk-rate', function(res) {
                if (res.success && res.rate) {
                    $('#modalKmkRate').val(res.rate);
                    autoFillIdr();
                } else {
                    $('#modalKmkRate').attr('placeholder', 'Gagal fetch');
                }
            }).fail(function() {
                $('#modalKmkRate').attr('placeholder', 'Gagal fetch');
            });
        }

        function setIdrValue(raw) {
            let num = Math.round(parseFloat(raw));
            if (!isNaN(num) && num > 0) {
                $('#modalAmountIdr').val(num);
                $('#modalAmountIdrDisplay').val(num.toLocaleString('id-ID'));
            } else {
                $('#modalAmountIdr').val('');
                $('#modalAmountIdrDisplay').val('');
            }
        }

        function autoFillIdr() {
            let usd = parseFloat($('#modalAmountUsd').val());
            let rate = parseFloat($('#modalKmkRate').val());
            if (!isNaN(usd) && usd > 0 && !isNaN(rate) && rate > 0) {
                setIdrValue(usd * rate);
            }
        }

        $('#modalAmountIdrDisplay').on('input', function() {
            let raw = $(this).val().replace(/\./g, '').replace(/,/g, '');
            $('#modalAmountIdr').val(raw);
        }).on('blur', function() {
            let raw = $(this).val().replace(/\./g, '').replace(/,/g, '');
            let num = parseInt(raw, 10);
            if (!isNaN(num) && num > 0) {
                $(this).val(num.toLocaleString('id-ID'));
                $('#modalAmountIdr').val(num);
            }
        });

        $('#modalAmountUsd').on('input', autoFillIdr);
        $('#modalKmkRate').on('change', autoFillIdr);
        $('#btnRefreshKmkRate').on('click', fetchKmkRate);

        // Open Update Contract modal
        $(document).on('click', '.update-contract-btn', function(e) {
            e.preventDefault();
            $('#modalSponsorId').val($(this).data('sponsor-id'));
            $('#modalContractStart').val($(this).data('contract-start'));
            $('#modalContractEnd').val($(this).data('contract-end'));
            $('#modalPackage').val($(this).data('package') || 'silver');
            $('#modalRenewalType').val('renewal');
            $('#modalAmountUsd').val('');
            $('#modalAmountIdr').val('');
            $('#modalAmountIdrDisplay').val('');
            $('#modalNotes').val('');
            $('#updateContractModal').modal('show');
            fetchKmkRate();
        });

        // Submit Update Contract
        $('#updateContractForm').on('submit', function(e) {
            e.preventDefault();
            let sponsorId = $('#modalSponsorId').val();
            $.ajax({
                url: '/admin/sponsors/' + sponsorId + '/update-contract',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message, 'Success', { positionClass: 'toast-top-right' });
                        $('#updateContractModal').modal('hide');
                        location.reload();
                    } else {
                        toastr.error(response.message, 'Error', { positionClass: 'toast-top-right' });
                    }
                },
                error: function(xhr) {
                    let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'An error occurred while updating the contract.';
                    toastr.error(msg, 'Error', { positionClass: 'toast-top-right' });
                }
            });
        });

        // Open Not Renewed modal
        $(document).on('click', '.not-renewed-btn', function() {
            $('#notRenewedSponsorId').val($(this).data('id'));
            $('#notRenewedSponsorName').text($(this).data('name'));
            $('#notRenewedContractStart').val($(this).data('contract-start'));
            $('#notRenewedContractEnd').val($(this).data('contract-end'));
            $('#notRenewedYear').val(new Date().getFullYear());
            $('#notRenewedNotes').val('');
            $('#notRenewedModal').modal('show');
        });

        // Submit Not Renewed
        $('#notRenewedForm').on('submit', function(e) {
            e.preventDefault();
            let sponsorId = $('#notRenewedSponsorId').val();
            $.ajax({
                url: '/admin/sponsors/' + sponsorId + '/mark-not-renewed',
                method: 'POST',
                data: {
                    _token:         '{{ csrf_token() }}',
                    renewal_year:   $('#notRenewedYear').val(),
                    contract_start: $('#notRenewedContractStart').val(),
                    contract_end:   $('#notRenewedContractEnd').val(),
                    notes:          $('#notRenewedNotes').val(),
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message, 'Success', { positionClass: 'toast-top-right' });
                        $('#notRenewedModal').modal('hide');
                        location.reload();
                    } else {
                        toastr.error(response.message, 'Error', { positionClass: 'toast-top-right' });
                    }
                },
                error: function(xhr) {
                    let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'An error occurred.';
                    toastr.error(msg, 'Error', { positionClass: 'toast-top-right' });
                }
            });
        });
    </script>
@endpush
