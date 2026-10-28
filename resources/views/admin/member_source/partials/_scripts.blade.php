@push('bottom')
<script>
    $('[data-toggle="tooltip"]').tooltip();

    var $msForm      = $('#member-source-form');
    var msStoreUrl   = '{{ route('admin.member_sources.store') }}';
    var msFrontendUrl = @json($frontendUrl);

    function updateLinkPreview() {
        var category = ($('#ms-category').val() || '').trim().toUpperCase();
        var code = ($('#ms-code').val() || '').trim().toUpperCase();
        $('#ms-link-preview').text(msFrontendUrl + '/member/' + (category || '…') + '/' + (code || '…'));
    }

    // Category/Code yang sudah dipakai pendaftar dikunci (readonly, bukan disabled,
    // supaya nilainya tetap ikut terkirim dan lolos validasi).
    function setKeyLocked(locked) {
        $('#ms-category, #ms-code').prop('readonly', locked);
        $('#ms-key-hint').toggleClass('d-none', locked);
        $('#ms-key-locked').toggleClass('d-none', !locked).toggleClass('d-block', locked);
    }

    function openSourceModal(data) {
        var isEdit = !!data.updateUrl;

        $msForm.attr('action', isEdit ? data.updateUrl : msStoreUrl);
        $('#ms-update-url').val(isEdit ? data.updateUrl : '');
        $('#member-source-modal-title').html(isEdit
            ? '<i class="fas fa-edit mr-1"></i>Edit Source'
            : '<i class="fas fa-link mr-1"></i>Tambah Source');

        $('#ms-category').val(data.category || '');
        $('#ms-code').val(data.code || '');
        $('#ms-name').val(data.name || '');
        $('#ms-form-type').val(data.formType || 'individual');
        $('#ms-active').prop('checked', data.active === undefined ? true : !!data.active);
        setKeyLocked(!!data.locked);
        updateLinkPreview();

        $('#memberSourceModal').modal('show');
    }

    $('#btn-add-source').on('click', function () {
        openSourceModal({});
    });

    $(document).on('click', '.btn-edit-source', function () {
        var $btn = $(this);
        openSourceModal({
            updateUrl: $btn.data('update-url'),
            category: $btn.data('category'),
            code: $btn.data('code'),
            name: $btn.data('name'),
            formType: $btn.data('form-type'),
            active: parseInt($btn.data('active'), 10) === 1,
            locked: parseInt($btn.data('locked'), 10) === 1
        });
    });

    $('#ms-category, #ms-code').on('input', updateLinkPreview);

    $(document).on('click', '.js-copy-link', function () {
        var $btn = $(this);
        var link = $btn.data('link');

        var done = function () {
            $btn.attr('data-original-title', 'Copied!').tooltip('show');
            setTimeout(function () {
                $btn.attr('data-original-title', 'Copy link').tooltip('hide');
            }, 1200);
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(link).then(done);
            return;
        }

        var $tmp = $('<textarea>').val(link).appendTo('body').select();
        document.execCommand('copy');
        $tmp.remove();
        done();
    });

    @if ($errors->any())
        // Validasi gagal: buka lagi modal dengan input sebelumnya.
        openSourceModal({
            updateUrl: @json(old('_update_url')),
            category: @json(old('category')),
            code: @json(old('code')),
            name: @json(old('name')),
            formType: @json(old('form_type')),
            active: @json((bool) old('is_active'))
        });
    @endif
</script>
@endpush
