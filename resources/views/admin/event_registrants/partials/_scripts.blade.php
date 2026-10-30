@push('bottom')
<script>
$(function () {
    var rows = @json($rows);

    function esc(value) {
        return $('<div>').text(value == null || value === '' ? '-' : value).html();
    }

    function formatDate(value) {
        if (!value) { return '-'; }
        var d = new Date(value.replace(' ', 'T'));
        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        var pad = function (n) { return (n < 10 ? '0' : '') + n; };
        return pad(d.getDate()) + ' ' + months[d.getMonth()] + ' ' + d.getFullYear() + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    var statusClass = { 'Paid Off': 'success', 'Waiting': 'warning' };

    // Tanpa Responsive: semua kolom tetap tampil, tabel di-scroll horizontal.
    $('#event-registrants-table').DataTable({
        data: rows,
        deferRender: true,
        order: [[0, 'desc']],
        pageLength: 50,
        responsive: false,
        columns: [
            { data: 'registered', className: 'text-nowrap', render: function (v, type) { return type === 'display' ? formatDate(v) : (v || ''); } },
            { data: 'event', render: function (v, type, row) { return type === 'display' ? '<a href="' + esc(row.event_url) + '">' + esc(v) + '</a>' : (v || ''); } },
            { data: 'name', render: esc },
            { data: 'email', render: esc },
            { data: 'phone', className: 'text-nowrap', render: esc },
            { data: 'company', render: esc },
            { data: 'job_title', render: esc },
            { data: 'package', render: esc },
            { data: 'status', render: function (v, type) {
                return type === 'display' ? '<span class="badge badge-' + (statusClass[v] || 'secondary') + '">' + esc(v) + '</span>' : (v || '');
            } },
            { data: 'is_member', render: function (v, type) {
                if (type !== 'display') { return v ? 'Member' : 'Non Member'; }
                return v ? '<span class="badge badge-primary">Member</span>' : '<span class="badge badge-light">Non Member</span>';
            } },
            { data: 'checked_in', className: 'text-center', render: function (v, type) {
                if (type !== 'display') { return v ? 'Checked in' : ''; }
                return v ? '<i class="fas fa-check text-success" title="Checked in"></i>' : '<span class="text-muted">-</span>';
            } }
        ]
    });
});
</script>
@endpush
