@push('bottom')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    $(document).on('click', '.btn-verify-follow-up', function() {
        var btn = $(this);
        if (!confirm('Tandai data "' + btn.data('name') + '" sebagai Update Verified?')) return;

        $.ajax({
            url: btn.data('url'),
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    location.reload();
                } else {
                    toastr.error(res.message || 'Gagal verifikasi.');
                }
            },
            error: function() { toastr.error('Terjadi kesalahan.'); }
        });
    });
</script>
@endpush
