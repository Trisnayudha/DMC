@push('bottom')
<script>
    // CSRF untuk semua AJAX
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
    });

    $('[data-toggle="tooltip"]').tooltip();

    function parseOfficeNumber(full) {
        var trimmed = (full || '').trim();
        var match = trimmed.match(/^(\+\d+)\s*(.*)$/);
        if (match) { return { prefix: match[1], office: match[2].trim() }; }
        return { prefix: '', office: trimmed };
    }

    // Helper: alert di atas tabel
    function showAlert(type, message) {
        $('#alert-area').html(
            `<div class="alert alert-${type} alert-dismissible show fade">
                <div class="alert-body">
                    <button class="close" data-dismiss="alert"><span>×</span></button>
                    ${message}
                </div>
            </div>`
        );
        $('html, body').animate({ scrollTop: $('#alert-area').offset().top - 80 }, 300);
    }

    function parseTags(raw) {
        if (!raw) return [];
        if (Array.isArray(raw)) return raw;
        try {
            const j = JSON.parse(raw);
            return Array.isArray(j) ? j : [];
        } catch (e) {
            return String(raw).split(',').map(s => s.trim()).filter(Boolean);
        }
    }

    // =========================================================
    // VERIFY MEMBER (2-step flow)
    // =========================================================
    var $vmSourceBtn = null;

    function vmFillCompanyFields(p) {
        $('#vm-prefix').val(p.prefix || '');
        if ($.fn.select2) $('#vm-prefix').trigger('change');
        $('#vm-company-name').val(p.company_name || '');
        $('#vm-company-website').val(p.company_website || '');
        $('#vm-company-category').val(p.company_category || '');
        $('#vm-company-other').val(p.company_other || '');
        $('#vm-address').val(p.address || '');
        $('#vm-city').val(p.city || '');
        $('#vm-portal-code').val(p.portal_code || '');
        $('#vm-country').val(p.country || '');
        var vmFullOffice = p.full_office_number || '';
        $('#vm-full-office-number').val(vmFullOffice);
        var vmParsed = parseOfficeNumber(vmFullOffice);
        $('#vm-prefix-office-number').val(p.prefix_office_number || vmParsed.prefix);
        $('#vm-office-number').val(p.office_number || vmParsed.office);
        if ((p.company_category || '') === 'other') {
            $('.vm-company-other-wrap').show();
        } else {
            $('.vm-company-other-wrap').hide();
            $('#vm-company-other').val('');
        }
    }

    function vmFillUserFields($btn) {
        $('#vm-user-name').val($btn.attr('data-member-name') || '');
        $('#vm-user-email').val($btn.attr('data-member-email') || '');
        $('#vm-user-job-title').val($btn.attr('data-member-job-title') || '');
        $('#vm-user-phone').val($btn.attr('data-member-phone') || '');
    }

    function vmDoVerifyMember(url, $btn) {
        $('#vm-btn-verify-member, #vm-btn-verify-member-direct').prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm mr-1"></span> Memverifikasi...');

        $.ajax({
            url,
            method: 'POST',
            dataType: 'json',
            data: {
                name: $('#vm-user-name').val() || undefined,
                email: $('#vm-user-email').val() || undefined,
                job_title: $('#vm-user-job-title').val() || undefined,
                phone: $('#vm-user-phone').val() || undefined,
            }
        })
        .done(function(res) {
            if (res && res.success) {
                if ($btn) {
                    const $td  = $btn.closest('td');
                    const $row = $btn.closest('tr');
                    $td.find('.member-status-badge').removeClass('badge-warning').addClass('badge-success')
                        .html('<i class="fas fa-check mr-1"></i>Active');
                    $btn.removeClass('btn-warning').addClass('btn-success')
                        .attr('disabled', true)
                        .html('<i class="fas fa-check"></i> Verified');
                    $row.removeClass('table-warning').css('background-color', '');
                }
                $('#verifyMemberModal').modal('hide');
                showAlert('success', '<i class="fas fa-check-circle mr-1"></i>' + res.message);
            } else {
                $('#vm-btn-verify-member, #vm-btn-verify-member-direct').prop('disabled', false)
                    .html('<i class="fas fa-shield-alt mr-1"></i> Verify Member Sekarang');
                showAlert('warning', (res && res.message) || 'Gagal verifikasi member.');
            }
        })
        .fail(function(xhr) {
            const msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menghubungi server.';
            $('#vm-btn-verify-member, #vm-btn-verify-member-direct').prop('disabled', false)
                .html('<i class="fas fa-shield-alt mr-1"></i> Verify Member Sekarang');
            showAlert('danger', msg);
        });
    }

    // Klik tombol Verify di tabel
    $(document).on('click', '.btn-verify-member:not([disabled])', function() {
        $vmSourceBtn = $(this);
        const companyVerified = String($vmSourceBtn.attr('data-company-verified')) === '1';
        const url = $vmSourceBtn.attr('data-url');
        const memberName = $vmSourceBtn.attr('data-member-name') || '';

        $('#vm-modal-title').text('Verifikasi Member — ' + memberName);
        $('#vm-member-name').text(memberName);
        $('#vm-step2-member-name').text(memberName);
        $('#vm-btn-verify-member').data('verify-url', url);
        $('#vm-btn-verify-member-direct').data('verify-url', url);

        vmFillUserFields($vmSourceBtn);

        if (companyVerified) {
            // Company sudah verified → tampilkan Step 1 mode simpel (hanya user info)
            $('#vm-alert-company-not-verified').hide();
            $('#vm-alert-company-verified').show();
            $('#vm-company-verified-label').text($vmSourceBtn.attr('data-company-name') || '');
            $('#vm-company-form-section').hide();
            $('#vm-btn-verify-company').hide();
            $('#vm-btn-verify-member-direct').show().prop('disabled', false)
                .html('<i class="fas fa-shield-alt mr-1"></i> Verify Member Sekarang');

            $('#vm-step-indicator-1').removeClass('badge-light').addClass('badge-primary');
            $('#vm-step-indicator-2').removeClass('badge-primary').addClass('badge-light');
            $('#vm-step-1').show();
            $('#vm-step-2').hide();
        } else {
            // Company belum verified → tampilkan form company
            let payload = {};
            try { payload = JSON.parse($vmSourceBtn.attr('data-payload') || '{}'); } catch (e) {}

            $('#vm-alert-company-not-verified').show();
            $('#vm-alert-company-verified').hide();
            $('#vm-company-label').text($vmSourceBtn.attr('data-company-name') || '-');
            $('#vm-normalized-name').val($vmSourceBtn.attr('data-normalized-name') || '');
            $('#vm-company-form-section').show();
            $('#vm-btn-verify-company').show().prop('disabled', false)
                .html('<i class="fas fa-check-circle mr-1"></i> Verifikasi Company & Lanjut');
            $('#vm-btn-verify-member-direct').hide();
            $('#vm-company-suggestions').hide().empty();

            vmFillCompanyFields(payload);

            if ($.fn.select2) {
                $('.vm-prefix-select2').select2({
                    dropdownParent: $('#verifyMemberModal'),
                    width: '100%',
                    placeholder: 'Select Prefix',
                    allowClear: true
                });
                $('#vm-prefix').val(payload.prefix || '').trigger('change');
            }

            $('#vm-step-indicator-1').removeClass('badge-light').addClass('badge-primary');
            $('#vm-step-indicator-2').removeClass('badge-primary').addClass('badge-light');
            $('#vm-step-1').show();
            $('#vm-step-2').hide();
        }

        $('#verifyMemberModal').modal('show');
    });

    // Direct verify (company already verified)
    $(document).on('click', '#vm-btn-verify-member-direct', function() {
        vmDoVerifyMember($(this).data('verify-url'), $vmSourceBtn);
    });

    // Step 2 verify button
    $(document).on('click', '#vm-btn-verify-member', function() {
        vmDoVerifyMember($(this).data('verify-url'), $vmSourceBtn);
    });

    $(document).on('input', '#vm-full-office-number', function() {
        var p = parseOfficeNumber($(this).val());
        $('#vm-prefix-office-number').val(p.prefix);
        $('#vm-office-number').val(p.office);
    });

    // Autocomplete company name
    var vmSuggestTimeout = null;
    $(document).on('input', '#vm-company-name', function() {
        var q = $(this).val().trim();
        clearTimeout(vmSuggestTimeout);
        $('#vm-company-suggestions').hide().empty();
        if (q.length < 2) return;

        vmSuggestTimeout = setTimeout(function() {
            $.ajax({
                url: '{{ route('admin.company_database.verified_companies') }}',
                data: { q },
                success: function(data) {
                    var $box = $('#vm-company-suggestions');
                    $box.empty();
                    if (!data || data.length === 0) { $box.hide(); return; }
                    $.each(data, function(i, c) {
                        var $item = $('<button type="button" class="list-group-item list-group-item-action"></button>');
                        $item.html(
                            '<span class="badge badge-success badge-sm mr-1"><i class="fas fa-check-circle"></i> Verified</span> <strong>' +
                            $('<span>').text(c.company_name).html() + '</strong>'
                        );
                        $item.on('click', function() { vmFillCompanyFields(c); $box.hide().empty(); });
                        $box.append($item);
                    });
                    $box.show();
                }
            });
        }, 300);
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#vm-company-name, #vm-company-suggestions').length) {
            $('#vm-company-suggestions').hide().empty();
        }
    });

    $(document).on('change', '#vm-company-category', function() {
        if ($(this).val() === 'other') {
            $('.vm-company-other-wrap').show();
        } else {
            $('.vm-company-other-wrap').hide();
            $('#vm-company-other').val('');
        }
    });

    // Step 1: Verifikasi Company & Lanjut
    $('#vm-btn-verify-company').on('click', function() {
        const companyName = $('#vm-company-name').val().trim();
        if (!companyName) { showAlert('warning', 'Company name wajib diisi.'); return; }

        $(this).prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm mr-1"></span> Menyimpan...');

        $.ajax({
            url: '{{ route('admin.company_database.update') }}',
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                normalized_name:      $('#vm-normalized-name').val(),
                company_name:         companyName,
                prefix:               $('#vm-prefix').val(),
                company_website:      $('#vm-company-website').val(),
                company_category:     $('#vm-company-category').val(),
                company_other:        $('#vm-company-other').val(),
                address:              $('#vm-address').val(),
                city:                 $('#vm-city').val(),
                portal_code:          $('#vm-portal-code').val(),
                country:              $('#vm-country').val(),
                prefix_office_number: $('#vm-prefix-office-number').val(),
                office_number:        $('#vm-office-number').val(),
                full_office_number:   $('#vm-full-office-number').val(),
            },
        })
        .done(function() {
            $('#vm-step-indicator-1').removeClass('badge-primary').addClass('badge-light');
            $('#vm-step-indicator-2').removeClass('badge-light').addClass('badge-primary');
            $('#vm-step2-company-label').text('Company "' + companyName + '" berhasil diverifikasi.');
            $('#vm-step-1').hide();
            $('#vm-step-2').show();
            $('#vm-btn-verify-member').prop('disabled', false)
                .html('<i class="fas fa-shield-alt mr-1"></i> Verify Member Sekarang');

            if ($vmSourceBtn) {
                $vmSourceBtn.attr('data-company-verified', '1')
                    .find('i').removeClass('fa-exclamation-triangle').addClass('fa-shield-alt');
            }
        })
        .fail(function(xhr) {
            const msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menyimpan data company.';
            $('#vm-btn-verify-company').prop('disabled', false)
                .html('<i class="fas fa-check-circle mr-1"></i> Verifikasi Company & Lanjut');
            showAlert('danger', msg);
        });
    });

    // =========================================================
    // DECLINE MEMBER
    // =========================================================
    function vmShowDeclineStep() {
        const memberName  = $vmSourceBtn ? ($vmSourceBtn.attr('data-member-name') || '-') : '-';
        const memberEmail = $vmSourceBtn ? ($vmSourceBtn.attr('data-member-email') || '-') : '-';
        $('#vm-decline-member-name').text(memberName);
        $('#vm-decline-member-email').text(memberEmail);
        $('#vm-step-1').hide();
        $('#vm-step-2').hide();
        $('#vm-step-decline').show();
    }

    $(document).on('click', '#vm-btn-open-decline, #vm-btn-open-decline-step2', vmShowDeclineStep);

    $(document).on('click', '#vm-btn-decline-cancel', function() {
        $('#vm-step-decline').hide();
        // Kembali ke step yang aktif sebelum decline
        const onStep2 = $('#vm-step-indicator-2').hasClass('badge-primary');
        if (onStep2) { $('#vm-step-2').show(); } else { $('#vm-step-1').show(); }
    });

    $(document).on('click', '#vm-btn-decline-confirm', function() {
        if (!$vmSourceBtn) return;

        const $btn = $(this);
        const url  = $vmSourceBtn.attr('data-url').replace(/\/verify$/, '/decline');

        $btn.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm mr-1"></span> Memproses...');

        $.ajax({ url, method: 'POST', dataType: 'json' })
            .done(function(res) {
                if (res && res.success) {
                    if ($vmSourceBtn) {
                        const $td  = $vmSourceBtn.closest('td');
                        const $row = $vmSourceBtn.closest('tr');
                        $td.find('.member-status-badge').removeClass('badge-warning').addClass('badge-danger')
                            .html('<i class="fas fa-times mr-1"></i>Declined');
                        $vmSourceBtn.removeClass('btn-warning').addClass('btn-danger')
                            .attr('disabled', true)
                            .html('<i class="fas fa-times"></i> Declined');
                        $row.css('background-color', '#fff5f5');
                    }
                    $('#verifyMemberModal').modal('hide');
                    showAlert('success', '<i class="fas fa-check-circle mr-1"></i>' + res.message);
                } else {
                    $btn.prop('disabled', false)
                        .html('<i class="fas fa-times-circle mr-1"></i> Ya, Decline & Kirim Email');
                    showAlert('warning', (res && res.message) || 'Gagal decline member.');
                }
            })
            .fail(function(xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menghubungi server.';
                $btn.prop('disabled', false)
                    .html('<i class="fas fa-times-circle mr-1"></i> Ya, Decline & Kirim Email');
                showAlert('danger', msg);
            });
    });

    // Reset decline step ketika modal ditutup
    $('#verifyMemberModal').on('hidden.bs.modal', function() {
        $('#vm-step-decline').hide();
        $('#vm-btn-decline-confirm').prop('disabled', false)
            .html('<i class="fas fa-times-circle mr-1"></i> Ya, Decline & Kirim Email');
    });

    // =========================================================
    // RE-SYNC KE MAILCHIMP
    // =========================================================
    $(document).on('click', '.btn-import-mailchimp', function() {
        const $btn    = $(this);
        const url     = $btn.data('url');
        const userId  = $btn.data('user-id');
        const email   = $btn.data('email');
        const tags    = parseTags($btn.attr('data-tags'));
        const original = $btn.html();

        $btn.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span> Syncing...');

        $.ajax({ url, method: 'POST', dataType: 'json', data: { user_id: userId, email, tags } })
            .done(function(res) {
                if (res && res.success) {
                    $btn.html('<i class="fas fa-check"></i> Synced').addClass('btn-success').removeClass('btn-outline-secondary');
                    showAlert('success', res.message);
                } else {
                    $btn.prop('disabled', false).html(original);
                    showAlert('warning', (res && res.message) || 'Sync gagal.');
                }
            })
            .fail(function(xhr) {
                $btn.prop('disabled', false).html(original);
                showAlert('danger', xhr.responseJSON?.message || 'Gagal menghubungi server.');
            });
    });

    // =========================================================
    // UPDATE TIER
    // =========================================================
    $(document).on('change', '.user-tier-select', function() {
        const $select = $(this);
        const url     = $select.data('url');
        const tier    = $select.val();
        const $badge  = $select.closest('td').find('.tier-status');

        $badge.removeClass('badge-light badge-success badge-danger').addClass('badge-warning').text('Saving...');

        $.ajax({ url, method: 'POST', dataType: 'json', data: { tier } })
            .done(function(res) {
                if (res && res.success) {
                    $badge.removeClass('badge-warning').addClass('badge-success').text('Saved');
                } else {
                    $badge.removeClass('badge-warning').addClass('badge-danger').text('Failed');
                    showAlert('warning', (res && res.message) || 'Gagal update tier.');
                }
            })
            .fail(function(xhr) {
                $badge.removeClass('badge-warning').addClass('badge-danger').text('Failed');
                showAlert('danger', xhr.responseJSON?.message || 'Gagal menghubungi server.');
            });
    });

    // =========================================================
    // EDIT USER
    // =========================================================
    $(document).on('change', '#eu-company-category', function() {
        if ($(this).val() === 'other') {
            $('.eu-company-other-wrap').show();
        } else {
            $('.eu-company-other-wrap').hide();
            $('#eu-company-other').val('');
        }
    });

    function euFillCompanyFields(company) {
        $('#eu-prefix').val(company.prefix || '');
        if ($.fn.select2) $('#eu-prefix').trigger('change');
        $('#eu-company-name').val(company.company_name || '');
        $('#eu-company-website').val(company.company_website || '');
        $('#eu-company-category').val(company.company_category || '').trigger('change');
        $('#eu-company-other').val(company.company_other || '');
        $('#eu-address').val(company.address || '');
        $('#eu-city').val(company.city || '');
        $('#eu-portal-code').val(company.portal_code || '');
        $('#eu-country').val(company.country || '');
        var euFullOffice = company.full_office_number || '';
        $('#eu-full-office-number').val(euFullOffice);
        var euParsed = parseOfficeNumber(euFullOffice);
        $('#eu-prefix-office-number').val(company.prefix_office_number || euParsed.prefix);
        $('#eu-office-number').val(company.office_number || euParsed.office);
    }

    $(document).on('input', '#eu-full-office-number', function() {
        var p = parseOfficeNumber($(this).val());
        $('#eu-prefix-office-number').val(p.prefix);
        $('#eu-office-number').val(p.office);
    });

    var euSuggestTimeout = null;
    $(document).on('input', '#eu-company-name', function() {
        var q = $(this).val().trim();
        clearTimeout(euSuggestTimeout);
        $('#eu-company-suggestions').hide().empty();
        if (q.length < 2) return;

        euSuggestTimeout = setTimeout(function() {
            $.ajax({
                url: '{{ route('admin.company_database.verified_companies') }}',
                data: { q },
                success: function(data) {
                    var $box = $('#eu-company-suggestions');
                    $box.empty();
                    if (!data || data.length === 0) { $box.hide(); return; }
                    $.each(data, function(i, c) {
                        var $item = $('<button type="button" class="list-group-item list-group-item-action"></button>');
                        $item.html(
                            '<span class="badge badge-success badge-sm mr-1"><i class="fas fa-check-circle"></i> Verified</span> <strong>' +
                            $('<span>').text(c.company_name).html() + '</strong>'
                        );
                        $item.on('click', function() { euFillCompanyFields(c); $box.hide().empty(); });
                        $box.append($item);
                    });
                    $box.show();
                },
                error: function() { $('#eu-company-suggestions').hide().empty(); }
            });
        }, 300);
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#eu-company-name, #eu-company-suggestions').length) {
            $('#eu-company-suggestions').hide().empty();
        }
    });

    $(document).on('click', '.btn-edit-user', function() {
        const $btn = $(this);

        if ($.fn.select2 && !$('#eu-prefix').data('select2')) {
            $('#eu-prefix').select2({
                dropdownParent: $('#editUserModal'),
                width: '100%',
                placeholder: 'Select Prefix',
                allowClear: true
            });
        }

        $('#eu-user-id').val($btn.attr('data-user-id'));
        $('#eu-update-url').val($btn.attr('data-update-url'));
        $('#eu-name').val($btn.attr('data-name'));
        $('#eu-email').val($btn.attr('data-email'));
        $('#eu-job-title').val($btn.attr('data-job-title'));
        $('#eu-phone').val($btn.attr('data-phone'));
        $('#eu-prefix').val($btn.attr('data-prefix') || '');
        if ($.fn.select2) $('#eu-prefix').trigger('change');
        $('#eu-company-name').val($btn.attr('data-company-name'));
        $('#eu-company-website').val($btn.attr('data-company-website'));
        $('#eu-company-category').val($btn.attr('data-company-category') || '');
        $('#eu-company-other').val($btn.attr('data-company-other'));
        $('#eu-address').val($btn.attr('data-address'));
        $('#eu-city').val($btn.attr('data-city'));
        $('#eu-portal-code').val($btn.attr('data-portal-code'));
        $('#eu-country').val($btn.attr('data-country'));
        $('#eu-prefix-office-number').val($btn.attr('data-prefix-office-number'));
        $('#eu-office-number').val($btn.attr('data-office-number'));
        $('#eu-full-office-number').val($btn.attr('data-full-office-number'));
        $('#eu-company-category').trigger('change');
        $('#eu-company-suggestions').hide().empty();
        $('#eu-status-member').val($btn.attr('data-status-member') || '');
        $('#eu-tier').val($btn.attr('data-tier') || 'reguler');
        $('#eu-btn-save').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Perubahan');
        $('#editUserModal').modal('show');
    });

    $('#eu-btn-save').on('click', function() {
        const url   = $('#eu-update-url').val();
        const name  = $('#eu-name').val().trim();
        const email = $('#eu-email').val().trim();
        if (!url) return;
        if (!name || !email) { showAlert('warning', 'Nama dan email wajib diisi.'); return; }

        $(this).prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm mr-1"></span> Menyimpan...');

        $.ajax({
            url, method: 'POST', dataType: 'json',
            data: {
                name, email,
                job_title:            $('#eu-job-title').val(),
                phone:                $('#eu-phone').val(),
                prefix:               $('#eu-prefix').val(),
                company_name:         $('#eu-company-name').val(),
                company_website:      $('#eu-company-website').val(),
                company_category:     $('#eu-company-category').val(),
                company_other:        $('#eu-company-other').val(),
                address:              $('#eu-address').val(),
                city:                 $('#eu-city').val(),
                portal_code:          $('#eu-portal-code').val(),
                country:              $('#eu-country').val(),
                prefix_office_number: $('#eu-prefix-office-number').val(),
                office_number:        $('#eu-office-number').val(),
                full_office_number:   $('#eu-full-office-number').val(),
                status_member:        $('#eu-status-member').val(),
                tier:                 $('#eu-tier').val(),
            }
        })
        .done(function(res) {
            if (res && res.success) {
                $('#editUserModal').modal('hide');
                const changed = res.changes && Object.keys(res.changes).length > 0
                    ? ' (field diubah: ' + Object.keys(res.changes).join(', ') + ')'
                    : '';
                showAlert('success', '<i class="fas fa-check-circle mr-1"></i>' + res.message + changed);
                setTimeout(function() { location.reload(); }, 1500);
            } else {
                $('#eu-btn-save').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Perubahan');
                showAlert('warning', (res && res.message) || 'Gagal menyimpan.');
            }
        })
        .fail(function(xhr) {
            $('#eu-btn-save').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Perubahan');
            showAlert('danger', xhr.responseJSON?.message || 'Gagal menghubungi server.');
        });
    });

    // =========================================================
    // VIEW EDIT LOGS
    // =========================================================
    $(document).on('click', '.btn-view-logs', function() {
        const url  = $(this).attr('data-logs-url');
        const name = $(this).attr('data-name');

        $('#logs-user-name').text(name);
        $('#logs-loading').show();
        $('#logs-empty').hide();
        $('#logs-content').hide();
        $('#logs-tbody').empty();
        $('#userLogsModal').modal('show');

        const fieldLabels = {
            name: 'Nama', email: 'Email', job_title: 'Job Title', phone: 'Phone',
            prefix: 'Prefix', company_name: 'Company Name', company_website: 'Company Website',
            company_category: 'Company Category', company_other: 'Company Other',
            address: 'Address', city: 'City', portal_code: 'Postal Code', country: 'Country',
            prefix_office_number: 'Prefix Office Number', office_number: 'Office Number',
            full_office_number: 'Full Office Number', is_verified: 'Company Verified',
            status_member: 'Status Member', tier: 'Tier'
        };

        $.ajax({ url, method: 'GET', dataType: 'json' })
            .done(function(data) {
                $('#logs-loading').hide();
                if (!data || data.length === 0) { $('#logs-empty').show(); return; }

                $.each(data, function(i, log) {
                    const time  = log.created_at || '-';
                    const admin = $('<span>').text(log.admin_name || '-').html();
                    $.each(log.changes || {}, function(field, diff) {
                        const label  = fieldLabels[field] || field;
                        const oldVal = $('<span>').text(diff.old || '-').html();
                        const newVal = $('<span>').text(diff.new || '-').html();
                        $('#logs-tbody').append(
                            `<tr>
                                <td class="text-nowrap"><small>${time}</small></td>
                                <td><small>${admin}</small></td>
                                <td><span class="badge badge-light">${label}</span></td>
                                <td><small class="text-danger">${oldVal}</small></td>
                                <td><small class="text-success">${newVal}</small></td>
                            </tr>`
                        );
                    });
                });

                $('#logs-content').show();
            })
            .fail(function() {
                $('#logs-loading').hide();
                $('#logs-empty').text('Gagal memuat log.').show();
            });
    });

    // =========================================================
    // MAILCHIMP CONTACT COUNT
    // =========================================================
    (function fetchMcCount() {
        $.ajax({ url: '{{ route('users.mailchimp.count') }}', method: 'GET', dataType: 'json', timeout: 12000 })
            .done(function(res) {
                if (res && res.success && res.count !== null) {
                    $('#mc-contact-count').text(Number(res.count).toLocaleString());
                } else {
                    $('#mc-contact-count').html('<span class="text-muted small">N/A</span>');
                }
            })
            .fail(function() {
                $('#mc-contact-count').html('<span class="text-muted small">N/A</span>');
            });
    })();

    // =========================================================
    // DEACTIVATE / REACTIVATE MEMBER
    // =========================================================
    $(document).on('click', '.btn-deactivate-member', function() {
        var btn  = $(this);
        var name = btn.data('name');
        if (!confirm('Deactivate "' + name + '"? Member tidak bisa login dan mendapat harga nonmember.')) return;

        $.ajax({
            url: btn.data('url'),
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    location.reload();
                } else {
                    toastr.error(res.message || 'Gagal deactivate.');
                }
            },
            error: function() { toastr.error('Terjadi kesalahan.'); }
        });
    });

    $(document).on('click', '.btn-reactivate-member', function() {
        var btn  = $(this);
        var name = btn.data('name');
        if (!confirm('Reactivate "' + name + '"? Member akan kembali aktif.')) return;

        $.ajax({
            url: btn.data('url'),
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    location.reload();
                } else {
                    toastr.error(res.message || 'Gagal reactivate.');
                }
            },
            error: function() { toastr.error('Terjadi kesalahan.'); }
        });
    });

    // DataTable
    $(document).ready(function() {
        $('#laravel_crud').DataTable({
            dom: 'Bfrtip',
            pageLength: 25,
            buttons: ['copyHtml5', 'excelHtml5', 'csvHtml5', 'pdfHtml5'],
            order: [[0, 'asc']],
        });
    });
</script>
@endpush
