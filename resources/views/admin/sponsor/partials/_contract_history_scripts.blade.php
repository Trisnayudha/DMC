{{-- JS for the Contract History edit modal. --}}
@push('bottom')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        function toggleEditRenewedFields(status) {
            if (status === 'renewed') {
                $('#editRenewedFields').show();
            } else {
                $('#editRenewedFields').hide();
            }
        }

        // Open Edit modal, prefilled from the row's data-* attributes.
        // Reads each attribute individually (dash-case key, matching the
        // pattern used by .update-contract-btn / .not-renewed-btn elsewhere
        // in this app) rather than bulk $(this).data() — safer against any
        // single attribute (e.g. free-text notes) tripping jQuery's
        // automatic JSON/number coercion for the whole cached data object.
        $(document).on('click', '.edit-renewal-btn', function() {
            var $btn = $(this);
            $('#editRenewalId').val($btn.attr('data-id'));
            $('#editRenewalSponsorName').text($btn.attr('data-sponsor-name') || '');
            $('#editContractStart').val($btn.attr('data-contract-start') || '');
            $('#editContractEnd').val($btn.attr('data-contract-end') || '');
            $('#editPackage').val($btn.attr('data-package') || '');
            $('#editRenewalType').val($btn.attr('data-renewal-type') || '');
            $('#editAmountUsd').val($btn.attr('data-amount-usd') || '');
            $('#editAmountIdr').val($btn.attr('data-amount-idr') || '');
            $('#editQuotationNumber').val($btn.attr('data-quotation-number') || '');
            $('#editQuotationDate').val($btn.attr('data-quotation-date') || '');
            $('#editInvoiceNumber').val($btn.attr('data-invoice-number') || '');
            $('#editInvoiceDate').val($btn.attr('data-invoice-date') || '');
            $('#editPaidDate').val($btn.attr('data-paid-date') || '');
            $('#editNotes').val($btn.attr('data-notes') || '');
            toggleEditRenewedFields($btn.attr('data-status'));
            $('#editRenewalModal').modal('show');
        });

        // Submit Edit
        $('#editRenewalForm').on('submit', function(e) {
            e.preventDefault();
            var id = $('#editRenewalId').val();
            $.ajax({
                url: '/admin/sponsors/contract-history/' + id,
                method: 'PATCH',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message, 'Success', { positionClass: 'toast-top-right' });
                        $('#editRenewalModal').modal('hide');
                        location.reload();
                    } else {
                        toastr.error(response.message, 'Error', { positionClass: 'toast-top-right' });
                    }
                },
                error: function(xhr) {
                    var msg = 'An error occurred while updating the record.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors)[0][0];
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    toastr.error(msg, 'Error', { positionClass: 'toast-top-right' });
                }
            });
        });
    </script>
@endpush
