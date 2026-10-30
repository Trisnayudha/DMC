@push('bottom')
<script>
$(function () {
    // Tanpa Responsive: semua kolom tetap tampil, tabel di-scroll horizontal.
    $('#partnership-visitors-table').DataTable({
        order: [[0, 'desc']],
        pageLength: 50,
        responsive: false
    });
});
</script>
@endpush
