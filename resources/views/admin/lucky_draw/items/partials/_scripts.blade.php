@push('bottom')
<script>
    $('[data-toggle="tooltip"]').tooltip();

    var $ldiForm  = $('#lucky-draw-item-form');
    var ldiStoreUrl = '{{ route('admin.lucky_draw.items.store') }}';

    function resetLuckyDrawItemModal() {
        $ldiForm.attr('action', ldiStoreUrl);
        $('#lucky-draw-item-modal-title').html('<i class="fas fa-gift mr-1"></i>Add Prize');
        $('#ldi-name').val('');
        $('#ldi-chance').val('');
        $('#ldi-sort').val(0);
        $('#ldi-active').prop('checked', true);
        $('#ldi-image').val('');
        $('#ldi-image-preview').attr('src', '').hide();
    }

    $('#btn-add-item').on('click', function () {
        resetLuckyDrawItemModal();
        $('#luckyDrawItemModal').modal('show');
    });

    $(document).on('click', '.btn-edit-item', function () {
        var btn = $(this);
        resetLuckyDrawItemModal();

        $ldiForm.attr('action', btn.data('update-url'));
        $('#lucky-draw-item-modal-title').html('<i class="fas fa-gift mr-1"></i>Edit Prize');
        $('#ldi-name').val(btn.data('name'));
        $('#ldi-chance').val(btn.data('chance'));
        $('#ldi-sort').val(btn.data('sort'));
        $('#ldi-active').prop('checked', btn.data('active') == 1);

        var image = btn.data('image');
        if (image) {
            $('#ldi-image-preview').attr('src', image).show();
        }

        $('#luckyDrawItemModal').modal('show');
    });
</script>
@endpush
