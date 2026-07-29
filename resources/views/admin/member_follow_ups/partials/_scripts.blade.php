@push('bottom')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
(function () {

    // ──────────────────────────────────────────────────────────────────────
    //  Open Verify Modal
    // ──────────────────────────────────────────────────────────────────────
    $(document).on('click', '.btn-open-verify-modal', function () {
        var btn = $(this);
        $('#vfu-follow-up-id').val(btn.data('follow-up-id'));
        $('#vfu-btn-submit').data('verify-url', btn.data('verify-url'));
        $('#vfu-member-name').text(btn.data('member-name') || '-');
        $('#vfu-new-company').val(btn.data('new-company') || '');
        $('#vfu-new-job-title').val(btn.data('member-job-title') || '');
        $('#vfu-new-email').val(btn.data('member-email') || '');
        $('#verifyFollowUpModal').modal('show');
    });

    // ──────────────────────────────────────────────────────────────────────
    //  Submit Verify Modal
    // ──────────────────────────────────────────────────────────────────────
    $(document).on('click', '#vfu-btn-submit', function () {
        var btn         = $(this);
        var verifyUrl   = btn.data('verify-url');
        var newCompany  = $('#vfu-new-company').val().trim();
        var newJobTitle = $('#vfu-new-job-title').val().trim();
        var newEmail    = $('#vfu-new-email').val().trim();

        if (!newCompany) {
            toastr.error('Company terbaru wajib diisi.');
            return;
        }

        if (!confirm(
            'Konfirmasi:\n\n' +
            '• Akun LAMA akan dinonaktifkan (deactivated)\n' +
            '• Akun BARU akan dibuat dengan company: "' + newCompany + '"\n' +
            '• Phone number dipindahkan ke akun baru\n\n' +
            'Lanjutkan?'
        )) return;

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');

        $.ajax({
            url:    verifyUrl,
            method: 'POST',
            data: {
                _token:          '{{ csrf_token() }}',
                new_company_name: newCompany,
                new_job_title:   newJobTitle,
                new_email:       newEmail,
            },
            success: function (res) {
                if (res.success) {
                    toastr.success(res.message);
                    $('#verifyFollowUpModal').modal('hide');
                    setTimeout(function () { location.reload(); }, 1200);
                } else {
                    toastr.error(res.message || 'Gagal melakukan verifikasi.');
                    btn.prop('disabled', false).html('<i class="fas fa-user-check mr-1"></i> Verify & Buat Akun Baru');
                }
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Terjadi kesalahan server.';
                toastr.error(msg);
                btn.prop('disabled', false).html('<i class="fas fa-user-check mr-1"></i> Verify & Buat Akun Baru');
            }
        });
    });

    // Reset modal state on close
    $('#verifyFollowUpModal').on('hidden.bs.modal', function () {
        $('#vfu-btn-submit')
            .prop('disabled', false)
            .html('<i class="fas fa-user-check mr-1"></i> Verify & Buat Akun Baru');
    });

})();
</script>
@endpush
