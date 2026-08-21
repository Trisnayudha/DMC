@push('bottom')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
(function () {

    function parseOfficeNumber(full) {
        var trimmed = (full || '').trim();
        var match = trimmed.match(/^(\+\d+)\s*(.*)$/);
        if (match) { return { prefix: match[1], office: match[2].trim() }; }
        return { prefix: '', office: trimmed };
    }

    // Looks up the typed company name against already-verified companies and, on
    // a match, fills in the rest of the company block — so the admin isn't
    // re-typing data that's already on file. Called both when the modal opens
    // (the flagged "new company" might already be a known verified one) and
    // whenever the admin changes the name field themselves. Debounced + a
    // request token so a fast edit-then-retype can't have an older lookup's
    // response land after a newer one.
    var verifiedCompanyLookupTimer = null;
    var verifiedCompanyLookupToken = 0;

    function applyVerifiedCompanyAutofill() {
        clearTimeout(verifiedCompanyLookupTimer);

        var typed = $('#efm-company-name').val().trim();
        if (!typed) {
            $('#efm-company-autofilled').addClass('d-none');
            return;
        }

        var thisToken = ++verifiedCompanyLookupToken;

        verifiedCompanyLookupTimer = setTimeout(function () {
            $.ajax({
                url: '{{ route('admin.member_follow_ups.verified_company') }}',
                method: 'GET',
                data: { name: typed },
                dataType: 'json'
            }).done(function (res) {
                if (thisToken !== verifiedCompanyLookupToken) return; // stale response

                if (!res || !res.found) {
                    $('#efm-company-autofilled').addClass('d-none');
                    return;
                }

                var match = res.data;
                $('#efm-prefix').val(match.prefix || '');
                $('#efm-company-website').val(match.company_website || '');
                $('#efm-company-category').val(match.company_category || '').trigger('change');
                $('#efm-company-other').val(match.company_other || '');
                $('#efm-address').val(match.address || '');
                $('#efm-city').val(match.city || '');
                $('#efm-portal-code').val(match.portal_code || '');
                $('#efm-country').val(match.country || '');
                $('#efm-full-office-number').val(match.full_office_number || '');
                $('#efm-prefix-office-number').val(match.prefix_office_number || '');
                $('#efm-office-number').val(match.office_number || '');

                $('#efm-company-autofilled').removeClass('d-none');
            });
        }, 250);
    }

    $('[data-toggle="tooltip"]').tooltip();

    // ──────────────────────────────────────────────────────────────────────
    //  Open Edit & Verify Modal
    // ──────────────────────────────────────────────────────────────────────
    $(document).on('click', '.btn-open-edit-member-modal', function () {
        var btn = $(this);
        $('#efm-follow-up-id').val(btn.data('follow-up-id'));
        $('#efm-btn-submit').data('update-url', btn.data('update-url'));
        $('#efm-member-name').text(btn.data('member-name') || '-');
        $('#efm-flagged-company').text(btn.data('flagged-company') || '-');

        var flaggedJobTitle = btn.data('flagged-job-title');
        if (flaggedJobTitle) {
            $('#efm-flagged-job-title').text(flaggedJobTitle);
            $('#efm-flagged-job-title-wrap').show();
        } else {
            $('#efm-flagged-job-title-wrap').hide();
        }

        $('#efm-name').val(btn.data('name') || '');
        $('#efm-email').val(btn.data('email') || '');
        $('#efm-job-title').val(btn.data('job-title') || '');
        $('#efm-phone').val(btn.data('phone') || '');
        $('#efm-prefix').val(btn.data('prefix') || '');
        $('#efm-company-name').val(btn.data('company-name') || '');
        $('#efm-company-website').val(btn.data('company-website') || '');
        $('#efm-company-category').val(btn.data('company-category') || '').trigger('change');
        $('#efm-company-other').val(btn.data('company-other') || '');
        $('#efm-address').val(btn.data('address') || '');
        $('#efm-city').val(btn.data('city') || '');
        $('#efm-portal-code').val(btn.data('portal-code') || '');
        $('#efm-country').val(btn.data('country') || '');
        $('#efm-prefix-office-number').val(btn.data('prefix-office-number') || '');
        $('#efm-office-number').val(btn.data('office-number') || '');
        $('#efm-full-office-number').val(btn.data('full-office-number') || '');

        // The flagged "new company" pre-filled above might already be a known
        // verified company (typical case — someone else from there already
        // registered) — if so, replace the stale/old-company detail fields with
        // the verified ones straight away, before the admin even looks at them.
        applyVerifiedCompanyAutofill();

        $('#editFollowUpMemberModal').modal('show');
    });

    $(document).on('change', '#efm-company-name', applyVerifiedCompanyAutofill);

    $(document).on('change', '#efm-company-category', function () {
        if ($(this).val() === 'other') {
            $('.efm-company-other-wrap').show();
        } else {
            $('.efm-company-other-wrap').hide();
            $('#efm-company-other').val('');
        }
    });

    $(document).on('input', '#efm-full-office-number', function () {
        var p = parseOfficeNumber($(this).val());
        $('#efm-prefix-office-number').val(p.prefix);
        $('#efm-office-number').val(p.office);
    });

    // ──────────────────────────────────────────────────────────────────────
    //  Submit Edit & Verify
    // ──────────────────────────────────────────────────────────────────────
    $(document).on('click', '#efm-btn-submit', function () {
        var btn = $(this);
        var name = $('#efm-name').val().trim();
        var email = $('#efm-email').val().trim();

        if (!name || !email) {
            toastr.error('Nama dan email wajib diisi.');
            return;
        }

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1"></span> Menyimpan...');

        $.ajax({
            url: btn.data('update-url'),
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                name: name,
                email: email,
                job_title: $('#efm-job-title').val(),
                phone: $('#efm-phone').val(),
                prefix: $('#efm-prefix').val(),
                company_name: $('#efm-company-name').val(),
                company_website: $('#efm-company-website').val(),
                company_category: $('#efm-company-category').val(),
                company_other: $('#efm-company-other').val(),
                address: $('#efm-address').val(),
                city: $('#efm-city').val(),
                portal_code: $('#efm-portal-code').val(),
                country: $('#efm-country').val(),
                prefix_office_number: $('#efm-prefix-office-number').val(),
                office_number: $('#efm-office-number').val(),
                full_office_number: $('#efm-full-office-number').val(),
            },
            success: function (res) {
                if (res.success) {
                    toastr.success(res.message);
                    $('#editFollowUpMemberModal').modal('hide');
                    setTimeout(function () { location.reload(); }, 1200);
                } else {
                    toastr.error(res.message || 'Gagal menyimpan.');
                    btn.prop('disabled', false).html('<i class="fas fa-user-check mr-1"></i> Simpan & Verify');
                }
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Terjadi kesalahan server.';
                toastr.error(msg);
                btn.prop('disabled', false).html('<i class="fas fa-user-check mr-1"></i> Simpan & Verify');
            }
        });
    });

    $('#editFollowUpMemberModal').on('hidden.bs.modal', function () {
        $('#efm-btn-submit').prop('disabled', false).html('<i class="fas fa-user-check mr-1"></i> Simpan & Verify');
    });

    // ──────────────────────────────────────────────────────────────────────
    //  History (reuses Members DMC's user_edit_logs viewer)
    // ──────────────────────────────────────────────────────────────────────
    $(document).on('click', '.btn-view-follow-up-logs', function () {
        var url = $(this).data('logs-url');
        var name = $(this).data('name');

        $('#logs-user-name').text(name);
        $('#logs-loading').show();
        $('#logs-empty').hide();
        $('#logs-content').hide();
        $('#logs-tbody').empty();
        $('#userLogsModal').modal('show');

        var fieldLabels = {
            name: 'Nama', email: 'Email', job_title: 'Job Title', phone: 'Phone',
            prefix: 'Prefix', company_name: 'Company Name', company_website: 'Company Website',
            company_category: 'Company Category', company_other: 'Company Other',
            address: 'Address', city: 'City', portal_code: 'Postal Code', country: 'Country',
            prefix_office_number: 'Prefix Office Number', office_number: 'Office Number',
            full_office_number: 'Full Office Number', is_verified: 'Company Verified',
            status_member: 'Status Member', tier: 'Tier'
        };

        $.ajax({ url: url, method: 'GET', dataType: 'json' })
            .done(function (data) {
                $('#logs-loading').hide();
                if (!data || data.length === 0) { $('#logs-empty').show(); return; }

                $.each(data, function (i, log) {
                    var time = log.created_at || '-';
                    var admin = $('<span>').text(log.admin_name || '-').html();
                    $.each(log.changes || {}, function (field, diff) {
                        var label = fieldLabels[field] || field;
                        var oldVal = $('<span>').text(diff.old || '-').html();
                        var newVal = $('<span>').text(diff.new || '-').html();
                        $('#logs-tbody').append(
                            '<tr>' +
                                '<td class="text-nowrap"><small>' + time + '</small></td>' +
                                '<td><small>' + admin + '</small></td>' +
                                '<td><span class="badge badge-light">' + label + '</span></td>' +
                                '<td><small class="text-danger">' + oldVal + '</small></td>' +
                                '<td><small class="text-success">' + newVal + '</small></td>' +
                            '</tr>'
                        );
                    });
                });

                $('#logs-content').show();
            })
            .fail(function () {
                $('#logs-loading').hide();
                $('#logs-empty').text('Gagal memuat log.').show();
            });
    });

})();
</script>
@endpush
