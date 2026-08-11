@push('bottom')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    $('[data-toggle="tooltip"]').tooltip();

    // =========================================================
    // ASSIGN PIC
    // =========================================================
    $(document).on('click', '.btn-open-assign-pic-modal', function() {
        var btn = $(this);
        $('#ap-btn-submit').data('assign-url', btn.data('assign-url'));
        $('#ap-member-name').text(btn.data('member-name') || '-');
        $('#ap-pic-id').val(btn.data('pic-id') || '');
        $('#assignPicModal').modal('show');
    });

    $(document).on('click', '#ap-btn-submit', function() {
        var btn = $(this);
        var picId = $('#ap-pic-id').val();
        if (!picId) {
            toastr.error('Pilih PIC terlebih dahulu.');
            return;
        }

        btn.prop('disabled', true);
        $.ajax({
            url: btn.data('assign-url'),
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', pic_id: picId },
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    location.reload();
                } else {
                    toastr.error(res.message || 'Gagal assign PIC.');
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
    // LOG FOLLOW UP
    // =========================================================
    $(document).on('click', '.btn-open-follow-up-log-modal', function() {
        var btn = $(this);
        $('#fl-btn-submit').data('log-url', btn.data('log-url'));
        $('#fl-member-name').text(btn.data('member-name') || '-');
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
