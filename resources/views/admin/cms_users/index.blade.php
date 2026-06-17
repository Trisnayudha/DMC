@extends('layouts.inspire.master')

@section('content')
<div class="content-wrapper">
    <section class="section">
        <div class="section-header">
            <h1>CMS Users</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item active">CMS Users</div>
            </div>
        </div>

        <div class="section-body">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible show fade">
                    <div class="alert-body">
                        <button class="close" data-dismiss="alert"><span>&times;</span></button>
                        {{ session('success') }}
                    </div>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible show fade">
                    <div class="alert-body">
                        <button class="close" data-dismiss="alert"><span>&times;</span></button>
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="mb-0"><i class="fas fa-user-shield mr-2"></i>Admin Users</h4>
                            <span class="badge badge-primary" style="font-size:13px;">{{ $admins->count() }} admin</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" style="font-size:13px;">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="40">#</th>
                                            <th>Nama</th>
                                            <th>Email</th>
                                            <th width="80" class="text-center">Status</th>
                                            <th width="120" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($admins as $i => $admin)
                                            <tr>
                                                <td class="text-muted">{{ $i + 1 }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center" style="gap:10px;">
                                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white"
                                                            style="width:34px;height:34px;font-size:13px;font-weight:600;flex-shrink:0;background:{{ $admin->is_active ? '#6777ef' : '#adb5bd' }};">
                                                            {{ strtoupper(substr($admin->name ?? '?', 0, 1)) }}
                                                        </div>
                                                        <div>
                                                            <div style="font-weight:600;">{{ $admin->name ?: '(no name)' }}</div>
                                                            @if ($admin->id === auth()->id())
                                                                <span class="badge badge-light border" style="font-size:9px;">You</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><span class="text-muted">{{ $admin->email }}</span></td>
                                                <td class="text-center">
                                                    @if ($admin->is_active)
                                                        <span class="badge badge-success" style="font-size:11px;">Active</span>
                                                    @else
                                                        <span class="badge badge-secondary" style="font-size:11px;">Inactive</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-inline-flex" style="gap:4px;">
                                                        <button type="button" class="btn btn-sm btn-outline-info js-edit-cms-user"
                                                            data-id="{{ $admin->id }}"
                                                            data-name="{{ $admin->name }}"
                                                            data-email="{{ $admin->email }}"
                                                            title="Edit" style="padding:4px 8px;">
                                                            <i class="fas fa-pen" style="font-size:11px;"></i>
                                                        </button>
                                                        @if ($admin->id !== auth()->id())
                                                            <form method="POST" action="{{ route('admin.cms_users.toggle', $admin->id) }}" style="display:inline;">
                                                                @csrf
                                                                <button type="submit"
                                                                    class="btn btn-sm btn-outline-{{ $admin->is_active ? 'warning' : 'success' }}"
                                                                    title="{{ $admin->is_active ? 'Deactivate' : 'Activate' }}"
                                                                    style="padding:4px 8px;"
                                                                    onclick="return confirm('{{ $admin->is_active ? 'Deactivate' : 'Activate' }} {{ addslashes($admin->name) }}?')">
                                                                    <i class="fas fa-{{ $admin->is_active ? 'ban' : 'check' }}" style="font-size:11px;"></i>
                                                                </button>
                                                            </form>
                                                            <form method="POST" action="{{ route('admin.cms_users.destroy', $admin->id) }}" style="display:inline;">
                                                                @csrf @method('DELETE')
                                                                <button type="submit"
                                                                    class="btn btn-sm btn-outline-danger"
                                                                    title="Delete"
                                                                    style="padding:4px 8px;"
                                                                    onclick="return confirm('Hapus {{ addslashes($admin->name) }}? Tidak bisa di-undo.')">
                                                                    <i class="fas fa-trash" style="font-size:11px;"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0"><i class="fas fa-user-plus mr-2"></i>Tambah Admin</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.cms_users.store') }}">
                                @csrf
                                <div class="form-group">
                                    <label class="small font-weight-bold">Nama</label>
                                    <input type="text" name="name" class="form-control form-control-sm @error('name') is-invalid @enderror"
                                        value="{{ old('name') }}" required placeholder="Full name">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Email</label>
                                    <input type="email" name="email" class="form-control form-control-sm @error('email') is-invalid @enderror"
                                        value="{{ old('email') }}" required placeholder="admin@dmc.com">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Password</label>
                                    <input type="password" name="password" class="form-control form-control-sm @error('password') is-invalid @enderror"
                                        required placeholder="Min 6 karakter">
                                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <button type="submit" class="btn btn-primary btn-block btn-sm">
                                    <i class="fas fa-plus mr-1"></i> Tambah Admin
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editCmsUserModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="editCmsUserForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Edit Admin User</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="small font-weight-bold">Nama</label>
                        <input type="text" name="name" id="editName" class="form-control form-control-sm" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Email</label>
                        <input type="email" name="email" id="editEmail" class="form-control form-control-sm" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Password <small class="text-muted font-weight-normal">(kosongkan jika tidak diubah)</small></label>
                        <input type="password" name="password" class="form-control form-control-sm" placeholder="••••••••">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('bottom')
<script>
    $(document).on('click', '.js-edit-cms-user', function() {
        var id = $(this).data('id');
        $('#editName').val($(this).data('name'));
        $('#editEmail').val($(this).data('email'));
        $('#editCmsUserForm').attr('action', '/admin/cms-users/' + id + '/update');
        $('#editCmsUserModal').modal('show');
    });
</script>
@endpush
