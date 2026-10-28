@push('bottom')
<script>
    (function() {
        var oldItems = @json(old('items', []));
        var renewalsById = {};
        var eventName = '';

        function rupiah(value) {
            return 'Rp ' + Math.round(value || 0).toLocaleString('id-ID');
        }

        // ── Item rows ──
        var rowIndex = 0;

        function addItem(item) {
            item = item || {};
            var i = rowIndex++;
            var $row = $('<tr>' +
                '<td><input type="text" class="form-control form-control-sm" required maxlength="255" name="items[' + i + '][description]"></td>' +
                '<td><input type="number" class="form-control form-control-sm js-qty" required min="1" name="items[' + i + '][qty]"></td>' +
                '<td><input type="number" class="form-control form-control-sm js-price" required min="0" step="1" name="items[' + i + '][unit_price]"></td>' +
                '<td class="text-right js-amount">Rp 0</td>' +
                '<td><button type="button" class="btn btn-sm btn-link text-danger js-remove-item" title="Hapus"><i class="fas fa-times"></i></button></td>' +
                '</tr>');
            $row.find('input').eq(0).val(item.description || '');
            $row.find('.js-qty').val(item.qty || 1);
            $row.find('.js-price').val(item.unit_price !== undefined && item.unit_price !== null ? item.unit_price : '');
            $('#fi-items').append($row);
            recalc();
            return $row;
        }

        // Baris pertama yang masih kosong dipakai dulu sebelum menambah baris baru.
        function fillOrAddItem(item) {
            var $empty = $('#fi-items tr').filter(function() {
                return !$(this).find('input').eq(0).val();
            }).first();
            if (!$empty.length) {
                return addItem(item);
            }
            $empty.find('input').eq(0).val(item.description);
            $empty.find('.js-qty').val(item.qty || 1);
            $empty.find('.js-price').val(item.unit_price);
            recalc();
            return $empty;
        }

        function recalc() {
            var subtotal = 0;
            $('#fi-items tr').each(function() {
                var qty = parseInt($(this).find('.js-qty').val(), 10) || 0;
                var price = Math.round(parseFloat($(this).find('.js-price').val()) || 0);
                $(this).find('.js-amount').text(rupiah(qty * price));
                subtotal += qty * price;
            });
            var vat = Math.round(subtotal * (parseFloat($('#fi-vat').val()) || 0) / 100);
            var total = subtotal + vat;
            $('#fi-subtotal').text(rupiah(subtotal));
            $('#fi-vat-amount').text(rupiah(vat));
            $('#fi-total').text(rupiah(total));
            $('#fi-min-warning').toggle(total > 0 && total < {{ \App\Services\FinanceInvoiceService::MIN_AMOUNT }});
        }

        $('#fi-add-item').on('click', function() { addItem(); });
        $(document).on('click', '.js-remove-item', function() {
            if ($('#fi-items tr').length > 1) {
                $(this).closest('tr').remove();
                recalc();
            }
        });
        $(document).on('input', '#fi-items input, #fi-vat', recalc);

        // ── Tipe invoice ──
        function currentType() {
            return $('.js-invoice-type:checked').val();
        }

        function showTypeSection() {
            var type = currentType();
            $('.js-type-section').each(function() {
                $(this).toggle($(this).data('type') === type);
            });
        }
        $('.js-invoice-type').on('change', showTypeSection);

        function setBillTo(data) {
            $('#fi-name').val(data.name || '');
            $('#fi-company').val(data.company || '');
            $('#fi-email').val(data.email || '');
            $('#fi-phone').val(data.phone || '');
            $('#fi-address').val(data.address || '');
        }

        // ── Sponsor renewal ──
        function loadSponsor(sponsorId, keepOld) {
            var $renewal = $('#fi-renewal');
            $renewal.html('<option value="">— Tidak dikaitkan —</option>');
            renewalsById = {};
            if (!sponsorId) return;

            $.get($('#fi-sponsor').data('url') + '/' + sponsorId, function(res) {
                if (!keepOld) setBillTo(res);
                res.renewals.forEach(function(r) {
                    renewalsById[r.id] = r;
                    $renewal.append($('<option>').val(r.id).text(r.label));
                });
                var oldRenewal = $renewal.data('old');
                if (keepOld && oldRenewal) {
                    $renewal.val(String(oldRenewal));
                }
            });
        }

        $('#fi-sponsor').on('change', function() { loadSponsor($(this).val(), false); });

        $('#fi-renewal').on('change', function() {
            var r = renewalsById[$(this).val()];
            if (!r) return;
            fillOrAddItem({
                description: 'Sponsorship ' + r.package + ' Djakarta Mining Club (' + r.contract_start + ' s/d ' + r.contract_end + ')',
                qty: 1,
                unit_price: r.amount_idr || ''
            });
        });

        // ── Tiket event ──
        function loadTickets(eventId) {
            var $ticket = $('#fi-ticket');
            $ticket.html('<option value="">— Pilih tiket —</option>');
            if (!eventId) return;
            $.get($('#fi-event').data('url') + '/' + eventId + '/tickets', function(res) {
                eventName = res.event;
                res.tickets.forEach(function(t) {
                    $ticket.append($('<option>').val(t.id).text(t.title + ' — ' + rupiah(t.price))
                        .attr('data-title', t.title).attr('data-price', t.price));
                });
            });
        }

        $('#fi-event').on('change', function() { loadTickets($(this).val()); });
        $('#fi-add-ticket').on('click', function() {
            var $opt = $('#fi-ticket option:selected');
            if (!$opt.val()) return;
            fillOrAddItem({
                description: 'Tiket ' + $opt.data('title') + ' - ' + eventName,
                qty: 1,
                unit_price: $opt.data('price')
            });
        });

        // ── Init (termasuk restore setelah validasi gagal) ──
        if (oldItems && Object.keys(oldItems).length) {
            Object.keys(oldItems).forEach(function(key) { addItem(oldItems[key]); });
        } else {
            addItem();
        }
        showTypeSection();
        if ($('#fi-sponsor').val()) loadSponsor($('#fi-sponsor').val(), true);
        if ($('#fi-event').val()) loadTickets($('#fi-event').val());

        $('#finance-invoice-form').on('submit', function() {
            $('#fi-submit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Membuat VA...');
        });
    })();
</script>
@endpush
