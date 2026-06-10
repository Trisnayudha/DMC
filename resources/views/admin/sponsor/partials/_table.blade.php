<!-- Sponsors Management Table -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Sponsors Management</h4>
                <form method="GET" action="{{ route('sponsors.index') }}" class="form-inline">
                    <div class="input-group mr-2">
                        <select name="type" id="filterType" class="form-control" onchange="this.form.submit()">
                            <option value="">All Packages</option>
                            <option value="platinum" {{ request('type') == 'platinum' ? 'selected' : '' }}>Platinum</option>
                            <option value="gold"     {{ request('type') == 'gold'     ? 'selected' : '' }}>Gold</option>
                            <option value="silver"   {{ request('type') == 'silver'   ? 'selected' : '' }}>Silver</option>
                        </select>
                    </div>
                    <div class="input-group mr-2">
                        <select name="status" id="filterStatus" class="form-control" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="publish" {{ request('status') == 'publish' ? 'selected' : '' }}>Active</option>
                            <option value="draft"   {{ request('status') == 'draft'   ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="input-group mr-2">
                        <select name="renewal_year" class="form-control" onchange="this.form.submit()">
                            <option value="">All Years</option>
                            @foreach ($availableYears as $year)
                                <option value="{{ $year }}" {{ request('renewal_year') == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="input-group mr-2">
                        <select name="renewal_state" class="form-control" onchange="this.form.submit()">
                            <option value="">All Renewal Status</option>
                            <option value="renewed"     {{ request('renewal_state') == 'renewed'     ? 'selected' : '' }}>Renewed</option>
                            <option value="new_sponsor" {{ request('renewal_state') == 'new_sponsor' ? 'selected' : '' }}>New Sponsor</option>
                            <option value="not_renewed" {{ request('renewal_state') == 'not_renewed' ? 'selected' : '' }}>Not Renewed</option>
                        </select>
                    </div>
                    <a href="{{ route('sponsors.exportRenewals', [
                        'renewal_year'  => request('renewal_year'),
                        'renewal_state' => request('renewal_state'),
                        'type'          => request('type'),
                        'status'        => request('status'),
                    ]) }}" class="btn btn-success mr-1">
                        <i class="fas fa-file-excel"></i> Export Renewal Data
                    </a>
                    @if (request('type') || request('status') || request('renewal_year') || request('renewal_state'))
                        <a href="{{ route('sponsors.index') }}" class="btn btn-secondary">Reset</a>
                    @endif
                </form>
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

                <div class="float-right">
                    <div class="card-header-action mb-2">
                        <a href="{{ route('sponsors.annual-report') }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-chart-bar"></i> Annual Report
                        </a>
                        <a href="{{ route('sponsors.export') }}" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Export Data
                        </a>
                        <a href="{{ route('sponsors.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Sponsor
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="laravel_crud" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th width="10px">No</th>
                                <th>Name Sponsor</th>
                                <th>Package</th>
                                <th>Status Display</th>
                                <th>Renewal Info</th>
                                <th width="15%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            @foreach ($data as $post)
                                <tr>
                                    <td>{{ $no++ }}</td>
                                    <td>
                                        <div class="font-weight-bold">{{ $post->name }}</div>
                                        @if ($post->firstPic)
                                            @php $pic = $post->firstPic; @endphp
                                            <div class="d-flex align-items-center mt-1" style="gap:6px;">
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
                                                            <a href="mailto:{{ $pic->email }}" style="font-size:11px;color:#007bff;" title="{{ $pic->email }}">
                                                                <i class="fas fa-envelope"></i> {{ Str::limit($pic->email, 22) }}
                                                            </a>
                                                        @endif
                                                        @if($pic->phone)
                                                            <a href="tel:{{ $pic->phone }}" style="font-size:11px;color:#28a745;" title="{{ $pic->phone }}">
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
                                            @if ($post->package == 'silver') badge-secondary
                                            @elseif($post->package == 'gold') badge-warning
                                            @elseif($post->package == 'platinum') badge-primary @endif">
                                            {{ ucfirst($post->package) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center" style="gap:8px;">
                                            <div class="custom-control custom-switch mb-0">
                                                <input type="checkbox"
                                                    class="custom-control-input toggle-status"
                                                    data-id="{{ $post->id }}"
                                                    id="statusToggle{{ $post->id }}"
                                                    {{ $post->status == 'publish' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="statusToggle{{ $post->id }}"></label>
                                            </div>
                                            @if ($post->status == 'publish')
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-secondary">Inactive</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $currentR = $post->renewals->where('is_current', 1)->first()
                                                ?? $post->renewals->sortByDesc('contract_start')->first();
                                            $typeLabels = [
                                                'renewal'    => 'Renewal',
                                                'upgrade'    => 'Upgrade',
                                                'new'        => 'New Sponsor',
                                                'new_member' => 'New Member',
                                            ];
                                        @endphp
                                        @if (request('renewal_year'))
                                            @php
                                                $renewalRecord = $post->renewals
                                                    ->where('renewal_year', (int) request('renewal_year'))
                                                    ->where('renewal_status', 'renewed')
                                                    ->first();
                                                $notRenewedRecord = $post->renewals
                                                    ->where('renewal_year', (int) request('renewal_year'))
                                                    ->where('renewal_status', 'not_renewed')
                                                    ->first();
                                            @endphp
                                            @if ($renewalRecord)
                                                <span class="badge badge-success">{{ $typeLabels[$renewalRecord->renewal_type] ?? 'Renewed' }}</span>
                                            @elseif($notRenewedRecord)
                                                <span class="badge badge-danger">Not Renewed</span>
                                            @else
                                                <span class="badge badge-secondary">No Record</span>
                                            @endif
                                        @elseif($currentR)
                                            @if($currentR->renewal_status === 'renewed')
                                                <small class="text-muted">{{ $currentR->contract_start }} s/d {{ $currentR->contract_end }}</small><br>
                                                <span class="badge badge-{{ $currentR->renewal_type === 'upgrade' ? 'info' : 'success' }}">
                                                    {{ $typeLabels[$currentR->renewal_type] ?? 'Renewed' }}
                                                </span>
                                            @else
                                                <span class="badge badge-secondary">No Active Contract</span>
                                            @endif
                                        @else
                                            <span class="badge badge-secondary">No Record</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap align-items-center" style="gap:4px;">
                                            <a href="{{ route('sponsors-advertising.show', $post->id) }}"
                                                class="btn btn-sm btn-primary action-icon-btn" data-toggle="tooltip" title="Advertisement/Brochure">
                                                <i class="fas fa-bullhorn"></i>
                                            </a>
                                            <a href="{{ route('sponsors-representative.show', $post->id) }}"
                                                class="btn btn-sm btn-warning action-icon-btn" data-toggle="tooltip" title="Sponsor Representative">
                                                <i class="fas fa-user-friends"></i>
                                            </a>
                                            <a href="{{ route('sponsors-address.show', $post->id) }}"
                                                class="btn btn-sm btn-info action-icon-btn" data-toggle="tooltip" title="Sponsor Address">
                                                <i class="fas fa-map-marker-alt"></i>
                                            </a>
                                            <a href="{{ route('photos-videos-activity.show', $post->id) }}"
                                                class="btn btn-sm btn-secondary action-icon-btn" data-toggle="tooltip" title="Photos/Videos Activity">
                                                <i class="fas fa-camera"></i>
                                            </a>
                                            <a href="{{ route('sponsors.benefit.detail', $post->id) }}"
                                                class="btn btn-sm btn-info action-icon-btn" data-toggle="tooltip" title="Sponsor Benefit Management">
                                                <i class="fas fa-chart-bar"></i>
                                            </a>
                                            <a href="{{ route('sponsors.edit', $post->id) }}"
                                                class="btn btn-sm btn-success action-icon-btn" data-toggle="tooltip" title="Edit Data">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                            <button class="btn btn-sm btn-warning action-icon-btn not-renewed-btn"
                                                data-id="{{ $post->id }}"
                                                data-name="{{ $post->name }}"
                                                data-contract-start="{{ $post->contract_start }}"
                                                data-contract-end="{{ $post->contract_end }}"
                                                data-toggle="tooltip" title="Mark Not Renewed">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger action-icon-btn delete-sponsor"
                                                data-id="{{ $post->id }}" data-toggle="tooltip" title="Delete Sponsor">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
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
</div>
