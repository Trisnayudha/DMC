{{-- Self-contained list behind the clickable "Converted Members" numbers on the diagram cards.
     The diagram is also loaded by AJAX on the Events page, so the modal is moved to <body>
     on open and click handlers are namespaced to avoid double-binding on reload. --}}
<div class="modal fade" id="cdm-modal" tabindex="-1" role="dialog" aria-labelledby="cdm-title" aria-hidden="true"
    data-url="{{ route('admin.event_conversions.converted_members') }}"
    data-year="{{ $selectedYear }}" data-search="{{ $search ?? '' }}">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="cdm-title">Converted members</h5>
                    <div class="small text-muted" id="cdm-subtitle"></div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <div id="cdm-loading" class="text-center py-4 text-muted">
                    <i class="fas fa-spinner fa-spin mr-1"></i> Loading...
                </div>
                <div id="cdm-empty" class="text-center py-4 text-muted" style="display:none;"></div>
                <div class="table-responsive" id="cdm-table-wrap" style="display:none;">
                    <table id="cdm-table" class="table table-sm table-striped table-hover mb-0 w-100" style="font-size: 13px;">
                        <thead><tr id="cdm-head"></tr></thead>
                        <tbody id="cdm-rows"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        // Keep only the freshest copy (the diagram may be re-rendered by the year selector).
        var $fresh = $('#cdm-modal').last();
        $('body > #cdm-modal').not($fresh).remove();

        // ⓘ source explanations on the diagram (see _stage_info).
        $(function() {
            if ($.fn.popover) {
                $('.stage-info').popover({ container: 'body', boundary: 'window' });
            }
        });
        // An ⓘ inside a clickable box only explains; it must not open the list.
        $(document).off('click.cdminfo').on('click.cdminfo', '.stage-info', function(e) {
            e.preventDefault();
            e.stopPropagation();
        });

        function esc(value) {
            return $('<div>').text(value == null ? '' : String(value)).html();
        }

        $(document).off('click.cdm').on('click.cdm', '.cdm-open', function(e) {
            e.preventDefault();
            var $modal = $('#cdm-modal').last();
            if (!$modal.parent().is('body')) {
                $('body > #cdm-modal').remove();
                $modal.appendTo('body');
            }

            // Each list has different columns, so drop the previous DataTable before rebuilding.
            if ($.fn.dataTable && $.fn.dataTable.isDataTable('#cdm-table')) {
                $('#cdm-table').DataTable().destroy();
            }

            $('#cdm-title').text($(this).data('title'));
            $('#cdm-subtitle').text('');
            $('#cdm-rows').empty();
            $('#cdm-table-wrap, #cdm-empty').hide();
            $('#cdm-loading').show();
            $modal.modal('show');

            $('#cdm-head').empty();
            $('#cdm-empty').text('No people in this period.');

            $.getJSON($(this).data('url') || $modal.data('url'), {
                type: $(this).data('type'),
                status: $(this).data('status'),
                year: $modal.data('year'),
                search: $modal.data('search'),
            }).done(function(res) {
                $('#cdm-loading').hide();
                $('#cdm-subtitle').text(res.period + ' · ' + res.count + ' row' + (res.count === 1 ? '' : 's'));
                if (!res.count) {
                    $('#cdm-empty').show();
                    return;
                }
                var head = '<th>#</th>';
                $.each(res.columns, function(_, col) { head += '<th>' + esc(col[1]) + '</th>'; });
                $('#cdm-head').html(head);

                var html = '';
                $.each(res.members, function(i, m) {
                    html += '<tr><td>' + (i + 1) + '</td>';
                    $.each(res.columns, function(_, col) {
                        var value = m[col[0]];
                        if (col[0] === 'status_member') {
                            value = value === 'active'
                                ? '<span class="badge badge-success">Active</span>'
                                : '<span class="badge badge-warning">Pending</span>';
                            html += '<td>' + value + '</td>';
                        } else {
                            html += '<td>' + esc(value) + '</td>';
                        }
                    });
                    html += '</tr>';
                });
                $('#cdm-rows').html(html);
                $('#cdm-table-wrap').show();

                if ($.fn.dataTable) {
                    // e.g. "DMC owned events - registrants (all statuses) - Year 2026"
                    var exportName = ($('#cdm-title').text() + ' - ' + res.period)
                        .replace(/[—–]/g, '-').replace(/[\\\/:*?"<>|]/g, '').replace(/\s+/g, ' ').trim();
                    var hasExcel = $.fn.dataTable.Buttons && $.fn.dataTable.ext.buttons.excelHtml5;

                    $('#cdm-table').DataTable({
                        pageLength: 10,
                        lengthMenu: [10, 25, 50, 100],
                        order: [],
                        autoWidth: false,
                        language: { search: 'Search:', emptyTable: 'No people in this period.' },
                        dom: hasExcel
                            ? "<'row align-items-center mb-2'<'col-md-4'l><'col-md-4 text-center'B><'col-md-4'f>>" +
                              "<'row'<'col-12'tr>>" +
                              "<'row align-items-center mt-2'<'col-md-5'i><'col-md-7'p>>"
                            : 'lfrtip',
                        // Exports every row (all pages), or only the matches when a search is typed.
                        buttons: hasExcel ? [{
                            extend: 'excelHtml5',
                            text: '<i class="fas fa-file-excel mr-1"></i> Export Excel',
                            className: 'btn btn-sm btn-success',
                            title: exportName,
                            filename: exportName,
                        }] : [],
                    });
                }
            }).fail(function() {
                $('#cdm-loading').hide();
                $('#cdm-empty').text('Failed to load converted members.').show();
            });
        });
    })();
</script>
