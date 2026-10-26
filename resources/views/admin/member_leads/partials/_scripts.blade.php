@push('bottom')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    $('[data-toggle="tooltip"]').tooltip();

    // Filters: dropdowns apply immediately (search / date still use Apply or Enter)
    $(document).on('change', '.lead-auto-submit', function() {
        $('#lead-filter-form').submit();
    });

    // =========================================================
    // LOG FOLLOW-UP (Sponsor Kit Sent / Follow-up 1 / Follow-up 2)
    // =========================================================
    $(document).on('click', '.btn-open-follow-up-log-modal', function() {
        var btn = $(this);

        // datetime-local needs "YYYY-MM-DDTHH:mm" in LOCAL time — toISOString()
        // would shift to UTC and show the wrong hour, so build it by hand.
        var now = new Date();
        var pad = function(n) { return String(n).padStart(2, '0'); };
        var nowLocal = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate())
            + 'T' + pad(now.getHours()) + ':' + pad(now.getMinutes());

        $('#fl-btn-submit').data('log-url', btn.data('log-url'));
        $('#fl-step-label').text(btn.data('step-label') || 'Follow-up');
        $('#fl-member-name').text(btn.data('member-name') || '-');
        $('#fl-date').val(nowLocal);
        $('#fl-channel').val(btn.data('channel') || '');
        $('#fl-notes').val('');
        $('#followUpLogModal').modal('show');
    });

    $(document).on('click', '#fl-btn-submit', function() {
        var btn = $(this);
        btn.prop('disabled', true);
        $.ajax({
            url: btn.data('log-url'),
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                date: $('#fl-date').val(),
                channel: $('#fl-channel').val(),
                notes: $('#fl-notes').val()
            },
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    location.reload();
                } else {
                    toastr.error(res.message || 'Failed to log follow-up.');
                    btn.prop('disabled', false);
                }
            },
            error: function(xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'An error occurred.');
                btn.prop('disabled', false);
            }
        });
    });

    // =========================================================
    // NOTES HISTORY (per step, e.g. prospect's reply)
    // =========================================================
    var notesChanged = false;

    function escapeHtml(text) {
        return $('<div>').text(text == null ? '' : text).html();
    }

    function loadLeadNotes(url) {
        var list = $('#ln-list');
        list.html('<div class="text-muted small">Loading...</div>');
        $.get(url, function(res) {
            if (!res.notes.length) {
                list.html('<div class="text-muted small">No notes yet.</div>');
                return;
            }
            list.html(res.notes.map(function(n) {
                return '<div class="border rounded p-2 mb-2">'
                    + '<div class="small text-muted mb-1">'
                    + '<span class="badge badge-light border mr-1">' + escapeHtml(n.step_label) + '</span>'
                    + escapeHtml(n.at) + ' · ' + escapeHtml(n.by)
                    + '</div>'
                    + '<div style="white-space:pre-wrap;">' + escapeHtml(n.note) + '</div>'
                    + '</div>';
            }).join(''));
        }).fail(function() {
            list.html('<div class="text-danger small">Failed to load notes.</div>');
        });
    }

    $(document).on('click', '.btn-open-notes-modal', function(e) {
        e.preventDefault();
        var btn = $(this);
        $('#ln-btn-submit').data('store-url', btn.data('store-url')).data('list-url', btn.data('list-url'));
        $('#ln-member-name').text(btn.data('member-name') || '-');
        $('#ln-step').val(btn.data('default-step') || '');
        $('#ln-note').val('');
        notesChanged = false;
        loadLeadNotes(btn.data('list-url'));
        $('#leadNotesModal').modal('show');
    });

    $('#leadNotesModal').on('hidden.bs.modal', function() {
        if (notesChanged) location.reload();
    });

    $(document).on('click', '#ln-btn-submit', function() {
        var btn = $(this);
        var note = $('#ln-note').val().trim();
        if (note === '') {
            toastr.warning('Please write the note first.');
            return;
        }
        btn.prop('disabled', true);
        $.ajax({
            url: btn.data('store-url'),
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', step: $('#ln-step').val(), note: note },
            success: function(res) {
                toastr.success(res.message);
                notesChanged = true;
                $('#ln-note').val('');
                loadLeadNotes(btn.data('list-url'));
            },
            error: function(xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'An error occurred.');
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    });

    // =========================================================
    // LEAD TEMPERATURE (SOP scoring checklist)
    // =========================================================
    function tempLevel(score) {
        return score >= 70 ? 'hot' : (score >= 40 ? 'warm' : 'cold');
    }

    function renderTempSummary() {
        var score = 0;
        $('#lt-items input:checked').each(function() { score += parseInt($(this).data('points'), 10); });
        var level = tempLevel(score);
        $('#lt-score').text(score);
        $('#lt-badge').attr('class', 'badge temp-badge temp-' + level).css('font-size', '12px').text(level.toUpperCase());
    }

    function renderTempItems(res) {
        $('#lt-items').html(res.items.map(function(item) {
            var id = 'lt-' + item.field;
            return '<div class="custom-control custom-checkbox mb-2">'
                + '<input type="checkbox" class="custom-control-input" id="' + id + '" value="' + item.field + '"'
                + ' data-points="' + item.points + '"'
                + (item.checked ? ' checked' : '') + (item.locked ? ' disabled' : '') + '>'
                + '<label class="custom-control-label" for="' + id + '">'
                + '<strong>' + item.points + '</strong> ' + escapeHtml(item.label)
                + (item.auto ? ' <span class="badge badge-light border ml-1">auto</span>' : '')
                + '</label></div>';
        }).join(''));
        renderTempSummary();
    }

    $(document).on('change', '#lt-items input', renderTempSummary);

    $(document).on('click', '.btn-open-temperature-modal', function(e) {
        e.preventDefault();
        var btn = $(this);
        $('#lt-btn-submit').data('save-url', btn.data('save-url')).prop('disabled', true);
        $('#lt-member-name').text(btn.data('member-name') || '-');
        $('#lt-items').html('<div class="text-muted small">Loading...</div>');
        $('#leadTemperatureModal').modal('show');
        $.get(btn.data('get-url'), function(res) {
            renderTempItems(res);
            $('#lt-btn-submit').prop('disabled', false);
        }).fail(function() {
            $('#lt-items').html('<div class="text-danger small">Failed to load.</div>');
        });
    });

    $(document).on('click', '#lt-btn-submit', function() {
        var btn = $(this);
        var checked = $('#lt-items input:checked').map(function() { return this.value; }).get();
        btn.prop('disabled', true);
        $.ajax({
            url: btn.data('save-url'),
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', checked: checked },
            success: function(res) {
                toastr.success(res.message);
                location.reload();
            },
            error: function(xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'An error occurred.');
                btn.prop('disabled', false);
            }
        });
    });

    // =========================================================
    // BULK ACTIONS (ticked rows)
    // =========================================================
    function bulkIds() {
        return $('.bulk-select:checked').map(function() { return this.value; }).get();
    }

    function refreshBulkBar() {
        var count = bulkIds().length;
        $('#bulk-count, .bulk-count-text').text(count);
        $('#bulk-bar').toggleClass('has-selection', count > 0);
        $('#bulk-hint').toggleClass('d-none', count > 0);
        $('#bulk-selected, #bulk-clear').toggleClass('d-none', count === 0);
        $('#bulk-btn-follow-up, #bulk-btn-temperature').prop('disabled', count === 0);
        $('#bulk-select-all').prop('checked', count > 0 && count === $('.bulk-select').length);
    }

    $(document).on('change', '.bulk-select', refreshBulkBar);

    $(document).on('change', '#bulk-select-all', function() {
        $('.bulk-select').prop('checked', this.checked);
        refreshBulkBar();
    });

    $(document).on('click', '#bulk-clear', function() {
        $('.bulk-select, #bulk-select-all').prop('checked', false);
        refreshBulkBar();
    });

    function postBulk(btn, url, data) {
        btn.prop('disabled', true);
        $.ajax({
            url: url,
            method: 'POST',
            data: $.extend({ _token: '{{ csrf_token() }}', ids: bulkIds() }, data),
            success: function(res) {
                toastr.success(res.message);
                setTimeout(function() { location.reload(); }, 1200);
            },
            error: function(xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'An error occurred.');
                btn.prop('disabled', false);
            }
        });
    }

    $('#bulkFollowUpModal').on('show.bs.modal', function() {
        var now = new Date();
        var pad = function(n) { return String(n).padStart(2, '0'); };
        $('#bf-date').val(now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate())
            + 'T' + pad(now.getHours()) + ':' + pad(now.getMinutes()));
        $('#bf-step, #bf-channel, #bf-notes').val('');
        $('#bf-btn-submit').prop('disabled', false);
    });

    $(document).on('click', '#bf-btn-submit', function() {
        postBulk($(this), '{{ route('admin.member_leads.bulk_follow_up') }}', {
            step: $('#bf-step').val(),
            date: $('#bf-date').val(),
            channel: $('#bf-channel').val(),
            notes: $('#bf-notes').val()
        });
    });

    $(document).on('click', '.btn-open-bulk-temperature', function() {
        $('.bt-change').val('');
        $('#bt-btn-submit').prop('disabled', false);
        $('#bulkTemperatureModal').modal('show');
    });

    $(document).on('click', '#bt-btn-submit', function() {
        var changes = {};
        $('.bt-change').each(function() {
            if ($(this).val() !== '') changes[$(this).data('field')] = $(this).val();
        });
        if ($.isEmptyObject(changes)) {
            toastr.warning('Pick at least one category to change.');
            return;
        }
        postBulk($(this), '{{ route('admin.member_leads.bulk_temperature') }}', { changes: changes });
    });

    // =========================================================
    // EDIT CHANNEL
    // =========================================================
    $(document).on('click', '.btn-open-channel-modal', function(e) {
        e.preventDefault();
        var btn = $(this);
        $('#lc-btn-submit').data('url', btn.data('url'));
        $('#lc-member-name').text(btn.data('member-name') || '-');
        $('#lc-channel').val(btn.data('channel') || '');
        $('#leadChannelModal').modal('show');
    });

    $(document).on('click', '#lc-btn-submit', function() {
        var btn = $(this);
        btn.prop('disabled', true);
        $.ajax({
            url: btn.data('url'),
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', channel: $('#lc-channel').val() },
            success: function(res) {
                toastr.success(res.message);
                location.reload();
            },
            error: function(xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'An error occurred.');
                btn.prop('disabled', false);
            }
        });
    });

    // =========================================================
    // ADD MEMBER TO LEAD
    // =========================================================
    $('#addLeadModal').on('shown.bs.modal', function() {
        if (!$('#al-user').data('select2')) {
            $('#al-user').select2({
                dropdownParent: $('#addLeadModal'),
                placeholder: 'Search member...',
                minimumInputLength: 2,
                ajax: {
                    url: '{{ route('admin.member_leads.search_members') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) { return { q: params.term }; },
                    processResults: function(data) { return { results: data.results }; }
                }
            });
        }
    });

    $('#addLeadModal').on('hidden.bs.modal', function() {
        $('#al-user').val(null).trigger('change');
        $('#al-notes').val('');
        $('#al-btn-submit').prop('disabled', false);
    });

    $(document).on('click', '#al-btn-submit', function() {
        var btn = $(this);
        var userId = $('#al-user').val();
        if (!userId) {
            toastr.error('Pilih member dulu.');
            return;
        }
        btn.prop('disabled', true);
        $.ajax({
            url: '{{ route('admin.member_leads.store') }}',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', user_id: userId, notes: $('#al-notes').val() },
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    location.reload();
                } else {
                    toastr.error(res.message || 'Gagal menambahkan lead.');
                    btn.prop('disabled', false);
                }
            },
            error: function(xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'An error occurred.');
                btn.prop('disabled', false);
            }
        });
    });

    // =========================================================
    // MARK RESULT (Win / Loss)
    // =========================================================
    $(document).on('click', '.btn-mark-lead-result', function() {
        var btn = $(this);
        var result = btn.data('result');
        if (!confirm('Mark this lead as ' + result.toUpperCase() + '?')) return;

        $.ajax({
            url: btn.data('url'),
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', result: result },
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    location.reload();
                } else {
                    toastr.error(res.message || 'Failed to update result.');
                }
            },
            error: function(xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'An error occurred.');
            }
        });
    });

    // =========================================================
    // DO NOT SEND (flag lead as ineligible for sponsor kit)
    // =========================================================
    $(document).on('click', '.btn-open-do-not-send-modal', function() {
        var btn = $(this);
        $('#dns-btn-submit').data('url', btn.data('url'));
        $('#dns-member-name').text(btn.data('member-name') || '-');
        $('#dns-reason-category').val('Competitor');
        $('#dns-reason-other').val('');
        $('#dns-reason-other-wrap').hide();
        $('#doNotSendModal').modal('show');
    });

    $(document).on('change', '#dns-reason-category', function() {
        $('#dns-reason-other-wrap').toggle($(this).val() === 'Other');
    });

    $(document).on('click', '#dns-btn-submit', function() {
        var btn = $(this);
        var category = $('#dns-reason-category').val();
        var reason = category === 'Other' ? $('#dns-reason-other').val().trim() : category;

        if (category === 'Other' && reason === '') {
            toastr.warning('Please provide the reason details first.');
            return;
        }

        btn.prop('disabled', true);
        $.ajax({
            url: btn.data('url'),
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', do_not_send: 1, reason: reason },
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    location.reload();
                } else {
                    toastr.error(res.message || 'Failed to flag lead.');
                    btn.prop('disabled', false);
                }
            },
            error: function(xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'An error occurred.');
                btn.prop('disabled', false);
            }
        });
    });

    $(document).on('click', '.btn-toggle-do-not-send', function() {
        var btn = $(this);
        if (!confirm('Remove the "Do Not Send" flag for this lead?')) return;

        $.ajax({
            url: btn.data('url'),
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', do_not_send: 0 },
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    location.reload();
                } else {
                    toastr.error(res.message || 'Failed to remove flag.');
                }
            },
            error: function(xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'An error occurred.');
            }
        });
    });
</script>
@endpush
