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
        $(document).on('click', '.edit-renewal-btn', function() {
            var d = $(this).data();
            $('#editRenewalId').val(d.id);
            $('#editRenewalSponsorName').text(d.sponsorName || '');
            $('#editContractStart').val(d.contractStart || '');
            $('#editContractEnd').val(d.contractEnd || '');
            $('#editPackage').val(d.package || '');
            $('#editRenewalType').val(d.renewalType || '');
            $('#editAmountUsd').val(d.amountUsd || '');
            $('#editAmountIdr').val(d.amountIdr || '');
            $('#editQuotationNumber').val(d.quotationNumber || '');
            $('#editQuotationDate').val(d.quotationDate || '');
            $('#editInvoiceNumber').val(d.invoiceNumber || '');
            $('#editInvoiceDate').val(d.invoiceDate || '');
            $('#editPaidDate').val(d.paidDate || '');
            $('#editNotes').val(d.notes || '');
            toggleEditRenewedFields(d.status);
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
