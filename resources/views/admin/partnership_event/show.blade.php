@extends('layouts.inspire.master')

@section('content')
    <style>
        #partnership-visitor-table {
            font-size: 12px;
        }

        #partnership-visitor-table th,
        #partnership-visitor-table td {
            padding: .35rem .5rem;
            vertical-align: middle;
        }

        #partnership-visitor-table td.text-truncate-cell {
            max-width: 140px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>

    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Partnership Event Visitors</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.partnership_events.index') }}">Partnership Event Visitors</a></div>
                    <div class="breadcrumb-item active">{{ $event->name }}</div>
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

                @if (session('import_errors'))
                    <div class="alert alert-warning alert-dismissible show fade">
                        <div class="alert-body">
                            <button class="close" data-dismiss="alert"><span>×</span></button>
                            <strong>Import warnings:</strong>
                            <ul class="mb-0 mt-1" style="font-size:12px;">
                                @foreach (session('import_errors') as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div class="row mb-3">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <div class="card mb-0">
                            <div class="card-body text-center py-3">
                                <div style="font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:#adb5bd; font-weight:600;">Total Visitor</div>
                                <div style="font-size:26px; font-weight:700; color:#495057;">{{ $totalVisitors }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <div class="card mb-0">
                            <div class="card-body text-center py-3">
                                <div style="font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:#adb5bd; font-weight:600;">
                                    <i class="fas fa-check-circle text-success"></i> Member
                                </div>
                                <div style="font-size:26px; font-weight:700; color:#28a745;">{{ $memberCount }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-0">
                            <div class="card-body text-center py-3">
                                <div style="font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:#adb5bd; font-weight:600;">Non-Member (Visitor)</div>
                                <div style="font-size:26px; font-weight:700; color:#6c757d;">{{ $nonMemberCount }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center" style="gap:12px; flex-wrap:wrap;">
                        <div>
                            <h4 class="mb-0">{{ $event->name }}</h4>
                            <small class="text-muted">
                                {{ $event->start_date ? \Carbon\Carbon::parse($event->start_date)->format('d M Y') : '-' }}
                                &ndash;
                                {{ $event->end_date ? \Carbon\Carbon::parse($event->end_date)->format('d M Y') : '-' }}
                                &middot; {{ $event->location ?: '-' }}
                            </small>
                        </div>
                        <div class="d-flex align-items-center" style="gap:10px;">
                            <form action="{{ route('admin.partnership_events.toggle_giveaway', $event->slug) }}" method="POST" class="mb-0">
                                @csrf
                                @if ($event->has_giveaway)
                                    <button type="submit" class="btn btn-sm btn-success" title="Giveaway saat ini AKTIF untuk booth event ini. Klik untuk mematikan.">
                                        <i class="fas fa-gift"></i> Giveaway: <strong>ON</strong>
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-sm btn-secondary" title="Giveaway saat ini NONAKTIF untuk booth event ini. Klik untuk menyalakan.">
                                        <i class="fas fa-ban"></i> Giveaway: <strong>OFF</strong>
                                    </button>
                                @endif
                            </form>

                            <a href="{{ url('visit/' . $event->slug) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Buka Form Booth Visitor / Salin Link untuk QR Code">
                                <i class="fas fa-external-link-alt"></i> Buka Form Booth
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.partnership_events.show', $event->slug) }}" class="mb-3">
                            <div class="form-row align-items-end">
                                <div class="form-group col-md-5 mb-2">
                                    <label class="mb-1">Cari Visitor</label>
                                    <input type="text" name="search" class="form-control" value="{{ $search }}"
                                        placeholder="Company Name / Name / Email">
                                </div>
                                <div class="form-group col-md-3 mb-2">
                                    <label class="mb-1">Membership</label>
                                    <select name="membership" class="form-control">
                                        <option value="" {{ $membership === '' ? 'selected' : '' }}>Semua</option>
                                        <option value="member" {{ $membership === 'member' ? 'selected' : '' }}>Member</option>
                                        <option value="visitor" {{ $membership === 'visitor' ? 'selected' : '' }}>Non-Member (Visitor)</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3 mb-2" style="display:flex; gap:8px;">
                                    <button type="submit" class="btn btn-primary btn-block">Filter</button>
                                    <a href="{{ route('admin.partnership_events.show', $event->slug) }}" class="btn btn-outline-secondary btn-block">Reset</a>
                                </div>
                            </div>
                        </form>

                        <div class="d-flex mb-3" style="gap:8px; flex-wrap:wrap;">
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#visitorFormModal"
                                onclick="openAddVisitorModal()">
                                <i class="fas fa-plus"></i> Tambah Visitor
                            </button>

                            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#importModal">
                                <i class="fas fa-file-upload"></i> Import Excel
                            </button>

                            <a href="{{ route('admin.partnership_events.export', [$event->slug, 'search' => $search, 'membership' => $membership]) }}" class="btn btn-success">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm" id="partnership-visitor-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Company Name</th>
                                        <th>Name</th>
                                        <th>Job Title</th>
                                        <th>Business Email</th>
                                        <th>Mobile Number</th>
                                        <th>Office Number</th>
                                        <th>Website</th>
                                        <th>Address</th>
                                        <th>Remarks</th>
                                        <th>Merchandise</th>
                                        <th>Membership</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($visitors as $idx => $visitor)
                                        @php
                                            $isMember = $visitor->business_email
                                                && $memberEmails->contains(strtolower(trim($visitor->business_email)));
                                        @endphp
                                        <tr>
                                            <td>{{ $visitors->firstItem() + $idx }}</td>
                                            <td>{{ $visitor->company_name ?: '-' }}</td>
                                            <td>{{ $visitor->name ?: '-' }}</td>
                                            <td>{{ $visitor->job_title ?: '-' }}</td>
                                            <td>{{ $visitor->business_email ?: '-' }}</td>
                                            <td>{{ $visitor->mobile_number ?: '-' }}</td>
                                            <td>{{ $visitor->office_number ?: '-' }}</td>
                                            <td class="text-truncate-cell" title="{{ $visitor->website }}">{{ $visitor->website ?: '-' }}</td>
                                            <td class="text-truncate-cell" title="{{ $visitor->address }}">{{ $visitor->address ?: '-' }}</td>
                                            <td class="text-truncate-cell" title="{{ $visitor->remarks }}">{{ $visitor->remarks ?: '-' }}</td>
                                            <td>{{ $visitor->merchandise ?: '-' }}</td>
                                            <td class="text-nowrap">
                                                @if ($isMember)
                                                    <span class="badge badge-success"><i class="fas fa-check-circle"></i> Member</span>
                                                @else
                                                    <span class="badge badge-secondary d-block mb-1" style="width:fit-content;">Visitor</span>
                                                    @if ($visitor->business_email)
                                                        <form method="POST"
                                                            action="{{ route('admin.partnership_events.visitors.register_as_member', [$event->slug, $visitor->id]) }}"
                                                            onsubmit="return confirm('Daftarkan {{ addslashes($visitor->name ?: $visitor->company_name) }} sebagai calon member?')">
                                                            @csrf
                                                            <button type="submit" class="btn btn-xs btn-outline-primary" style="font-size:11px; padding:2px 6px;">
                                                                <i class="fas fa-user-plus"></i> Register as Member
                                                            </button>
                                                        </form>
                                                    @else
                                                        <button type="button" class="btn btn-xs btn-outline-secondary" style="font-size:11px; padding:2px 6px;" disabled
                                                            title="Business Email kosong, lengkapi dulu">
                                                            <i class="fas fa-user-plus"></i> Register as Member
                                                        </button>
                                                    @endif
                                                @endif
                                            </td>
                                            <td class="text-nowrap">
                                                <button type="button" class="btn btn-sm btn-warning js-edit-visitor"
                                                    data-toggle="modal" data-target="#visitorFormModal"
                                                    data-payload='@json($visitor)'>
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST"
                                                    action="{{ route('admin.partnership_events.visitors.destroy', [$event->slug, $visitor->id]) }}"
                                                    style="display:inline;"
                                                    onsubmit="return confirm('Hapus visitor {{ addslashes($visitor->name ?: $visitor->company_name) }}?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="13" class="text-center text-muted">Belum ada data visitor untuk event ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{ $visitors->links() }}
                    </div>
                </div>
            </div>
        </section>
    </div>

    @include('admin.partnership_event._import_modal')
    @include('admin.partnership_event._visitor_form_modal')
@endsection

@push('bottom')
    <script>
        function openAddVisitorModal() {
            $('#visitorFormModalTitle').text('Tambah Visitor');
            $('#visitorForm').attr('action', '{{ route('admin.partnership_events.visitors.store', $event->slug) }}');
            $('#visitorForm')[0].reset();
        }

        $(document).ready(function() {
            $(document).on('click', '.js-edit-visitor', function() {
                var payload = {};
                try {
                    payload = JSON.parse($(this).attr('data-payload') || '{}');
                } catch (e) {
                    payload = {};
                }

                $('#visitorFormModalTitle').text('Edit Visitor');
                $('#visitorForm').attr('action',
                    '{{ url('admin/partnership-events/' . $event->slug . '/visitors') }}/' + payload.id + '/update');

                $('#vf_company_name').val(payload.company_name || '');
                $('#vf_name').val(payload.name || '');
                $('#vf_job_title').val(payload.job_title || '');
                $('#vf_business_email').val(payload.business_email || '');
                $('#vf_mobile_number').val(payload.mobile_number || '');
                $('#vf_office_number').val(payload.office_number || '');
                $('#vf_website').val(payload.website || '');
                $('#vf_address').val(payload.address || '');
                $('#vf_remarks').val(payload.remarks || '');
                $('#vf_merchandise').val(payload.merchandise || '');
            });
        });
    </script>
@endpush
