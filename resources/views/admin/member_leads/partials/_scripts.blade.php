@push('bottom')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    $('[data-toggle="tooltip"]').tooltip();

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
        $('#fl-notes').val(btn.data('notes') || '');
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
