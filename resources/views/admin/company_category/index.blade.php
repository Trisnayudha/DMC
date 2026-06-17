@extends('layouts.inspire.master')

@section('content')
<div class="content-wrapper">
    <section class="section">
        <div class="section-header">
            <h1>Company Categories</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item active">Company Categories</div>
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

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="mb-0"><i class="fas fa-tags mr-2"></i>Categories</h4>
                            <span class="badge badge-primary" style="font-size:13px;">{{ $categories->count() }} total</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" style="font-size:13px;" id="categoryTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="50" class="text-center">Order</th>
                                            <th>Category Name</th>
                                            <th width="80" class="text-center">Status</th>
                                            <th width="60" class="text-center">Used</th>
                                            <th width="140" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="sortableBody">
                                        @foreach ($categories as $cat)
                                            <tr data-id="{{ $cat->id }}">
                                                <td class="text-center text-muted">{{ $cat->sort_order }}</td>
                                                <td>
                                                    <span class="font-weight-600">{{ $cat->name }}</span>
                                                </td>
                                                <td class="text-center">
                                                    @if ($cat->is_active)
                                                        <span class="badge badge-success" style="font-size:11px;">Active</span>
                                                    @else
                                                        <span class="badge badge-secondary" style="font-size:11px;">Inactive</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @php
                                                        $usedCount = \DB::table('company')->where('company_category', $cat->name)->count();
                                                    @endphp
                                                    <span class="text-muted">{{ $usedCount }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-inline-flex" style="gap:4px;">
                                                        <button type="button" class="btn btn-sm btn-outline-info js-edit-category"
                                                            data-id="{{ $cat->id }}"
                                                            data-name="{{ $cat->name }}"
                                                            title="Edit" style="padding:4px 8px;">
                                                            <i class="fas fa-pen" style="font-size:11px;"></i>
                                                        </button>
                                                        <form method="POST" action="{{ route('admin.company_categories.toggle', $cat->id) }}" style="display:inline;">
                                                            @csrf
                                                            <button type="submit"
                                                                class="btn btn-sm btn-outline-{{ $cat->is_active ? 'warning' : 'success' }}"
                                                                title="{{ $cat->is_active ? 'Deactivate' : 'Activate' }}"
                                                                style="padding:4px 8px;"
                                                                onclick="return confirm('{{ $cat->is_active ? 'Deactivate' : 'Activate' }} {{ addslashes($cat->name) }}?')">
                                                                <i class="fas fa-{{ $cat->is_active ? 'ban' : 'check' }}" style="font-size:11px;"></i>
                                                            </button>
                                                        </form>
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
                            <h4 class="mb-0"><i class="fas fa-plus mr-2"></i>Tambah Category</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.company_categories.store') }}">
                                @csrf
                                <div class="form-group">
                                    <label class="small font-weight-bold">Category Name</label>
                                    <input type="text" name="name" class="form-control form-control-sm @error('name') is-invalid @enderror"
                                        value="{{ old('name') }}" required placeholder="e.g. Renewable Energy">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <button type="submit" class="btn btn-primary btn-block btn-sm">
                                    <i class="fas fa-plus mr-1"></i> Tambah
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0"><i class="fas fa-info-circle mr-2"></i>Info</h4>
                        </div>
                        <div class="card-body" style="font-size:12px;color:#888;">
                            <p>Categories yang di-<strong>deactivate</strong> tidak akan muncul di dropdown registrasi, tapi data existing yang sudah pakai category tersebut tetap aman.</p>
                            <p class="mb-0">API endpoint: <code>GET /api/company-categories</code></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="editCategoryForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="small font-weight-bold">Category Name</label>
                        <input type="text" name="name" id="editCatName" class="form-control form-control-sm" required>
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
    $(document).on('click', '.js-edit-category', function() {
        var id = $(this).data('id');
        $('#editCatName').val($(this).data('name'));
        $('#editCategoryForm').attr('action', '/admin/company-categories/' + id + '/update');
        $('#editCategoryModal').modal('show');
    });
</script>
@endpush
