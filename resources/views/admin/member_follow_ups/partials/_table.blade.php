{{-- Main table card: status tabs + search/filter + table --}}

<div class="card">
    <div class="card-header">
        <h4 class="mb-0">
            <i class="fas fa-exchange-alt mr-1"></i>Company Change Follow-Up
        </h4>
    </div>

    <div class="card-body">

        {{-- Status tabs --}}
        <div class="mb-3">
            <div class="d-flex flex-wrap" style="gap:6px;">
                <a href="{{ url('admin/company-follow-ups?' . http_build_query(array_merge(request()->except('status'), ['status' => 'needs_follow_up']))) }}"
                    class="btn btn-sm {{ $status === 'needs_follow_up' ? 'btn-warning' : 'btn-outline-warning' }}">
                    <i class="fas fa-exchange-alt mr-1"></i> Need Follow Up
                    <span class="badge badge-light ml-1">{{ $countNeedsFollowUp }}</span>
                </a>
                <a href="{{ url('admin/company-follow-ups?' . http_build_query(array_merge(request()->except('status'), ['status' => 'verified']))) }}"
                    class="btn btn-sm {{ $status === 'verified' ? 'btn-success' : 'btn-outline-success' }}">
                    <i class="fas fa-check-circle mr-1"></i> Verified
                    <span class="badge badge-light ml-1">{{ $countVerified }}</span>
                </a>
                <a href="{{ url('admin/company-follow-ups?' . http_build_query(array_merge(request()->except('status'), ['status' => 'all']))) }}"
                    class="btn btn-sm {{ $status === 'all' ? 'btn-dark' : 'btn-outline-secondary' }}">
                    <i class="fas fa-list mr-1"></i> All
                </a>
            </div>
        </div>

        {{-- Search + filters --}}
        <form action="{{ url('admin/company-follow-ups') }}" method="GET"
            class="d-flex flex-wrap align-items-end mb-3 border-top pt-3" style="gap:10px;">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="form-group mb-0" style="min-width:220px; flex:1 1 220px;">
                <label class="mb-1 small text-muted">Search</label>
                <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm"
                    placeholder="Nama, email, company, job title, notes...">
            </div>
            <div class="form-group mb-0">
                <label class="mb-1 small text-muted">From Date</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control form-control-sm">
            </div>
            <div class="form-group mb-0">
                <label class="mb-1 small text-muted">To Date</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control form-control-sm">
            </div>
            <div class="form-group mb-0">
                <label class="mb-1 small text-muted">Flagged By</label>
                <select name="pic_id" class="form-control form-control-sm">
                    <option value="">All</option>
                    @foreach ($pics as $pic)
                        <option value="{{ $pic->id }}" {{ (string) $picId === (string) $pic->id ? 'selected' : '' }}>
                            {{ $pic->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-0">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i> Filter</button>
                <a href="{{ url('admin/company-follow-ups?status=' . $status) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times mr-1"></i> Clear
                </a>
            </div>
        </form>

        <div class="table-responsive follow-up-table-wrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="10px">No</th>
                        <th class="text-nowrap">Flagged</th>
                        <th>Member</th>
                        <th class="text-nowrap">Phone</th>
                        <th>Company (Previous → New)</th>
                        <th>Job Title (Previous → New)</th>
                        <th>Notes</th>
                        <th width="110px">Status</th>
                        <th class="text-nowrap">Flagged By</th>
                        <th class="text-nowrap">Verified By</th>
                        <th width="90px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    @forelse ($list as $item)
                        @php
                            $member = $item->user;
                            $memberProfile = optional($member)->profile;
                            // Same resolution path as UsersController::updateUser() —
                            // profiles.company_id is the primary link, not the
                            // separate/legacy User::company() relation.
                            $memberCompany = optional($memberProfile)->company;
                            $phone = optional($memberProfile)->fullphone ?? optional($memberProfile)->phone;

                            $waDigits = preg_replace('/\D/', '', (string) $phone);
                            if ($waDigits !== '' && substr($waDigits, 0, 1) === '0') {
                                $waDigits = '62' . substr($waDigits, 1);
                            } elseif ($waDigits !== '' && substr($waDigits, 0, 2) !== '62') {
                                $waDigits = '62' . $waDigits;
                            }

                            $isVerified = $item->status === \App\Models\MemberCompanyFollowUp::STATUS_VERIFIED;

                            $companyTitle = trim(($item->previous_company_name ?: '—') . ' → ' . ($item->new_company_name ?: '—'));
                            $jobTitleTitle = trim(($item->previous_job_title ?: '—') . ' → ' . ($item->new_job_title ?: '—'));
                        @endphp
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td class="text-nowrap"><small>{{ $item->created_at ? $item->created_at->format('d M Y H:i') : '-' }}</small></td>
                            <td>
                                <span class="cell-truncate font-weight-bold" title="{{ optional($member)->name }}">{{ optional($member)->name ?? '(deleted user)' }}</span>
                                <br>
                                <span class="cell-truncate text-muted" style="font-size:10.5px;" title="{{ optional($member)->email }}">{{ optional($member)->email }}</span>
                            </td>
                            <td class="text-nowrap">
                                {{ $phone }}
                                @if ($waDigits !== '')
                                    <a href="https://wa.me/{{ $waDigits }}" target="_blank" rel="noopener"
                                        class="btn btn-icon btn-outline-success" title="Chat via WhatsApp" data-toggle="tooltip">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                @endif
                            </td>
                            <td>
                                @if ($item->previous_company_name || $item->new_company_name)
                                    <span class="cell-truncate" style="max-width:220px;" title="{{ $companyTitle }}">
                                        <span class="text-muted">{{ $item->previous_company_name ?: '—' }}</span>
                                        <i class="fas fa-long-arrow-alt-right mx-1 text-muted" style="font-size:9px;"></i>
                                        <span class="font-weight-bold">{{ $item->new_company_name ?: '—' }}</span>
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($item->previous_job_title || $item->new_job_title)
                                    <span class="cell-truncate" style="max-width:200px;" title="{{ $jobTitleTitle }}">
                                        <span class="text-muted">{{ $item->previous_job_title ?: '—' }}</span>
                                        <i class="fas fa-long-arrow-alt-right mx-1 text-muted" style="font-size:9px;"></i>
                                        <span class="font-weight-bold">{{ $item->new_job_title ?: '—' }}</span>
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($item->notes)
                                    <span class="cell-truncate" style="max-width:160px;" title="{{ $item->notes }}">{{ $item->notes }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($isVerified)
                                    <span class="badge badge-success mini-badge"><i class="fas fa-check mr-1"></i>Verified</span>
                                @else
                                    <span class="badge badge-warning mini-badge"><i class="fas fa-clock mr-1"></i>Needs Follow Up</span>
                                @endif
                            </td>
                            <td class="text-nowrap"><small>{{ $item->flagged_by_name }}</small></td>
                            <td class="text-nowrap">
                                @if ($isVerified)
                                    <small>{{ $item->verified_by_name }}<br>
                                        <span class="text-muted">{{ optional($item->verified_at)->format('d M Y H:i') }}</span></small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-icon-group">
                                    @if (!$isVerified && $member)
                                        <button type="button"
                                            class="btn btn-icon btn-outline-primary btn-open-follow-up-modal"
                                            data-user-id="{{ $member->id }}"
                                            data-member-name="{{ $member->name }}"
                                            data-current-company="{{ $item->previous_company_name }}"
                                            data-new-company="{{ $item->new_company_name }}"
                                            data-current-job-title="{{ $item->previous_job_title }}"
                                            data-new-job-title="{{ $item->new_job_title }}"
                                            data-notes="{{ $item->notes }}"
                                            title="Edit flag ini (belum menyimpan ke data member)" data-toggle="tooltip">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button"
                                            class="btn btn-icon btn-success btn-open-edit-member-modal"
                                            data-follow-up-id="{{ $item->id }}"
                                            data-update-url="{{ route('admin.member_follow_ups.update', $item->id) }}"
                                            data-member-name="{{ $member->name }}"
                                            data-flagged-company="{{ $item->new_company_name }}"
                                            data-flagged-job-title="{{ $item->new_job_title }}"
                                            data-name="{{ $member->name }}"
                                            data-email="{{ $member->email }}"
                                            data-job-title="{{ $item->new_job_title ?: optional($memberProfile)->job_title }}"
                                            data-phone="{{ $phone }}"
                                            data-prefix="{{ optional($memberCompany)->prefix }}"
                                            data-company-name="{{ $item->new_company_name ?: optional($memberCompany)->company_name }}"
                                            data-company-website="{{ optional($memberCompany)->company_website }}"
                                            data-company-category="{{ optional($memberCompany)->company_category }}"
                                            data-company-other="{{ optional($memberCompany)->company_other }}"
                                            data-address="{{ optional($memberCompany)->address }}"
                                            data-city="{{ optional($memberCompany)->city }}"
                                            data-portal-code="{{ optional($memberCompany)->portal_code }}"
                                            data-country="{{ optional($memberCompany)->country }}"
                                            data-prefix-office-number="{{ optional($memberCompany)->prefix_office_number }}"
                                            data-office-number="{{ optional($memberCompany)->office_number }}"
                                            data-full-office-number="{{ optional($memberCompany)->full_office_number }}"
                                            title="Edit data member & verify — update langsung, tanpa bikin akun baru" data-toggle="tooltip">
                                            <i class="fas fa-user-check"></i>
                                        </button>
                                    @endif
                                    @if ($member)
                                        <button type="button"
                                            class="btn btn-icon btn-outline-secondary btn-view-follow-up-logs"
                                            data-logs-url="{{ route('users.logs', $member->id) }}"
                                            data-name="{{ $member->name }}"
                                            title="Riwayat perubahan member ini" data-toggle="tooltip">
                                            <i class="fas fa-history"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">Tidak ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>{{-- /card --}}

<style>
    /* Compact, modern look — same treatment as the Members DMC table
       (resources/views/admin/users/partials/_table.blade.php), scoped here so
       it doesn't leak into other pages. */
    .follow-up-table-wrap table {
        border-collapse: separate !important;
        border: none;
    }
    .follow-up-table-wrap table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f8f9fc;
        border-top: none !important;
        border-bottom: 2px solid #e3e6f0 !important;
        border-left: none !important;
        border-right: none !important;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #6c757d;
        white-space: nowrap;
        vertical-align: middle;
        padding: .4rem .5rem;
    }
    .follow-up-table-wrap table tbody td {
        border-left: none !important;
        border-right: none !important;
        border-top: none !important;
        border-bottom: 1px solid #eef0f4 !important;
        vertical-align: middle;
        font-size: 11.5px;
        padding: .4rem .5rem;
    }
    .follow-up-table-wrap table tbody tr:hover {
        background-color: #f7f9fc;
    }

    /* Icon-only action buttons — same as Members DMC's Actions column */
    .follow-up-table-wrap .btn-icon {
        width: 22px;
        height: 22px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        line-height: 1;
        border-radius: 5px;
    }
    .follow-up-table-wrap .btn-icon-group {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 3px;
    }

    /* Small pill badges (status) */
    .follow-up-table-wrap .mini-badge {
        font-size: 9.5px;
        padding: .2em .45em;
        font-weight: 600;
    }

    /* Long free-text fields (member, company/job title pairs, notes) —
       truncate with ellipsis, full value available via title="" on hover. */
    .follow-up-table-wrap .cell-truncate {
        display: inline-block;
        max-width: 160px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
    }
</style>
