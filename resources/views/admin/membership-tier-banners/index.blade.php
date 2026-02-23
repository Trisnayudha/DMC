@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Membership Tier Banners</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="">Membership Tier Banners</a></div>
                </div>
            </div>

            <div class="section-body">
                <h2 class="section-title">Banner / Carousel per Tier</h2>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Management</h4>
                            </div>

                            <div class="card-body">
                                @if ($errors->any())
                                    <div class="alert alert-warning">
                                        <div class="alert-title">Whoops!</div>
                                        @lang('general.validation_error_message')
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if (session('success'))
                                    <div class="alert alert-success">{{ session('success') }}</div>
                                @endif

                                @if (session('error'))
                                    <div class="alert alert-danger">{{ session('error') }}</div>
                                @endif

                                <div class="form-row mb-3">
                                    <div class="form-group col-md-10"></div>
                                    <div class="form-group col-md-2">
                                        <a href="javascript:void(0)" class="btn btn-block btn-icon icon-left btn-success"
                                            id="btnAddNew">
                                            <i class="fas fa-plus-circle"></i>
                                            Tambah Banner
                                        </a>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table id="laravel_crud" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="10px">No</th>
                                                <th>Image</th>
                                                <th>Tier</th>
                                                <th>Section</th>
                                                <th>Title</th>
                                                <th>Link</th>
                                                <th>Order</th>
                                                <th>Status</th>
                                                <th width="18%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; ?>
                                            @foreach ($data as $row)
                                                <tr>
                                                    <td>{{ $no++ }}</td>
                                                    <td>
                                                        <img src="{{ $row->image }}" alt="" width="70">
                                                    </td>
                                                    <td><span class="badge badge-primary">{{ $row->tier }}</span></td>
                                                    <td>{{ $row->section_key }}</td>
                                                    <td>{{ $row->title }}</td>
                                                    <td>{{ $row->link_url }}</td>
                                                    <td>{{ $row->sort_order }}</td>
                                                    <td>
                                                        @if ($row->is_active)
                                                            <span class="badge badge-success">Active</span>
                                                        @else
                                                            <span class="badge badge-secondary">Inactive</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-warning btn-edit"
                                                            data-id="{{ $row->id }}" data-tier="{{ $row->tier }}"
                                                            data-section="{{ $row->section_key }}"
                                                            data-title="{{ $row->title }}"
                                                            data-link="{{ $row->link_url }}"
                                                            data-newtab="{{ $row->open_new_tab ? 1 : 0 }}"
                                                            data-order="{{ $row->sort_order }}"
                                                            data-active="{{ $row->is_active ? 1 : 0 }}" title="Edit">
                                                            <span class="fa fa-edit"></span>
                                                        </button>

                                                        <button class="btn btn-danger btn-delete"
                                                            data-id="{{ $row->id }}" title="Hapus">
                                                            <span class="fa fa-trash"></span>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                            </div> {{-- card-body --}}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- MODAL --}}
    <div class="modal fade" id="banner-modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modalTitle">Tambah Banner</h4>
                </div>

                <div class="modal-body">
                    <form id="bannerForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="formMethod" name="_method" value="POST">
                        <input type="hidden" id="banner_id" value="">

                        <div class="form-group">
                            <label>Image <small class="text-muted">(required saat add, optional saat edit)</small></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="image" name="image">
                                <label class="custom-file-label" for="image">Choose file</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Tier</label>
                            <select class="form-control" name="tier" id="tier" required>
                                <option value="">Choose</option>
                                <option value="reguler">Reguler</option>
                                <option value="black">Black</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Section (2 carousel)</label>
                            <select class="form-control" name="section_key" id="section_key" required>
                                <option value="">Choose</option>
                                <option value="dashboard_left">dashboard_left</option>
                                <option value="dashboard_right">dashboard_right</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Title (optional)</label>
                            <input type="text" name="title" id="title" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Link URL (optional)</label>
                            <input type="text" name="link_url" id="link_url" class="form-control"
                                placeholder="https://... / atau /path">
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="open_new_tab"
                                    name="open_new_tab" value="1">
                                <label class="custom-control-label" for="open_new_tab">Open in new tab</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Sort Order</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control" value="1"
                                min="1">
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                    value="1" checked>
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                        </div>

                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-primary" id="btn-save">Save changes</button>
                        </div>
                    </form>
                </div>

                <div class="modal-footer"></div>
            </div>
        </div>
    </div>

    <script src="{{ asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            $('#laravel_crud').DataTable();
            bsCustomFileInput.init();

            // ADD
            $('#btnAddNew').click(function() {
                $('#modalTitle').text('Tambah Banner');
                $('#bannerForm').trigger("reset");
                $('.custom-file-label').text('Choose file');

                $('#banner_id').val('');
                $('#formMethod').val('POST');

                // action ke store
                $('#bannerForm').attr('action', "{{ route('membership-tier-banners.store') }}");

                // default active checked
                $('#is_active').prop('checked', true);

                $('#banner-modal').modal('show');
            });

            // EDIT (pakai data-* biar simpel)
            $('.btn-edit').click(function() {
                $('#modalTitle').text('Edit Banner');
                $('#bannerForm').trigger("reset");
                $('.custom-file-label').text('Choose file');

                let id = $(this).data('id');

                $('#banner_id').val(id);
                $('#formMethod').val('PUT');

                $('#tier').val($(this).data('tier'));
                $('#section_key').val($(this).data('section'));
                $('#title').val($(this).data('title'));
                $('#link_url').val($(this).data('link'));
                $('#sort_order').val($(this).data('order'));

                $('#open_new_tab').prop('checked', $(this).data('newtab') == 1);
                $('#is_active').prop('checked', $(this).data('active') == 1);

                // action ke update
                let updateUrl = "{{ url('admin/membership-tier-banners') }}/" + id;
                $('#bannerForm').attr('action', updateUrl);

                $('#banner-modal').modal('show');
            });

            // DELETE
            $('.btn-delete').click(function() {
                let id = $(this).data('id');
                let token = "{{ csrf_token() }}";

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Banner ini akan dihapus!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "/admin/membership-tier-banners/" + id,
                            type: 'DELETE',
                            data: {
                                "_token": token
                            },
                            success: function(response) {
                                Swal.fire('Terhapus!', response.message, 'success');
                                location.reload();
                            },
                            error: function() {
                                Swal.fire('Gagal!',
                                    'Terjadi kesalahan saat menghapus data.',
                                    'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
