@push('bottom')
<script>
$(function () {
    // Tanpa Responsive: semua kolom tetap tampil, tabel di-scroll horizontal.
    $('#inquiries-table').DataTable({
        order: [[0, 'desc']],
        pageLength: 50,
        responsive: false
    });
});
</script>
@endpush
