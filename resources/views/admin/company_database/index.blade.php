@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Company Database</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active">Company Database</div>
                </div>
            </div>

            <div class="section-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible show fade">
                        <div class="alert-body">
                            <button class="close" data-dismiss="alert"><span>×</span></button>
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible show fade">
                        <div class="alert-body">
                            <button class="close" data-dismiss="alert"><span>×</span></button>
                            {{ session('error') }}
                        </div>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center" style="gap:12px;">
                        <h4 class="mb-0">Company Sync Center</h4>
                        <div style="display:flex; gap:8px; flex-wrap:wrap;">
                            <span class="badge badge-light">Total Company: {{ $totalCompanies }}</span>
                            <span class="badge badge-warning">Need Sync: {{ $totalNeedSync }}</span>
                            <span class="badge badge-primary">Duplicates: {{ $totalDuplicates }}</span>
                        </div>
                    </div>

                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.company_database.index') }}" class="mb-3">
                            <div class="form-row align-items-end">
                                <div class="form-group col-md-5 mb-2">
                                    <label class="mb-1">Search Company</label>
                                    <input type="text" name="search" class="form-control" value="{{ $search }}"
                                        placeholder="contoh: PT ABC">
                                </div>
                                <div class="form-group col-md-4 mb-2">
                                    <label class="mb-1">Scope</label>
                                    <select name="scope" class="form-control">
                                        <option value="need_sync" {{ $scope === 'need_sync' ? 'selected' : '' }}>Need Sync (Duplicate + Incomplete)</option>
                                        <option value="duplicates" {{ $scope === 'duplicates' ? 'selected' : '' }}>Duplicates Only</option>
                                        <option value="all" {{ $scope === 'all' ? 'selected' : '' }}>All Companies</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3 mb-2" style="display:flex; gap:8px;">
                                    <button type="submit" class="btn btn-primary btn-block">Filter</button>
                                    <a href="{{ route('admin.company_database.index') }}" class="btn btn-outline-secondary btn-block">Reset</a>
                                </div>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('admin.company_database.sync_all') }}" class="mb-3">
                            @csrf
                            <input type="hidden" name="search" value="{{ $search }}">
                            <input type="hidden" name="scope" value="{{ $scope }}">
                            <button type="submit" class="btn btn-warning"
                                onclick="return confirm('Sync semua company sesuai filter saat ini?')">
                                <i class="fas fa-sync"></i> Sync Semua Sesuai Filter
                            </button>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="company-database-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Company Name</th>
                                        <th>Total Record</th>
                                        <th>Incomplete Record</th>
                                        <th>Best Data</th>
                                        <th>User IDs (sample)</th>
                                        <th>Last Update</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($list as $idx => $item)
                                        <tr>
                                            <td>{{ $idx + 1 }}</td>
                                            <td>{{ $item->company_name }}</td>
                                            <td>{{ $item->total_records }}</td>
                                            <td>
                                                @if ($item->incomplete_records > 0)
                                                    <span class="badge badge-warning">{{ $item->incomplete_records }}</span>
                                                @else
                                                    <span class="badge badge-success">0</span>
                                                @endif
                                            </td>
                                            <td>
                                                ID {{ $item->best_record_id }}
                                                <span class="badge badge-info">{{ $item->best_score }}/{{ $item->max_score }} field</span>
                                            </td>
                                            <td>{{ $item->user_ids ?: '-' }}</td>
                                            <td>{{ $item->updated_at ? \Carbon\Carbon::parse($item->updated_at)->format('d M Y H:i') : '-' }}</td>
                                            <td>
                                                <form method="POST" action="{{ route('admin.company_database.sync') }}" style="display:inline-block;">
                                                    @csrf
                                                    <input type="hidden" name="normalized_name" value="{{ $item->normalized_name }}">
                                                    <button type="submit" class="btn btn-sm btn-primary"
                                                        onclick="return confirm('Sync company {{ addslashes($item->company_name) }} sekarang?')">
                                                        Sync
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-info js-edit-company"
                                                    data-toggle="modal" data-target="#editCompanyModal"
                                                    data-company-name="{{ $item->company_name }}"
                                                    data-normalized-name="{{ $item->normalized_name }}"
                                                    data-payload='@json($item->best_values)'>
                                                    Edit & Sync
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">Data company tidak ditemukan untuk filter saat ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="editCompanyModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.company_database.update') }}">
                    @csrf
                    <input type="hidden" name="normalized_name" id="edit_normalized_name">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Company Data & Auto Sync</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-light mb-3">
                            Company: <strong id="edit_company_name">-</strong><br>
                            Perubahan akan diterapkan ke semua record company dengan nama yang sama.
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Prefix</label>
                                <select name="prefix" id="edit_prefix" class="form-control js-prefix-select2">
                                    <option value="">Other</option>
                                    <option value="PT">PT</option>
                                    <option value="CV">CV</option>
                                    <option value="Ltd">Ltd</option>
                                    <option value="GmbH">GmbH</option>
                                    <option value="Limited">Limited</option>
                                    <option value="Llc">Llc</option>
                                    <option value="Corp">Corp</option>
                                    <option value="Pte Ltd">Pte Ltd</option>
                                    <option value="Assosiation">Assosiation</option>
                                    <option value="Government">Government</option>
                                    <option value="Pty Ltd">Pty Ltd</option>
                                </select>
                            </div>
                            <div class="form-group col-md-8">
                                <label>Company Name</label>
                                <input type="text" name="company_name" id="edit_company_name_input"
                                    class="form-control" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label>Website</label>
                                <input type="text" name="company_website" id="edit_company_website" class="form-control">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Company Category</label>
                                <select name="company_category" id="edit_company_category"
                                    class="form-control company_category_edit">
                                    <option value="">--Select--</option>
                                    <option value="Coal Mining">Coal Mining</option>
                                    <option value="Minerals Producer">Minerals Producer</option>
                                    <option value="Supplier/Distributor/Manufacturer">Supplier/Distributor/Manufacturer</option>
                                    <option value="Contrator">Contrator</option>
                                    <option value="Association / Organization / Government">Association / Organization / Government</option>
                                    <option value="Financial Services">Financial Services</option>
                                    <option value="Technology">Technology</option>
                                    <option value="Investors">Investors</option>
                                    <option value="Logistics and Shipping">Logistics and Shipping</option>
                                    <option value="Media">Media</option>
                                    <option value="Consultant">Consultant</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6 company_other_edit" style="display:none;">
                                <label>Company Other</label>
                                <input type="text" name="company_other" id="edit_company_other" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="address" id="edit_address" rows="2" class="form-control"></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>City</label>
                                <input type="text" name="city" id="edit_city" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Postal Code</label>
                                <input type="text" name="portal_code" id="edit_portal_code" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Country</label>
                                <input type="text" name="country" id="edit_country" class="form-control">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Prefix Office Number</label>
                                <input type="text" name="prefix_office_number" id="edit_prefix_office_number" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Office Number</label>
                                <input type="text" name="office_number" id="edit_office_number" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Full Office Number</label>
                                <input type="text" name="full_office_number" id="edit_full_office_number" class="form-control">
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"
                            onclick="return confirm('Simpan perubahan dan sync ke semua record company ini?')">
                            Save & Sync
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('bottom')
    <script>
        $(document).ready(function() {
            if ($.fn.select2) {
                $('.js-prefix-select2').select2({
                    dropdownParent: $('#editCompanyModal'),
                    width: '100%',
                    placeholder: 'Select Prefix',
                    allowClear: true
                });
            }

            if ($.fn.DataTable.isDataTable('#company-database-table')) {
                $('#company-database-table').DataTable().destroy();
            }

            $('#company-database-table').DataTable({
                pageLength: 25,
                order: [
                    [2, 'desc']
                ]
            });

            $('.js-edit-company').on('click', function() {
                var payload = $(this).attr('data-payload') || '{}';
                var parsed = {};

                try {
                    parsed = JSON.parse(payload);
                } catch (e) {
                    parsed = {};
                }

                $('#edit_company_name').text($(this).data('company-name') || '-');
                $('#edit_normalized_name').val($(this).data('normalized-name') || '');
                $('#edit_company_name_input').val(parsed.company_name || $(this).data('company-name') || '');
                $('#edit_prefix').val(parsed.prefix || '').trigger('change');
                $('#edit_company_website').val(parsed.company_website || '');
                $('#edit_company_category').val(parsed.company_category || '');
                $('#edit_company_other').val(parsed.company_other || '');
                $('#edit_address').val(parsed.address || '');
                $('#edit_city').val(parsed.city || '');
                $('#edit_portal_code').val(parsed.portal_code || '');
                $('#edit_country').val(parsed.country || '');
                $('#edit_prefix_office_number').val(parsed.prefix_office_number || '');
                $('#edit_office_number').val(parsed.office_number || '');
                $('#edit_full_office_number').val(parsed.full_office_number || '');
                $('#edit_company_category').trigger('change');
            });

            $('#edit_company_category').on('change', function() {
                if ($(this).val() === 'other') {
                    $('.company_other_edit').css('display', 'block');
                } else {
                    $('.company_other_edit').css('display', 'none');
                    $('#edit_company_other').val('');
                }
            });
        });
    </script>
@endpush
