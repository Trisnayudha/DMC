@extends('layouts.inspire.master')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Ngrok Management</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ url('admin') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Ngrok</div>
        </div>
    </div>

    <div class="section-body">
        <div class="card">
            <div class="card-header">
                <h4>Daftar Ngrok Link</h4>
                <div class="card-header-action">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addModal">
                        <i class="fas fa-plus"></i> Tambah Link
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="alert-box"></div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="ngrokTable">
                        <thead>
                            <tr>
                                <th width="50px">No</th>
                                <th>Link</th>
                                <th>Dibuat</th>
                                <th width="150px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($list as $item)
                            <tr id="row-{{ $item->id }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <a href="{{ $item->link }}" target="_blank">{{ $item->link }}</a>
                                </td>
                                <td>{{ $item->created_at->format('d M Y, H:i') }}</td>
                                <td>
                                    <button class="btn btn-sm btn-warning btn-edit"
                                        data-id="{{ $item->id }}"
                                        data-link="{{ $item->link }}">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger btn-delete"
                                        data-id="{{ $item->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr id="empty-row">
                                <td colspan="4" class="text-center text-muted">Belum ada data ngrok.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Tambah -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Ngrok Link</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Link <span class="text-danger">*</span></label>
                    <input type="url" id="addLink" class="form-control" placeholder="https://xxxx.ngrok.io">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSave">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Ngrok Link</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editId">
                <div class="form-group">
                    <label>Link <span class="text-danger">*</span></label>
                    <input type="url" id="editLink" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning" id="btnUpdate">Update</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var csrfToken = '{{ csrf_token() }}';

    function showAlert(message, type) {
        $('#alert-box').html('<div class="alert alert-' + type + ' alert-dismissible fade show">' +
            message + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
    }

    // Tambah
    $('#btnSave').click(function () {
        var link = $('#addLink').val().trim();
        if (!link) { showAlert('Link tidak boleh kosong.', 'danger'); return; }

        $.ajax({
            url: '{{ route("admin.ngrok.store") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { link: link },
            success: function (res) {
                showAlert(res.message, 'success');
                $('#addModal').modal('hide');
                $('#addLink').val('');
                setTimeout(function () { location.reload(); }, 800);
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message ?? 'Gagal menyimpan.';
                showAlert(msg, 'danger');
            }
        });
    });

    // Buka modal edit
    $(document).on('click', '.btn-edit', function () {
        $('#editId').val($(this).data('id'));
        $('#editLink').val($(this).data('link'));
        $('#editModal').modal('show');
    });

    // Update
    $('#btnUpdate').click(function () {
        var id   = $('#editId').val();
        var link = $('#editLink').val().trim();
        if (!link) { showAlert('Link tidak boleh kosong.', 'danger'); return; }

        $.ajax({
            url: '/admin/ngrok/' + id,
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { link: link },
            success: function (res) {
                showAlert(res.message, 'success');
                $('#editModal').modal('hide');
                setTimeout(function () { location.reload(); }, 800);
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message ?? 'Gagal mengupdate.';
                showAlert(msg, 'danger');
            }
        });
    });

    // Hapus
    $(document).on('click', '.btn-delete', function () {
        var id = $(this).data('id');
        if (!confirm('Yakin hapus ngrok link ini?')) return;

        $.ajax({
            url: '/admin/ngrok/' + id,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (res) {
                $('#row-' + id).remove();
                showAlert(res.message, 'success');
            },
            error: function () {
                showAlert('Gagal menghapus.', 'danger');
            }
        });
    });
</script>
@endpush
