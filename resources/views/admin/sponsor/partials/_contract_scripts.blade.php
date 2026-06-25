{{-- JS & modal handler kontrak/renewal bersama: KMK rate, Update Contract,
     Not Renewed, dan Renewal Follow-up. Dipakai oleh sponsor page dan
     annual report — selalu sertakan bersama partials._modals. --}}
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
    </style>

    <script>
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
            var year = new Date().getFullYear();
            $('#modalSponsorId').val($(this).data('sponsor-id'));
            $('#modalContractStart').val($(this).data('contract-start'));
            $('#modalContractEnd').val($(this).data('contract-end'));
            $('#modalPackage').val($(this).data('package') || 'silver');
            $('#modalRenewalType').val('renewal');
            $('#modalAmountUsd').val('');
            $('#modalAmountIdr').val('');
            $('#modalAmountIdrDisplay').val('');
            $('#modalNotes').val('');
            $('#modalQuotationNumber').val('').attr('placeholder', 'Loading...');
            $('#quotationNumberHint').text('');
            $('#updateContractModal').modal('show');
            fetchKmkRate();
            // Fetch suggested quotation number
            $.get('/admin/sponsors/next-quotation-number', { year: year }, function(res) {
                if (res.next) {
                    $('#modalQuotationNumber').attr('placeholder', res.next);
                    $('#quotationNumberHint').text('Suggested: ' + res.next + ' (kosongkan untuk auto)');
                }
            }).fail(function() {
                $('#modalQuotationNumber').attr('placeholder', 'e.g. ' + year + 'DMC14');
            });
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

        // ── Renewal Follow-up ──
        var followupData = []; // cache follow-up terakhir yang di-load (untuk deteksi follow-up pertama)

        function renderFollowupTimeline(followups) {
            // Kosong → siklus belum dimulai (belum generate renewal form / follow-up pertama)
            if (!followups || !followups.length) {
                $('#followupTimeline').html(
                    '<div class="d-flex align-items-start py-2 px-2" style="gap:10px;">' +
                    '<span class="badge badge-secondary" style="border-radius:50%;width:24px;height:24px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-file-signature"></i></span>' +
                    '<div style="flex:1;min-width:0;">' +
                    '<div style="font-size:13px;font-weight:600;color:#888;">Renewal Form belum di-submit</div>' +
                    '<div class="text-muted" style="font-size:12px;">Follow-up pertama akan men-generate renewal form (wajib input KMK).</div>' +
                    '</div></div>');
                return;
            }

            var html = '';

            // Milestone awal: Renewal Form Submitted (ditandai dari follow-up pertama)
            var first = followups[0];
            html += '<div class="d-flex align-items-start py-2 px-2" style="gap:10px;border-bottom:1px solid #e9ecef;background:#eafaf0;border-radius:6px;margin-bottom:4px;">' +
                '<span class="badge" style="background:#47c363;color:#fff;border-radius:50%;width:24px;height:24px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-file-signature"></i></span>' +
                '<div style="flex:1;min-width:0;">' +
                '<div style="font-size:13px;font-weight:700;color:#2d3748;">Renewal Form Submitted</div>' +
                '<div class="text-muted" style="font-size:12px;">' + first.followed_up_at +
                (first.kmk_rate ? ' &middot; KMK IDR ' + Number(first.kmk_rate).toLocaleString('id-ID') + '/USD' : '') +
                ' <span class="badge badge-light border ml-1" style="font-size:10px;">' + first.renewal_year + '</span></div>' +
                '</div></div>';

            // Daftar follow-up: Follow Up 1 / 2 / 3 / dst — (Date) | (Nama PIC)
            followups.forEach(function(f) {
                html += '<div class="d-flex align-items-start py-2 px-2" style="gap:10px;border-bottom:1px dashed #eee;">' +
                    '<span class="badge" style="background:#f39c12;color:#fff;border-radius:50%;width:24px;height:24px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">' + f.sequence + '</span>' +
                    '<div style="flex:1;min-width:0;">' +
                    '<div style="font-size:13px;font-weight:600;color:#333;">Follow Up ' + f.sequence + '</div>' +
                    '<div style="font-size:12px;color:#555;">' + f.followed_up_at +
                    (f.created_by ? ' <span class="text-muted">| ' + $('<span>').text(f.created_by).html() + '</span>' : '') +
                    (f.channel ? ' <span class="text-muted">&middot; via ' + f.channel + '</span>' : '') + '</div>' +
                    (f.notes ? '<div class="text-muted" style="font-size:12px;">' + $('<div>').text(f.notes).html() + '</div>' : '') +
                    '</div>' +
                    '<a href="' + f.proof_url + '" target="_blank" class="btn btn-sm btn-light border" style="flex-shrink:0;" title="View proof">' +
                    '<i class="fas fa-paperclip mr-1"></i>Proof</a>' +
                    '</div>';
            });
            $('#followupTimeline').html(html);
        }

        function loadFollowupTimeline(sponsorId) {
            $('#followupTimeline').html('<div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
            $.get('/admin/sponsors/' + sponsorId + '/followups', function(res) {
                followupData = res.followups || [];
                renderFollowupTimeline(followupData);
                updateFollowupKmkField();
            }).fail(function() {
                followupData = [];
                $('#followupTimeline').html('<div class="text-center text-danger py-3" style="font-size:12px;">Failed to load follow-up history.</div>');
            });
        }

        // KMK rate hanya wajib di follow-up PERTAMA untuk tahun yang dipilih
        function updateFollowupKmkField() {
            var year = parseInt($('#followupYear').val());
            var existing = followupData.filter(function(f) { return parseInt(f.renewal_year) === year; }).length;
            if (existing === 0) {
                $('#followupKmkGroup').show();
                $('#followupKmkRate').prop('required', true);
                if (!$('#followupKmkRate').val()) {
                    fetchFollowupKmkRate();
                }
            } else {
                $('#followupKmkGroup').hide();
                $('#followupKmkRate').prop('required', false).val('');
            }
        }

        function fetchFollowupKmkRate() {
            $('#followupKmkRate').val('').attr('placeholder', 'Loading...');
            $.get('/admin/sponsors/kmk-rate', function(res) {
                if (res.success && res.rate) {
                    $('#followupKmkRate').val(res.rate);
                } else {
                    $('#followupKmkRate').attr('placeholder', 'Gagal fetch — isi manual');
                }
            }).fail(function() {
                $('#followupKmkRate').attr('placeholder', 'Gagal fetch — isi manual');
            });
        }

        $('#btnRefreshFollowupKmk').on('click', fetchFollowupKmkRate);
        $('#followupYear').on('change', updateFollowupKmkField);

        // Open Follow-up modal
        $(document).on('click', '.followup-btn', function() {
            $('#followupSponsorId').val($(this).data('id'));
            $('#followupSponsorName').text($(this).data('name'));
            $('#followupDate').val(new Date().toISOString().slice(0, 10));
            $('#followupYear').val(new Date().getFullYear());
            $('#followupNotes').val('');
            $('#followupProof').val('');
            $('#followupKmkRate').val('');
            $('#followupKmkGroup').hide();
            loadFollowupTimeline($(this).data('id'));
            $('#followupModal').modal('show');
        });

        // Submit Follow-up (multipart karena ada upload bukti)
        $('#followupForm').on('submit', function(e) {
            e.preventDefault();
            var sponsorId = $('#followupSponsorId').val();
            var formData = new FormData(this);
            $('#followupSubmitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
            $.ajax({
                url: '/admin/sponsors/' + sponsorId + '/followups',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    toastr.success(response.message, 'Success', { positionClass: 'toast-top-right' });
                    $('#followupNotes').val('');
                    $('#followupProof').val('');
                    $('#followupKmkRate').val('');
                    loadFollowupTimeline(sponsorId);
                },
                error: function(xhr) {
                    var msg = 'Gagal menyimpan follow-up.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors)[0][0];
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    toastr.error(msg, 'Error', { positionClass: 'toast-top-right' });
                },
                complete: function() {
                    $('#followupSubmitBtn').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Follow-up');
                }
            });
        });
    </script>
@endpush
