@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Nearing Contract End</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('sponsors.index') }}">Sponsors Management</a></div>
                    <div class="breadcrumb-item active">Nearing Contract End</div>
                </div>
                <div class="section-header-button ml-auto">
                    <a href="{{ route('sponsors.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <div class="section-body">

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:8px">
                        <div>
                            <h4 class="mb-0">
                                <i class="fas fa-hourglass-half text-warning mr-2"></i>
                                Sponsors Nearing Contract End
                                <span class="badge badge-warning ml-1">{{ $data->count() }}</span>
                            </h4>
                            <small class="text-muted">Active sponsors with contracts ending within the next 3 months — sorted by most urgent</small>
                        </div>
                        <form method="GET" action="{{ route('sponsors.nearing-contract') }}" class="form-inline" style="gap:6px">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search sponsor…" value="{{ $search }}" style="min-width:180px">
                            <select name="type" class="form-control form-control-sm" onchange="this.form.submit()">
                                <option value="">All Packages</option>
                                <option value="platinum" {{ $type === 'platinum' ? 'selected' : '' }}>Platinum</option>
                                <option value="gold"     {{ $type === 'gold'     ? 'selected' : '' }}>Gold</option>
                                <option value="silver"   {{ $type === 'silver'   ? 'selected' : '' }}>Silver</option>
                            </select>
                            <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-search"></i></button>
                            @if($search || $type)
                                <a href="{{ route('sponsors.nearing-contract') }}" class="btn btn-light btn-sm">Reset</a>
                            @endif
                        </form>
                    </div>

                    <div class="card-body p-0">
                        @if($data->isEmpty())
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-check-circle fa-3x text-success mb-3 d-block"></i>
                                No contracts ending soon. All good!
                            </div>
                        @else
                        <div class="table-responsive">
                            <table id="nearing_contract_table" class="table table-bordered table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th width="10px">No</th>
                                        <th>Name Sponsor</th>
                                        <th style="width:90px">Package</th>
                                        <th style="width:130px">Contract End</th>
                                        <th style="width:170px">Time Left</th>
                                        <th style="width:120px">Status Display</th>
                                        <th style="width:180px">Renewal Info</th>
                                        <th width="15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $no = 1; @endphp
                                    @foreach ($data as $post)
                                        @php
                                            $endDate  = \Carbon\Carbon::createFromFormat('Y-m', $post->contract_end)->endOfMonth();
                                            $daysLeft = (int) now()->diffInDays($endDate, false);
                                            if ($daysLeft <= 30) {
                                                $rowBorder   = 'border-left: 4px solid #dc3545;';
                                                $badgeColor  = 'danger';
                                                $urgencyText = 'Urgent';
                                            } elseif ($daysLeft <= 60) {
                                                $rowBorder   = 'border-left: 4px solid #ffc107;';
                                                $badgeColor  = 'warning';
                                                $urgencyText = 'Moderate';
                                            } else {
                                                $rowBorder   = 'border-left: 4px solid #17a2b8;';
                                                $badgeColor  = 'info';
                                                $urgencyText = 'Upcoming';
                                            }
                                            $pct = max(0, min(100, round((90 - $daysLeft) / 90 * 100)));

                                            $currentR = $post->renewals->where('is_current', 1)->first()
                                                ?? $post->renewals->sortByDesc('contract_start')->first();
                                            $typeLabels = [
                                                'renewal'    => 'Renewal',
                                                'upgrade'    => 'Upgrade',
                                                'new'        => 'New Sponsor',
                                                'new_member' => 'New Member',
                                            ];
                                        @endphp
                                        <tr style="{{ $rowBorder }}">
                                            <td>{{ $no++ }}</td>
                                            <td>
                                                <div class="font-weight-bold">{{ $post->name }}</div>
                                                @if ($post->firstPic)
                                                    @php $pic = $post->firstPic; @endphp
                                                    <div class="d-flex align-items-center mt-1" style="gap:6px">
                                                        <div style="width:28px;height:28px;border-radius:50%;background:#6c757d;color:#fff;font-size:11px;font-weight:600;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                            {{ strtoupper(substr($pic->name, 0, 1)) }}
                                                        </div>
                                                        <div style="line-height:1.3;">
                                                            <div style="font-size:12px;font-weight:500;color:#333;">{{ $pic->name }}</div>
                                                            @if($pic->title)
                                                                <div style="font-size:11px;color:#888;">{{ $pic->title }}</div>
                                                            @endif
                                                            <div class="d-flex mt-1" style="gap:8px;flex-wrap:wrap;">
                                                                @if($pic->email)
                                                                    <a href="mailto:{{ $pic->email }}" style="font-size:11px;color:#007bff;">
                                                                        <i class="fas fa-envelope"></i> {{ Str::limit($pic->email, 22) }}
                                                                    </a>
                                                                @endif
                                                                @if($pic->phone)
                                                                    <a href="tel:{{ $pic->phone }}" style="font-size:11px;color:#28a745;">
                                                                        <i class="fas fa-phone"></i> {{ $pic->phone }}
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div style="font-size:11px;color:#bbb;margin-top:3px;"><i class="fas fa-user-slash"></i> No PIC</div>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge
                                                    @if($post->package === 'platinum') badge-primary
                                                    @elseif($post->package === 'gold') badge-warning
                                                    @else badge-secondary @endif">
                                                    {{ ucfirst($post->package) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="font-weight-bold text-{{ $badgeColor }}">
                                                    {{ \Carbon\Carbon::createFromFormat('Y-m', $post->contract_end)->format('M Y') }}
                                                </div>
                                                <small class="text-muted" style="font-size:11px">{{ $post->contract_end }}</small>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center mb-1" style="gap:6px">
                                                    <span class="badge badge-{{ $badgeColor }}">{{ $urgencyText }}</span>
                                                    <span class="text-muted" style="font-size:12px">{{ $daysLeft }} days left</span>
                                                </div>
                                                <div class="progress" style="height:5px;border-radius:3px">
                                                    <div class="progress-bar bg-{{ $badgeColor }}" style="width:{{ $pct }}%"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center" style="gap:8px">
                                                    <div class="custom-control custom-switch mb-0">
                                                        <input type="checkbox"
                                                            class="custom-control-input toggle-status"
                                                            data-id="{{ $post->id }}"
                                                            id="statusToggle{{ $post->id }}"
                                                            {{ $post->status == 'publish' ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="statusToggle{{ $post->id }}"></label>
                                                    </div>
                                                    <span class="badge badge-success">Active</span>
                                                </div>
                                            </td>
                                            <td>
                                                @if($currentR && $currentR->renewal_status === 'renewed')
                                                    <small class="text-muted">{{ $currentR->contract_start }} – {{ $currentR->contract_end }}</small><br>
                                                    <span class="badge badge-{{ $currentR->renewal_type === 'upgrade' ? 'info' : 'success' }}">
                                                        {{ $typeLabels[$currentR->renewal_type] ?? 'Renewed' }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-secondary">No Active Contract</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap" style="gap:4px; max-width:150px">
                                                    <a href="#" class="btn btn-icon btn-sm btn-primary update-contract-btn"
                                                       data-sponsor-id="{{ $post->id }}"
                                                       data-contract-start="{{ $post->contract_start }}"
                                                       data-contract-end="{{ $post->contract_end }}"
                                                       data-package="{{ $post->package }}"
                                                       data-toggle="tooltip" title="Renew / Update Contract">
                                                        <i class="fas fa-sync-alt"></i>
                                                    </a>
                                                    <a href="{{ route('sponsors-advertising.show', $post->id) }}"
                                                        class="btn btn-icon btn-sm btn-primary" data-toggle="tooltip" title="Advertisement/Brochure">
                                                        <i class="fas fa-bullhorn"></i>
                                                    </a>
                                                    <a href="{{ route('sponsors-representative.show', $post->id) }}"
                                                        class="btn btn-icon btn-sm btn-warning" data-toggle="tooltip" title="Sponsor Representative">
                                                        <i class="fas fa-user-friends"></i>
                                                    </a>
                                                    <a href="{{ route('sponsors-address.show', $post->id) }}"
                                                        class="btn btn-icon btn-sm btn-info" data-toggle="tooltip" title="Sponsor Address">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                    </a>
                                                    <a href="{{ route('sponsors.benefit.detail', $post->id) }}"
                                                        class="btn btn-icon btn-sm btn-info" data-toggle="tooltip" title="Benefit Management">
                                                        <i class="fas fa-chart-bar"></i>
                                                    </a>
                                                    <a href="{{ route('sponsors.edit', $post->id) }}"
                                                        class="btn btn-icon btn-sm btn-success" data-toggle="tooltip" title="Edit Sponsor">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </a>
                                                    <button class="btn btn-icon btn-sm btn-warning not-renewed-btn"
                                                        data-id="{{ $post->id }}"
                                                        data-name="{{ $post->name }}"
                                                        data-contract-start="{{ $post->contract_start }}"
                                                        data-contract-end="{{ $post->contract_end }}"
                                                        data-toggle="tooltip" title="Mark Not Renewed">
                                                        <i class="fas fa-times-circle"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('bottom')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- Modal Update Contract / Renewal -->
    <div class="modal fade" id="updateContractModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="updateContractForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Update Contract / Renewal</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="modalSponsorId" name="sponsor_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Contract Start <span class="text-danger">*</span></label>
                                    <input type="month" name="contract_start" id="modalContractStart" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Contract End <span class="text-danger">*</span></label>
                                    <input type="month" name="contract_end" id="modalContractEnd" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Renewal Type <span class="text-danger">*</span></label>
                                    <select name="renewal_type" id="modalRenewalType" class="form-control" required>
                                        <option value="renewal">Renewal</option>
                                        <option value="upgrade">Renewal - Upgrade</option>
                                        <option value="new">New Sponsor</option>
                                        <option value="new_member">New Member</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Package <span class="text-danger">*</span></label>
                                    <select name="package" id="modalPackage" class="form-control" required>
                                        <option value="platinum">Platinum / Major</option>
                                        <option value="gold">Gold</option>
                                        <option value="silver">Silver</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>KMK Rate (USD/IDR)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">IDR</span></div>
                                        <input type="number" id="modalKmkRate" class="form-control" readonly placeholder="Loading...">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary" id="btnRefreshKmkRate" title="Refresh rate">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted">KMK Pajak — auto-fetched</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Amount USD</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">USD</span></div>
                                        <input type="number" name="amount_usd" id="modalAmountUsd" class="form-control" step="0.01" min="0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Amount IDR <small class="text-muted">(auto dari USD × KMK, bisa diubah)</small></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">IDR</span></div>
                                        <input type="text" id="modalAmountIdrDisplay" class="form-control" placeholder="e.g. 39.000.000">
                                        <input type="hidden" name="amount_idr" id="modalAmountIdr">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Notes / Final Confirmation</label>
                            <textarea name="notes" id="modalNotes" class="form-control" rows="2" placeholder="e.g. Confirmed - Gold Sponsorship USD 3,500"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Contract</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Not Renewed -->
    <div class="modal fade" id="notRenewedModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="notRenewedForm">
                    @csrf
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title"><i class="fas fa-times-circle"></i> Mark as Not Renewed</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3">Record <strong id="notRenewedSponsorName"></strong> as not renewed.</p>
                        <input type="hidden" id="notRenewedSponsorId">
                        <div class="form-group">
                            <label>Year of Non-Renewal <span class="text-danger">*</span></label>
                            <input type="number" name="renewal_year" id="notRenewedYear" class="form-control" min="2020" max="2100" value="{{ now()->year }}" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Last Contract Period (Start)</label>
                                    <input type="month" name="contract_start" id="notRenewedContractStart" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Last Contract Period (End)</label>
                                    <input type="month" name="contract_end" id="notRenewedContractEnd" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Reason for Not Renewing</label>
                            <textarea name="notes" id="notRenewedNotes" class="form-control" rows="3" placeholder="e.g. Budget is limited, they will focus on other priorities..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning"><i class="fas fa-times-circle"></i> Confirm Not Renewed</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('[data-toggle="tooltip"]').tooltip();

            // Toggle status
            $('.toggle-status').change(function() {
                let sponsorId = $(this).data('id');
                let status    = this.checked ? 'publish' : 'draft';
                $.ajax({
                    url: '/admin/sponsors/update-status/' + sponsorId,
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', status: status },
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Status updated successfully', 'Success', { positionClass: 'toast-top-right' });
                        } else {
                            toastr.error('Failed to update status', 'Error', { positionClass: 'toast-top-right' });
                        }
                    }
                });
            });

            function fetchKmkRate() {
                $('#modalKmkRate').val('').attr('placeholder', 'Loading...');
                $.get('/admin/sponsors/kmk-rate', function(res) {
                    if (res.success && res.rate) {
                        $('#modalKmkRate').val(res.rate);
                        autoFillIdr();
                    } else {
                        $('#modalKmkRate').attr('placeholder', 'Gagal fetch');
                    }
                }).fail(function() {
                    $('#modalKmkRate').attr('placeholder', 'Gagal fetch');
                });
            }

            function setIdrValue(raw) {
                let num = Math.round(parseFloat(raw));
                if (!isNaN(num) && num > 0) {
                    $('#modalAmountIdr').val(num);
                    $('#modalAmountIdrDisplay').val(num.toLocaleString('id-ID'));
                } else {
                    $('#modalAmountIdr').val('');
                    $('#modalAmountIdrDisplay').val('');
                }
            }

            function autoFillIdr() {
                let usd = parseFloat($('#modalAmountUsd').val());
                let rate = parseFloat($('#modalKmkRate').val());
                if (!isNaN(usd) && usd > 0 && !isNaN(rate) && rate > 0) {
                    setIdrValue(usd * rate);
                }
            }

            $('#modalAmountIdrDisplay').on('input', function() {
                let raw = $(this).val().replace(/\./g, '').replace(/,/g, '');
                $('#modalAmountIdr').val(raw);
            }).on('blur', function() {
                let raw = $(this).val().replace(/\./g, '').replace(/,/g, '');
                let num = parseInt(raw, 10);
                if (!isNaN(num) && num > 0) {
                    $(this).val(num.toLocaleString('id-ID'));
                    $('#modalAmountIdr').val(num);
                }
            });

            $('#modalAmountUsd').on('input', autoFillIdr);
            $('#modalKmkRate').on('change', autoFillIdr);
            $('#btnRefreshKmkRate').on('click', fetchKmkRate);

            // Open update contract modal
            $(document).on('click', '.update-contract-btn', function(e) {
                e.preventDefault();
                $('#modalSponsorId').val($(this).data('sponsor-id'));
                $('#modalContractStart').val($(this).data('contract-start'));
                $('#modalContractEnd').val($(this).data('contract-end'));
                $('#modalPackage').val($(this).data('package') || 'silver');
                $('#modalRenewalType').val('renewal');
                $('#modalAmountUsd, #modalAmountIdr, #modalAmountIdrDisplay, #modalNotes').val('');
                $('#updateContractModal').modal('show');
                fetchKmkRate();
            });

            // Submit update contract
            $('#updateContractForm').on('submit', function(e) {
                e.preventDefault();
                let sponsorId = $('#modalSponsorId').val();
                $.ajax({
                    url: '/admin/sponsors/' + sponsorId + '/update-contract',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message, 'Success', { positionClass: 'toast-top-right' });
                            $('#updateContractModal').modal('hide');
                            location.reload();
                        } else {
                            toastr.error(response.message, 'Error', { positionClass: 'toast-top-right' });
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message ?? 'An error occurred.', 'Error', { positionClass: 'toast-top-right' });
                    }
                });
            });

            // Open not renewed modal
            $(document).on('click', '.not-renewed-btn', function() {
                $('#notRenewedSponsorId').val($(this).data('id'));
                $('#notRenewedSponsorName').text($(this).data('name'));
                $('#notRenewedContractStart').val($(this).data('contract-start'));
                $('#notRenewedContractEnd').val($(this).data('contract-end'));
                $('#notRenewedYear').val(new Date().getFullYear());
                $('#notRenewedNotes').val('');
                $('#notRenewedModal').modal('show');
            });

            // Submit not renewed
            $('#notRenewedForm').on('submit', function(e) {
                e.preventDefault();
                let sponsorId = $('#notRenewedSponsorId').val();
                $.ajax({
                    url: '/admin/sponsors/' + sponsorId + '/mark-not-renewed',
                    method: 'POST',
                    data: {
                        _token:         '{{ csrf_token() }}',
                        renewal_year:   $('#notRenewedYear').val(),
                        contract_start: $('#notRenewedContractStart').val(),
                        contract_end:   $('#notRenewedContractEnd').val(),
                        notes:          $('#notRenewedNotes').val(),
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message, 'Success', { positionClass: 'toast-top-right' });
                            $('#notRenewedModal').modal('hide');
                            location.reload();
                        } else {
                            toastr.error(response.message, 'Error', { positionClass: 'toast-top-right' });
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message ?? 'An error occurred.', 'Error', { positionClass: 'toast-top-right' });
                    }
                });
            });
        });
    </script>
@endpush
