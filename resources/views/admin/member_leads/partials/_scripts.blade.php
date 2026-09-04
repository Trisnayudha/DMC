@push('bottom')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    $('[data-toggle="tooltip"]').tooltip();

    // =========================================================
    // LOG FOLLOW UP (Kirim Sponsorkit / Follow Up 1 / Follow Up 2)
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
        $('#fl-step-label').text(btn.data('step-label') || 'Follow Up');
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
                    toastr.error(res.message || 'Gagal mencatat follow up.');
                    btn.prop('disabled', false);
                }
            },
            error: function(xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Terjadi kesalahan.');
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
        if (!confirm('Tandai lead ini sebagai ' + result.toUpperCase() + '?')) return;

        $.ajax({
            url: btn.data('url'),
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', result: result },
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    location.reload();
                } else {
                    toastr.error(res.message || 'Gagal memperbarui hasil.');
                }
            },
            error: function(xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Terjadi kesalahan.');
            }
        });
    });
</script>
@endpush
