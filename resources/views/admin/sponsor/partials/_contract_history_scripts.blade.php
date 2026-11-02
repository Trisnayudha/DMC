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

        // Data inti kontrak dikunci di Edit Contract; yang bisa diubah hanya VAT, PPh 23,
        // Invoice Sent Date, Paid Date & Notes. Field terkunci tetap ikut terkirim (lihat submit).
        var EDIT_LOCKED_FIELDS = '#editContractStart, #editContractEnd, #editPackage, #editRenewalType, '
            + '#editAmountUsdDisplay, #editAmountIdrDisplay, #editQuotationNumber, #editQuotationDate, #editInvoiceNumber, #editInvoiceDate';
        $(function() { $(EDIT_LOCKED_FIELDS).prop('disabled', true); });

        // Saran PPh 23 = 2% dari Amount IDR sebelum VAT. Cuma saran — nominal di input
        // yang disimpan, finance bebas koreksi.
        function pph23Suggestion() {
            var base = parseFloat($('#editAmountIdr').val());
            return !isNaN(base) && base > 0 ? Math.round(base * 2 / 100) : null;
        }
        // Format angka gaya Indonesia (62.520.500); kosong kalau bukan angka > 0.
        function fmtId(n) {
            var num = parseFloat(n);
            return !isNaN(num) && num > 0 ? num.toLocaleString('id-ID', { maximumFractionDigits: 2 }) : '';
        }
        // Isi hidden (angka mentah, yang dikirim) + display terformat sekaligus.
        function setEditAmount(id, value) {
            var num = parseFloat(value);
            $('#' + id).val(!isNaN(num) && num > 0 ? num : '');
            $('#' + id + 'Display').val(fmtId(value));
        }

        $('#btnPph23Suggest').on('click', function() {
            var s = pph23Suggestion();
            if (s !== null) { setEditAmount('editPph23Amount', s); updateEditTaxSummary(); }
        });
        // Ketik nominal PPh 23: titik/koma diabaikan, angka mentah ke hidden, format ulang saat blur.
        $('#editPph23AmountDisplay').on('input', function() {
            var raw = $(this).val().replace(/[^\d]/g, '');
            $('#editPph23Amount').val(raw);
            updateEditTaxSummary();
        }).on('blur', function() {
            setEditAmount('editPph23Amount', $('#editPph23Amount').val());
        });

        // Ringkasan pajak per mata uang: Amount, VAT, Grand Total, potongan PPh 23 dan Net
        // Received. PPh IDR = nominal di input; USD pakai rasio nominal / Amount IDR.
        // Disembunyikan kalau belum ada amount.
        function updateEditTaxSummary() {
            var s = pph23Suggestion();
            $('#editPph23Hint').text(s !== null ? '2% of Amount IDR before VAT = IDR ' + s.toLocaleString('id-ID') + ' (editable)' : '');
            var vat = parseFloat($('#editVatPercent').val()) || 0;
            var pphIdr = parseFloat($('#editPph23Amount').val()) || 0;
            var amountIdr = parseFloat($('#editAmountIdr').val()) || 0;
            var pph = pphIdr > 0 && amountIdr > 0 ? pphIdr / amountIdr * 100 : 0;
            var fmt = function(n) { return Math.round(n).toLocaleString('id-ID'); };
            var rows = [];
            [['IDR', parseFloat($('#editAmountIdr').val())], ['USD', parseFloat($('#editAmountUsd').val())]].forEach(function(c) {
                var cur = c[0], base = c[1];
                if (isNaN(base) || base <= 0) return;
                var vatAmt = base * vat / 100, pphAmt = cur === 'IDR' ? pphIdr : base * pph / 100;
                var html = '<div class="mb-1"><strong>' + cur + '</strong> &middot; Amount ' + fmt(base);
                if (vat > 0) html += ' + VAT ' + vat + '% ' + fmt(vatAmt) + ' = <strong>Grand Total ' + fmt(base + vatAmt) + '</strong>';
                if (pph > 0) html += ' &minus; PPh 23 ' + fmt(pphAmt);
                html += ' &rarr; <span class="text-success font-weight-bold">' + (pph > 0 ? 'Net Received ' : 'Total ') + cur + ' ' + fmt(base + vatAmt - pphAmt) + '</span></div>';
                rows.push(html);
            });
            $('#editTaxSummary').html(rows.join('')).toggle(rows.length > 0);
        }
        $(document).on('change', '#editVatPercent', updateEditTaxSummary);

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
            setEditAmount('editAmountUsd', $btn.attr('data-amount-usd'));
            setEditAmount('editAmountIdr', $btn.attr('data-amount-idr'));
            $('#editQuotationNumber').val($btn.attr('data-quotation-number') || '');
            $('#editQuotationDate').val($btn.attr('data-quotation-date') || '');
            $('#editInvoiceNumber').val($btn.attr('data-invoice-number') || '');
            $('#editInvoiceDate').val($btn.attr('data-invoice-date') || '');
            $('#editInvoiceSentDate').val($btn.attr('data-invoice-sent-date') || '');
            $('#editPaidDate').val($btn.attr('data-paid-date') || '');
            $('#editNotes').val($btn.attr('data-notes') || '');
            $('#editVatPercent').val(parseFloat($btn.attr('data-vat-percent')) > 0 ? String(parseInt($btn.attr('data-vat-percent'), 10)) : '0');
            // Nominal tersimpan diutamakan; data lama yang cuma punya persen dihitung ulang dari Amount IDR.
            var pphAmt = parseFloat($btn.attr('data-pph23-amount'));
            var pphPct = parseFloat($btn.attr('data-pph23-percent'));
            var idrAmt = parseFloat($btn.attr('data-amount-idr'));
            if (!(pphAmt > 0) && pphPct > 0 && idrAmt > 0) pphAmt = Math.round(idrAmt * pphPct / 100);
            setEditAmount('editPph23Amount', pphAmt > 0 ? Math.round(pphAmt) : '');
            $('#editVatHint').text('');
            updateEditTaxSummary();
            toggleEditRenewedFields($btn.attr('data-status'));
            $('#editRenewalFormRef').hide();
            $('#editRenewalModal').modal('show');

            // Referensi: renewal form (proposal) terakhir yang pernah di-generate untuk
            // sponsor ini, kalau ada. Ditampilkan apa adanya (read-only) — field di atas
            // TIDAK auto-diisi dari sini, supaya nggak menimpa data kontrak historis yang
            // sudah benar. "Use as Quotation No." tersedia buat yang mau nyalin manual.
            var sponsorId = $btn.attr('data-sponsor-id');
            if (sponsorId) {
                $.get('/admin/sponsors/' + sponsorId + '/renewal-form/latest', function(res) {
                    var f = res && res.form;
                    if (!f) return;

                    $('#editRenewalFormRefNumber').text(f.form_number || '—');
                    $('#editRenewalFormRefDate').text(f.generated_at || '—');
                    $('#editRenewalFormRefKmk').text(f.kmk_rate
                        ? ('IDR ' + Number(f.kmk_rate).toLocaleString('id-ID') + '/USD' + (f.kmk_number ? ' (' + f.kmk_number + ')' : ''))
                        : '—');

                    var amountParts = [];
                    if (f.amount_usd) amountParts.push('USD ' + Number(f.amount_usd).toLocaleString('id-ID'));
                    if (f.amount_idr) amountParts.push('IDR ' + Number(f.amount_idr).toLocaleString('id-ID'));
                    $('#editRenewalFormRefAmount').text(amountParts.length ? amountParts.join(' / ') : '—');

                    // VAT cuma diinfoin kalau memang diisi (> 0%) — default nggak kena VAT.
                    var vat = parseFloat(f.vat_percent);
                    $('#editRenewalFormRefVat').text(vat > 0 ? ' · VAT ' + vat + '%' : '');

                    // Kontrak yang tercatat 0% padahal renewal form tahun yang sama kena VAT
                    // (dikonfirmasi sebelum VAT ikut disimpan): isi VAT dari form, tersimpan saat Save.
                    if (vat > 0 && $('#editVatPercent').val() === '0'
                        && String(f.renewal_year) === String($btn.attr('data-renewal-year'))) {
                        $('#editVatPercent').val(String(parseInt(vat, 10)));
                        $('#editVatHint').text('Prefilled from renewal form ' + (f.form_number || '') + ' — click Save Changes to keep it.');
                        updateEditTaxSummary();
                    }

                    if (f.notes) {
                        $('#editRenewalFormRefNotes').text(f.notes);
                        $('#editRenewalFormRefNotesWrap').show();
                    } else {
                        $('#editRenewalFormRefNotesWrap').hide();
                    }

                    $('#editRenewalFormRefLink').attr('href', '/admin/sponsors/' + sponsorId + '/renewal-form/preview');
                    $('#editRenewalFormRef').show();
                });
            }
        });

        // Submit Edit
        $('#editRenewalForm').on('submit', function(e) {
            e.preventDefault();
            var id = $('#editRenewalId').val();
            // Field disabled tidak ikut serialize — aktifkan sebentar supaya nilainya tetap terkirim.
            var $locked = $(EDIT_LOCKED_FIELDS).prop('disabled', false);
            var payload = $(this).serialize();
            $locked.prop('disabled', true);
            $.ajax({
                url: '/admin/sponsors/contract-history/' + id,
                method: 'PATCH',
                data: payload,
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
