@push('bottom')
<script>
    $('[data-toggle="tooltip"]').tooltip();

    var $ldiForm    = $('#lucky-draw-item-form');
    var ldiStoreUrl = '{{ route('admin.lucky_draw.items.store') }}';
    var quickBatchUrl = $('#lucky-draw-budget-card').data('quick-batch-url');

    // Setup AJAX CSRF Token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Helper: Toast Notifikasi Ringan
    function showToast(icon, title) {
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2200,
                timerProgressBar: true
            });
            Toast.fire({ icon: icon, title: title });
        }
    }

    // Helper: Update Realtime Indikator Kuota & Progress Bar
    function updateBudgetUI(used, remaining) {
        used = parseFloat(used) || 0;
        remaining = parseFloat(remaining) || 0;

        var usedClean = (Math.round(used * 100) / 100).toString();
        var remClean  = (Math.round(remaining * 100) / 100).toString();

        $('#val-used-chance').text(usedClean + '%');
        $('#val-remaining-chance').text(remClean + '%');

        var pctUsed = Math.min(100, Math.max(0, used));
        var pctRem  = Math.min(100, Math.max(0, remaining));

        $('#progress-bar-used').css('width', pctUsed + '%').attr('aria-valuenow', pctUsed);
        $('#progress-bar-remaining').css('width', pctRem + '%').attr('aria-valuenow', pctRem);

        if (used >= 100) {
            $('#badge-used-chance').removeClass('badge-primary badge-warning').addClass('badge-success');
            $('#progress-bar-used').removeClass('bg-primary bg-danger').addClass('bg-success');
            $('#budget-status-note').html('<span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i>Pasti keluar hadiah (ZONK 0%)</span>');
        } else if (used > 100) {
            $('#badge-used-chance').removeClass('badge-primary badge-success').addClass('badge-danger');
            $('#progress-bar-used').removeClass('bg-primary bg-success').addClass('bg-danger');
            $('#budget-status-note').html('<span class="text-danger font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i>Peluang > 100%! Harap kurangi</span>');
        } else {
            $('#badge-used-chance').removeClass('badge-success badge-danger').addClass('badge-primary');
            $('#progress-bar-used').removeClass('bg-success bg-danger').addClass('bg-primary');
            $('#budget-status-note').html('<span><i class="fas fa-info-circle mr-1"></i>Sisa ' + remClean + '% menjadi peluang tanpa hadiah (Zonk)</span>');
        }
    }

    // Helper: Sinkronisasi tampilan angka di mobile card & desktop row
    function syncChanceValueDisplay(itemId, chanceVal) {
        var cleanVal = (Math.round(parseFloat(chanceVal) * 100) / 100).toString() + '%';
        $('#mobile-chance-badge-' + itemId).text(cleanVal);
        $('#desktop-chance-val-' + itemId).text(cleanVal);
    }

    // Modal Reset & Tambah
    function resetLuckyDrawItemModal() {
        $ldiForm.attr('action', ldiStoreUrl);
        $('#lucky-draw-item-modal-title').html('<i class="fas fa-gift mr-1"></i>Tambah Hadiah');
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

    // Modal Edit Detail
    $(document).on('click', '.btn-edit-item', function () {
        var btn = $(this);
        resetLuckyDrawItemModal();

        $ldiForm.attr('action', btn.data('update-url'));
        $('#lucky-draw-item-modal-title').html('<i class="fas fa-gift mr-1"></i>Edit Detail Hadiah');
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

    // ================================================================
    // KONTROL CEPAT MOBILE & DESKTOP (AJAX)
    // ================================================================

    // 1. Toggle ON/OFF (Switch Aktif/Nonaktif)
    $(document).on('change', '.js-toggle-active', function () {
        var checkbox = $(this);
        var itemId   = checkbox.data('id');
        var isActive = checkbox.is(':checked');
        var card     = $('#mobile-card-' + itemId);
        var row      = $('#desktop-row-' + itemId);
        var quickUrl = card.data('quick-url');

        checkbox.prop('disabled', true);

        $.ajax({
            url: quickUrl,
            type: 'POST',
            data: { is_active: isActive ? 1 : 0 },
            success: function (res) {
                checkbox.prop('disabled', false);
                if (isActive) {
                    card.removeClass('is-inactive');
                    row.removeClass('table-light text-muted');
                    $('#mobile-status-pill-' + itemId).removeClass('badge-secondary').addClass('badge-success').text('Aktif');
                    $('#desktop-badge-' + itemId).removeClass('badge-secondary').addClass('badge-success').text('Aktif');
                } else {
                    card.addClass('is-inactive');
                    row.addClass('table-light text-muted');
                    $('#mobile-status-pill-' + itemId).removeClass('badge-success').addClass('badge-secondary').text('Nonaktif');
                    $('#desktop-badge-' + itemId).removeClass('badge-success').addClass('badge-secondary').text('Nonaktif');
                }
                updateBudgetUI(res.used, res.remaining);
                showToast('success', res.message);
            },
            error: function (xhr) {
                checkbox.prop('disabled', false);
                checkbox.prop('checked', !isActive); // Kembalikan ke posisi semula jika gagal
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal mengubah status.';
                showToast('error', msg);
            }
        });
    });

    // Debounce timer map untuk update persentase
    var chanceUpdateTimers = {};

    function triggerQuickChanceUpdate(itemId, newChance) {
        var card     = $('#mobile-card-' + itemId);
        var quickUrl = card.data('quick-url');
        var input    = $('#input-chance-' + itemId);

        if (chanceUpdateTimers[itemId]) {
            clearTimeout(chanceUpdateTimers[itemId]);
        }

        chanceUpdateTimers[itemId] = setTimeout(function () {
            $.ajax({
                url: quickUrl,
                type: 'POST',
                data: { chance_percent: newChance },
                success: function (res) {
                    syncChanceValueDisplay(itemId, newChance);
                    updateBudgetUI(res.used, res.remaining);
                    showToast('success', res.message);
                },
                error: function (xhr) {
                    var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Peluang gagal disimpan.';
                    showToast('error', msg);
                    // Kembalikan ke sisa kuota maksimum bila tersedia
                    if (xhr.responseJSON && xhr.responseJSON.remaining !== undefined) {
                        var maxAllowed = xhr.responseJSON.remaining;
                        input.val(maxAllowed);
                        syncChanceValueDisplay(itemId, maxAllowed);
                        updateBudgetUI(xhr.responseJSON.used, xhr.responseJSON.remaining);
                    }
                }
            });
        }, 400); // 400ms debounce
    }

    // 2. Stepper Tombol [-5%] dan [+5%]
    $(document).on('click', '.js-btn-step', function () {
        var btn    = $(this);
        var itemId = btn.data('id');
        var step   = parseFloat(btn.data('step')) || 0;
        var input  = $('#input-chance-' + itemId);

        var currentVal = parseFloat(input.val()) || 0;
        var nextVal    = Math.max(0, Math.min(100, Math.round((currentVal + step) * 100) / 100));

        input.val(nextVal);
        syncChanceValueDisplay(itemId, nextVal);
        triggerQuickChanceUpdate(itemId, nextVal);
    });

    // 3. Preset Buttons (0%, 10%, 25%, 50%)
    $(document).on('click', '.js-btn-preset', function () {
        var btn    = $(this);
        var itemId = btn.data('id');
        var val    = parseFloat(btn.data('val')) || 0;
        var input  = $('#input-chance-' + itemId);

        input.val(val);
        syncChanceValueDisplay(itemId, val);
        triggerQuickChanceUpdate(itemId, val);
    });

    // 4. Input Manual di Kolom Angka (on input debounce)
    $(document).on('input', '.js-chance-input', function () {
        var input  = $(this);
        var itemId = input.data('id');
        var val    = parseFloat(input.val());

        if (isNaN(val) || val < 0) val = 0;
        if (val > 100) val = 100;

        syncChanceValueDisplay(itemId, val);
        triggerQuickChanceUpdate(itemId, val);
    });

    // 5. Tombol "100% Pasti Keluar" (Force Win)
    $(document).on('click', '.js-force-win-btn', function () {
        var btn      = $(this);
        var itemId   = btn.data('id');
        var itemName = btn.data('name');

        var confirmText = 'Setel "' + itemName + '" menjadi 100% keluar? Semua hadiah lain akan dinolkan (0%) agar hadiah ini PASTI keluar pada putaran berikutnya.';

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Mode Pasti Keluar (100%)?',
                text: confirmText,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Jadikan 100%!',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (result.isConfirmed) {
                    executeBatchAction('force_win', itemId);
                }
            });
        } else {
            if (confirm(confirmText)) {
                executeBatchAction('force_win', itemId);
            }
        }
    });

    // 6. Tombol "Bagi Rata (100%)"
    $(document).on('click', '.js-btn-balance', function () {
        var confirmText = 'Bagi rata peluang 100% ke seluruh hadiah yang sedang aktif?';

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Bagi Rata Peluang?',
                text: confirmText,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#17a2b8',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Bagi Rata',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (result.isConfirmed) {
                    executeBatchAction('balance', null);
                }
            });
        } else {
            if (confirm(confirmText)) {
                executeBatchAction('balance', null);
            }
        }
    });

    // Eksekusi Batch Action (Force Win / Balance)
    function executeBatchAction(action, itemId) {
        $.ajax({
            url: quickBatchUrl,
            type: 'POST',
            data: {
                action: action,
                item_id: itemId
            },
            success: function (res) {
                if (res.items && res.items.length) {
                    res.items.forEach(function (itm) {
                        var chanceVal = (Math.round(itm.chance_percent * 100) / 100);
                        $('#input-chance-' + itm.id).val(chanceVal);
                        syncChanceValueDisplay(itm.id, chanceVal);

                        if (itm.is_active) {
                            $('#switch-active-' + itm.id).prop('checked', true);
                            $('#mobile-card-' + itm.id).removeClass('is-inactive');
                            $('#desktop-row-' + itm.id).removeClass('table-light text-muted');
                            $('#mobile-status-pill-' + itm.id).removeClass('badge-secondary').addClass('badge-success').text('Aktif');
                            $('#desktop-badge-' + itm.id).removeClass('badge-secondary').addClass('badge-success').text('Aktif');
                        } else {
                            $('#switch-active-' + itm.id).prop('checked', false);
                            $('#mobile-card-' + itm.id).addClass('is-inactive');
                            $('#desktop-row-' + itm.id).addClass('table-light text-muted');
                            $('#mobile-status-pill-' + itm.id).removeClass('badge-success').addClass('badge-secondary').text('Nonaktif');
                            $('#desktop-badge-' + itm.id).removeClass('badge-success').addClass('badge-secondary').text('Nonaktif');
                        }
                    });
                }

                updateBudgetUI(res.used, res.remaining);
                showToast('success', res.message);
            },
            error: function (xhr) {
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal memproses aksi batch.';
                showToast('error', msg);
            }
        });
    }
</script>
@endpush
