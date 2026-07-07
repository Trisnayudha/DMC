{{-- JS & modal handlers for contract/renewal: KMK rate, Update Contract,
     Not Renewed, and Renewal Follow-up. Used by the sponsor page and
     annual report — always include alongside partials._modals. --}}
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
                    $('#modalKmkRate').attr('placeholder', 'Failed to fetch');
                }
            }).fail(function() {
                $('#modalKmkRate').attr('placeholder', 'Failed to fetch');
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
            var sponsorId = $(this).data('sponsor-id');
            $('#modalSponsorId').val(sponsorId);
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
            $('#modalKmkRate').val('').attr('placeholder', 'Loading...');
            $('#updateContractModal').modal('show');

            // Fetch suggested quotation number (placeholder saja)
            $.get('/admin/sponsors/next-quotation-number', { year: year }, function(res) {
                if (res.next) {
                    $('#modalQuotationNumber').attr('placeholder', res.next);
                    if (!$('#quotationNumberHint').text()) {
                        $('#quotationNumberHint').text('Suggested: ' + res.next + ' (leave blank for auto)');
                    }
                }
            }).fail(function() {
                $('#modalQuotationNumber').attr('placeholder', 'e.g. ' + year + 'DMC14');
            });

            // Auto-prefill dari renewal form yang sudah di-generate (biar tidak double input).
            // Nilai tetap bisa diedit kalau ada penggantian. KMK dari form diutamakan;
            // kalau belum ada form, baru pakai kurs live.
            $.get('/admin/sponsors/' + sponsorId + '/renewal-form/latest', { year: year }, function(res) {
                var f = res.form;
                if (f && f.kmk_rate) {
                    $('#modalKmkRate').val(f.kmk_rate).attr('placeholder', '');
                } else {
                    fetchKmkRate();
                }
                if (f) {
                    if (f.amount_usd) $('#modalAmountUsd').val(parseFloat(f.amount_usd));
                    if (f.amount_idr) {
                        setIdrValue(f.amount_idr);
                    } else {
                        autoFillIdr();
                    }
                    // Quotation Number = nomor renewal form (dokumen yang sama). Tetap
                    // bisa diedit; kalau nomor sudah dipakai (mis. sponsor sudah pernah
                    // di-confirm), backend akan menolak dan admin bisa ganti manual.
                    if (f.form_number) {
                        $('#modalQuotationNumber').val(f.form_number);
                        $('#quotationNumberHint').text('Dari renewal form ' + f.form_number + ' (bisa diubah)');
                    }
                }
            }).fail(function() {
                fetchKmkRate();
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

        // ══ Renewal Process: Step 1 Generate Renewal Form → Step 2 Follow-up ══
        var followupData        = []; // all follow-ups for the open sponsor
        var renewalFormData     = []; // all renewal forms for the open sponsor
        var sponsorOptionsData  = []; // {id, name, last_amount_usd, last_amount_idr}
        var sponsorOptionsReady = false;

        function rfCurrentYear() {
            return parseInt($('#renewalYear').val(), 10);
        }

        // ── Sponsor picker (with last-paid USD for auto-fill) ──
        function loadSponsorOptions(cb) {
            if (sponsorOptionsReady) { if (cb) cb(); return; }
            $.get('/admin/sponsors/renewal-form-options', function(res) {
                sponsorOptionsData = res.sponsors || [];
                var html = '<option value="">— Select sponsor —</option>';
                sponsorOptionsData.forEach(function(s) {
                    html += '<option value="' + s.id + '">' + $('<span>').text(s.name).html() + '</option>';
                });
                $('#rfSponsorSelect').html(html);
                sponsorOptionsReady = true;
                if (cb) cb();
            }).fail(function() {
                if (cb) cb();
            });
        }

        // Pastikan sponsor tertentu ada & terpilih di dropdown (mis. saat dibuka dari baris)
        function selectSponsorOption(id, name) {
            if ($('#rfSponsorSelect option[value="' + id + '"]').length === 0) {
                $('#rfSponsorSelect').append('<option value="' + id + '">' + $('<span>').text(name || '').html() + '</option>');
            }
            $('#rfSponsorSelect').val(String(id));
        }

        function lastUsdFor(id) {
            for (var i = 0; i < sponsorOptionsData.length; i++) {
                if (parseInt(sponsorOptionsData[i].id, 10) === parseInt(id, 10)) {
                    return sponsorOptionsData[i].last_amount_usd;
                }
            }
            return null;
        }

        function rfFormForYear(year) {
            for (var i = 0; i < renewalFormData.length; i++) {
                if (parseInt(renewalFormData[i].renewal_year, 10) === year) return renewalFormData[i];
            }
            return null;
        }

        // ── USD/IDR auto-calc for the Generate Renewal Form fields ──
        function rfSetIdr(raw) {
            var num = Math.round(parseFloat(raw));
            if (!isNaN(num) && num > 0) {
                $('#rfAmountIdr').val(num);
                $('#rfAmountIdrDisplay').val(num.toLocaleString('id-ID'));
            } else {
                $('#rfAmountIdr').val('');
                $('#rfAmountIdrDisplay').val('');
            }
        }
        function rfAutoFillIdr() {
            var usd  = parseFloat($('#rfAmountUsd').val());
            var rate = parseFloat($('#rfKmkRate').val());
            if (!isNaN(usd) && usd > 0 && !isNaN(rate) && rate > 0) {
                rfSetIdr(usd * rate);
            }
        }
        $('#rfAmountUsd').on('input', rfAutoFillIdr);
        $('#rfKmkRate').on('input', rfAutoFillIdr);
        $('#rfAmountIdrDisplay').on('input', function() {
            var raw = $(this).val().replace(/\./g, '').replace(/,/g, '');
            $('#rfAmountIdr').val(raw);
        }).on('blur', function() {
            var raw = $(this).val().replace(/\./g, '').replace(/,/g, '');
            var num = parseInt(raw, 10);
            if (!isNaN(num) && num > 0) {
                $(this).val(num.toLocaleString('id-ID'));
                $('#rfAmountIdr').val(num);
            }
        });

        function fetchRfKmkRate() {
            $('#rfKmkRate').val('').attr('placeholder', 'Loading...');
            $.get('/admin/sponsors/kmk-rate', function(res) {
                if (res.success && res.rate) {
                    $('#rfKmkRate').val(res.rate);
                    rfAutoFillIdr();
                } else {
                    $('#rfKmkRate').attr('placeholder', 'Failed to fetch — enter manually');
                }
            }).fail(function() {
                $('#rfKmkRate').attr('placeholder', 'Failed to fetch — enter manually');
            });
        }
        $('#btnRefreshRfKmk').on('click', fetchRfKmkRate);

        function fetchNextFormNumber(year) {
            $('#rfFormNumber').val('').attr('placeholder', 'Loading...');
            $('#rfFormNumberHint').text('');
            $.get('/admin/sponsors/next-form-number', { year: year }, function(res) {
                if (res.next) {
                    $('#rfFormNumber').attr('placeholder', res.next);
                    $('#rfFormNumberHint').text('Suggested: ' + res.next + ' (leave blank for auto)');
                }
            }).fail(function() {
                $('#rfFormNumber').attr('placeholder', year + 'DMC1');
            });
        }

        // Reset the empty Generate form for a fresh year (form number + KMK + auto USD)
        function prefillRenewalForm(year) {
            // USD auto dari nominal terakhir sponsor ini bayar (history renewed terakhir).
            var lastUsd = lastUsdFor($('#followupSponsorId').val());
            if (lastUsd && parseFloat(lastUsd) > 0) {
                $('#rfAmountUsd').val(parseFloat(lastUsd));
                $('#rfUsdHint').text('Auto dari pembayaran terakhir: USD ' + Number(lastUsd).toLocaleString('id-ID') + ' (bisa diubah)');
            } else {
                $('#rfAmountUsd').val('');
                $('#rfUsdHint').text('Belum ada history pembayaran — isi manual.');
            }
            $('#rfAmountIdr').val('');
            $('#rfAmountIdrDisplay').val('');
            $('#rfKmkNumber').val('');
            $('#rfNotes').val('');
            fetchNextFormNumber(year);
            fetchRfKmkRate(); // IDR ter-recalc dari USD × KMK begitu rate masuk
        }

        function renderGeneratedBox(form) {
            var sponsorId = $('#followupSponsorId').val();
            $('#rfGenNumber').text(form.form_number || '—');
            $('#rfGenDate').text(form.generated_at || '—');
            $('#rfGenBy').text(form.created_by ? ' · by ' + form.created_by : '');
            $('#rfGenKmk').text(form.kmk_rate ? 'IDR ' + Number(form.kmk_rate).toLocaleString('id-ID') + '/USD' : '—');
            $('#rfGenKmkNumber').text(form.kmk_number || '—');
            var amt = [];
            if (form.amount_usd) amt.push('USD ' + Number(form.amount_usd).toLocaleString('id-ID'));
            if (form.amount_idr) amt.push('IDR ' + Number(form.amount_idr).toLocaleString('id-ID'));
            $('#rfGenAmount').text(amt.length ? 'Value: ' + amt.join(' · ') : '');
            $('#rfPreviewBtn').attr('href', '/admin/sponsors/' + sponsorId + '/renewal-form/preview');
        }

        function renderFollowupTimeline(followups, form) {
            var html = '';

            // Renewal form milestone (Step 1)
            if (form) {
                html += '<div class="d-flex align-items-start py-2 px-2" style="gap:10px;border-bottom:1px solid #e9ecef;background:#eafaf0;border-radius:6px;margin-bottom:4px;">' +
                    '<span class="badge" style="background:#47c363;color:#fff;border-radius:50%;width:24px;height:24px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-file-signature"></i></span>' +
                    '<div style="flex:1;min-width:0;">' +
                    '<div style="font-size:13px;font-weight:700;color:#2d3748;">Renewal Form ' + $('<span>').text(form.form_number || '').html() + '</div>' +
                    '<div class="text-muted" style="font-size:12px;">' + (form.generated_at || '') +
                    (form.kmk_rate ? ' &middot; KMK IDR ' + Number(form.kmk_rate).toLocaleString('id-ID') + '/USD' : '') + '</div>' +
                    '</div></div>';
            }

            if (!followups || !followups.length) {
                html += '<div class="text-muted text-center py-2" style="font-size:12px;">No follow-up recorded yet for this year.</div>';
                $('#followupTimeline').html(html);
                return;
            }

            // Follow-up list: Follow Up 1 / 2 / 3 — (Date) | (PIC Name)
            followups.forEach(function(f, idx) {
                html += '<div class="d-flex align-items-start py-2 px-2" style="gap:10px;border-bottom:1px dashed #eee;">' +
                    '<span class="badge" style="background:#f39c12;color:#fff;border-radius:50%;width:24px;height:24px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">' + (idx + 1) + '</span>' +
                    '<div style="flex:1;min-width:0;">' +
                    '<div style="font-size:13px;font-weight:600;color:#333;">Follow Up ' + (idx + 1) + '</div>' +
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

        // Toggle Step 1 / Step 2 based on whether a renewal form exists for the year
        function refreshRenewalUI() {
            var year = rfCurrentYear();
            var form = rfFormForYear(year);
            var yearFollowups = followupData.filter(function(f) { return parseInt(f.renewal_year, 10) === year; });

            if (form) {
                renderGeneratedBox(form);
                $('#renewalFormGenerated').show();
                $('#renewalFormForm').hide();
                $('#followupLocked').hide();
                $('#followupContent').show();
            } else {
                $('#renewalFormGenerated').hide();
                $('#renewalFormForm').show();
                prefillRenewalForm(year);
                $('#followupContent').hide();
                $('#followupLocked').show();
            }
            renderFollowupTimeline(yearFollowups, form);
        }

        function loadRenewalData(sponsorId) {
            $('#followupTimeline').html('<div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
            $.get('/admin/sponsors/' + sponsorId + '/followups', function(res) {
                followupData    = res.followups || [];
                renewalFormData = res.renewalForms || [];
                refreshRenewalUI();
            }).fail(function() {
                followupData = [];
                renewalFormData = [];
                $('#followupTimeline').html('<div class="text-center text-danger py-3" style="font-size:12px;">Failed to load renewal data.</div>');
            });
        }

        $('#renewalYear').on('change', refreshRenewalUI);

        // Ganti sponsor dari dropdown → muat ulang data & gating untuk sponsor terpilih
        $('#rfSponsorSelect').on('change', function() {
            var id = $(this).val();
            if (!id) return;
            var name = $(this).find('option:selected').text();
            $('#followupSponsorId').val(id);
            $('#followupSponsorName').text(name);
            loadRenewalData(id);
        });

        // Open Renewal Process modal
        $(document).on('click', '.followup-btn', function() {
            var id   = $(this).data('id');
            var name = $(this).data('name');
            $('#followupSponsorId').val(id);
            $('#followupSponsorName').text(name);
            $('#renewalYear').val(new Date().getFullYear());
            $('#followupDate').val(new Date().toISOString().slice(0, 10));
            $('#followupNotes').val('');
            $('#followupProof').val('');
            $('#followupModal').modal('show');
            // Muat daftar sponsor dulu (butuh last-paid USD), lalu pilih & muat datanya
            loadSponsorOptions(function() {
                selectSponsorOption(id, name);
                loadRenewalData(id);
            });
        });

        // Submit: Generate Renewal Form (Step 1)
        $('#renewalFormForm').on('submit', function(e) {
            e.preventDefault();
            var sponsorId = $('#followupSponsorId').val();
            $('#rfSubmitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Generating...');
            $.ajax({
                url: '/admin/sponsors/' + sponsorId + '/renewal-form',
                method: 'POST',
                data: {
                    _token:       '{{ csrf_token() }}',
                    renewal_year: $('#renewalYear').val(),
                    form_number:  $('#rfFormNumber').val(),
                    kmk_rate:     $('#rfKmkRate').val(),
                    kmk_number:   $('#rfKmkNumber').val(),
                    amount_usd:   $('#rfAmountUsd').val(),
                    amount_idr:   $('#rfAmountIdr').val(),
                    notes:        $('#rfNotes').val(),
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message, 'Success', { positionClass: 'toast-top-right' });
                        loadRenewalData(sponsorId);
                    } else {
                        toastr.error(response.message, 'Error', { positionClass: 'toast-top-right' });
                    }
                },
                error: function(xhr) {
                    var msg = 'Failed to generate renewal form.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors)[0][0];
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    toastr.error(msg, 'Error', { positionClass: 'toast-top-right' });
                },
                complete: function() {
                    $('#rfSubmitBtn').prop('disabled', false).html('<i class="fas fa-file-signature mr-1"></i> Generate Renewal Form');
                }
            });
        });

        // Submit: Follow-up (Step 2, multipart — with proof upload)
        $('#followupForm').on('submit', function(e) {
            e.preventDefault();
            var sponsorId = $('#followupSponsorId').val();
            var formData = new FormData(this);
            formData.append('renewal_year', $('#renewalYear').val());
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
                    loadRenewalData(sponsorId);
                },
                error: function(xhr) {
                    var msg = 'Failed to save follow-up.';
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
