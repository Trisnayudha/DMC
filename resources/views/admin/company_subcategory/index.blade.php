@extends('layouts.inspire.master')

@section('content')
<div class="content-wrapper">
    <section class="section">
        <div class="section-header">
            <h1>Company Subcategories</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.company_categories.index') }}">Company Categories</a></div>
                <div class="breadcrumb-item active">Subcategories</div>
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
                            <h4 class="mb-0"><i class="fas fa-sitemap mr-2"></i>Subcategories per Category</h4>
                            <span class="badge badge-primary" style="font-size:13px;">{{ $categories->sum(fn($c) => $c->subcategories->count()) }} total</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" style="font-size:13px;">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Category / Subcategory</th>
                                            <th width="80" class="text-center">Status</th>
                                            <th width="60" class="text-center">Used</th>
                                            <th width="120" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($categories as $cat)
                                            <tr style="background:#f8f9fa;">
                                                <td colspan="4">
                                                    <span class="font-weight-600"><i class="fas fa-tag mr-1 text-muted"></i>{{ $cat->name }}</span>
                                                    <span class="text-muted">({{ $cat->subcategories->count() }})</span>
                                                    @if (!$cat->is_active)
                                                        <span class="badge badge-secondary" style="font-size:10px;">category inactive</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @forelse ($cat->subcategories as $sub)
                                                <tr>
                                                    <td style="padding-left:32px;">{{ $sub->name }}</td>
                                                    <td class="text-center">
                                                        @if ($sub->is_active)
                                                            <span class="badge badge-success" style="font-size:11px;">Active</span>
                                                        @else
                                                            <span class="badge badge-secondary" style="font-size:11px;">Inactive</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @php
                                                            $usedCount = \DB::table('company_subcategory_company')->where('company_subcategory_id', $sub->id)->count();
                                                        @endphp
                                                        <span class="text-muted">{{ $usedCount }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="d-inline-flex" style="gap:4px;">
                                                            <button type="button" class="btn btn-sm btn-outline-info js-edit-subcategory"
                                                                data-id="{{ $sub->id }}"
                                                                data-name="{{ $sub->name }}"
                                                                title="Edit" style="padding:4px 8px;">
                                                                <i class="fas fa-pen" style="font-size:11px;"></i>
                                                            </button>
                                                            <form method="POST" action="{{ route('admin.company_subcategories.toggle', $sub->id) }}" style="display:inline;">
                                                                @csrf
                                                                <button type="submit"
                                                                    class="btn btn-sm btn-outline-{{ $sub->is_active ? 'warning' : 'success' }}"
                                                                    title="{{ $sub->is_active ? 'Deactivate' : 'Activate' }}"
                                                                    style="padding:4px 8px;"
                                                                    onclick="return confirm('{{ $sub->is_active ? 'Deactivate' : 'Activate' }} {{ addslashes($sub->name) }}?')">
                                                                    <i class="fas fa-{{ $sub->is_active ? 'ban' : 'check' }}" style="font-size:11px;"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-muted" style="padding-left:32px;font-style:italic;">Belum ada subcategory</td>
                                                </tr>
                                            @endforelse
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
                            <h4 class="mb-0"><i class="fas fa-plus mr-2"></i>Tambah Subcategory</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.company_subcategories.store') }}">
                                @csrf
                                <div class="form-group">
                                    <label class="small font-weight-bold">Parent Category</label>
                                    <select name="company_category_id" class="form-control form-control-sm @error('company_category_id') is-invalid @enderror" required>
                                        <option value="">--Pilih Category--</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ old('company_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('company_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Subcategory Name</label>
                                    <input type="text" name="name" class="form-control form-control-sm @error('name') is-invalid @enderror"
                                        value="{{ old('name') }}" required placeholder="e.g. Open Pit">
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
                            <p>Subcategory berada di bawah satu <strong>category</strong>. Company bisa memilih <strong>lebih dari satu</strong> subcategory (multi-input).</p>
                            <p>Subcategory yang di-<strong>deactivate</strong> tidak muncul di dropdown, tapi data existing tetap aman.</p>
                            <p class="mb-0">API endpoint: <code>GET /api/company-subcategories?category_id=</code></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editSubcategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="editSubcategoryForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Edit Subcategory</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="small font-weight-bold">Subcategory Name</label>
                        <input type="text" name="name" id="editSubName" class="form-control form-control-sm" required>
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
    $(document).on('click', '.js-edit-subcategory', function() {
        var id = $(this).data('id');
        $('#editSubName').val($(this).data('name'));
        $('#editSubcategoryForm').attr('action', '/admin/company-subcategories/' + id + '/update');
        $('#editSubcategoryModal').modal('show');
    });
</script>
@endpush
