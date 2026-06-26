{{-- Table: registered members (User model) --}}
<table id="laravel_crud" class="table table-bordered table-hover">
    <thead class="thead-light">
        <tr>
            <th width="10px">No</th>
            <th>Date Register</th>
            <th>Name</th>
            <th width="140px">Tier</th>
            <th width="150px">
                Status Member
                <i class="fas fa-info-circle text-muted ml-1"
                    title="Active = sudah diverifikasi admin. Pending = belum diverifikasi."
                    data-toggle="tooltip"></i>
            </th>
            <th>Job Title</th>
            <th>Company</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Office</th>
            <th>Address</th>
            <th>Website</th>
            <th>Category</th>
            <th width="180px">
                CCI &amp; Sponsorship
                <i class="fas fa-info-circle text-muted ml-1"
                    title="CCI: anggota CCI. Open to Sponsorship: member bersedia menerima penawaran paket sponsorship."
                    data-toggle="tooltip"></i>
            </th>
            <th width="100px">Password</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1; ?>
        @foreach ($list as $post)
            @php
                $memberStatus  = strtolower($post->status_member ?? '');
                $isActive      = $memberStatus === 'active';
                $isDeclined    = $memberStatus === 'declined';
                $isDeactivated = $memberStatus === 'deactivated';
                $rowBg = $isActive ? '' : ($isDeclined ? 'background-color:#fff5f5;' : ($isDeactivated ? 'background-color:#f0f0f0;' : 'background-color:#fffbee;'));
            @endphp
            <tr id="row_{{ $post->user_id }}" style="{{ $rowBg }}">

                <td>{{ $no++ }}</td>

                <td class="text-nowrap">
                    {{ date('d M Y', strtotime($post->user_created_at ?? $post->created_at)) }}<br>
                    <small class="text-muted">{{ date('H:i', strtotime($post->user_created_at ?? $post->created_at)) }}</small>
                </td>

                <td>
                    {{ $post->name }}
                    @if (isset($selfEditMap[$post->user_id]))
                        <br>
                        <span class="badge badge-warning" style="font-size:10px; cursor:default;"
                            title="User mengubah data sendiri — {{ \Carbon\Carbon::parse($selfEditMap[$post->user_id])->format('d M Y H:i') }}"
                            data-toggle="tooltip">
                            <i class="fas fa-user-edit"></i> Self-edited
                        </span>
                    @endif
                </td>

                {{-- TIER --}}
                <td>
                    <div class="d-flex align-items-center">
                        <select class="form-control form-control-sm user-tier-select"
                            data-url="{{ route('users.update.tier', $post->user_id) }}"
                            style="max-width:110px;">
                            @php
                                $tier = strtolower((string) ($post->tier ?? 'reguler'));
                                if (!in_array($tier, ['reguler', 'black'])) { $tier = 'reguler'; }
                            @endphp
                            <option value="reguler" {{ $tier === 'reguler' ? 'selected' : '' }}>Reguler</option>
                            <option value="black" {{ $tier === 'black' ? 'selected' : '' }}>Black</option>
                        </select>
                        <span class="ml-1 badge badge-light tier-status" style="font-size:10px;">Saved</span>
                    </div>
                </td>

                {{-- STATUS MEMBER --}}
                <td>
                    <div class="d-flex flex-column align-items-start" style="gap:4px;">
                        @if ($isDeactivated)
                            <span class="badge badge-secondary member-status-badge">
                                <i class="fas fa-user-slash mr-1"></i>Deactivated
                            </span>
                            <button type="button"
                                class="btn btn-xs btn-outline-success btn-reactivate-member"
                                data-url="{{ route('users.reactivate', $post->user_id) }}"
                                data-name="{{ $post->name }}"
                                title="Reactivate member ini">
                                <i class="fas fa-undo"></i> Reactivate
                            </button>
                        @elseif ($isActive)
                            <span class="badge badge-success member-status-badge">
                                <i class="fas fa-check mr-1"></i>Active
                            </span>
                            <button type="button"
                                class="btn btn-xs btn-success btn-verify-member"
                                data-url="{{ route('users.verify', $post->user_id) }}"
                                disabled>
                                <i class="fas fa-check"></i> Verified
                            </button>
                            <button type="button"
                                class="btn btn-xs btn-outline-secondary btn-deactivate-member"
                                data-url="{{ route('users.deactivate', $post->user_id) }}"
                                data-name="{{ $post->name }}"
                                title="Deactivate member ini">
                                <i class="fas fa-user-slash"></i> Deactivate
                            </button>
                        @elseif ($isDeclined)
                            @php
                                $declinedCompanyVerified = !empty($post->is_verified) || !empty($post->has_verified_company_name);
                                $declinedPayload = [
                                    'company_name'         => $post->company_name,
                                    'prefix'               => $post->prefix,
                                    'company_website'      => $post->company_website,
                                    'company_category'     => $post->company_category,
                                    'company_other'        => $post->company_other,
                                    'address'              => $post->address,
                                    'city'                 => $post->city,
                                    'portal_code'          => $post->portal_code,
                                    'prefix_office_number' => $post->prefix_office_number,
                                    'office_number'        => $post->office_number,
                                    'full_office_number'   => $post->full_office_number,
                                    'country'              => $post->country,
                                ];
                            @endphp
                            <span class="badge badge-danger member-status-badge">
                                <i class="fas fa-times mr-1"></i>Declined
                            </span>
                            <button type="button"
                                class="btn btn-xs btn-danger btn-verify-member"
                                data-url="{{ route('users.verify', $post->user_id) }}"
                                data-company-verified="{{ $declinedCompanyVerified ? '1' : '0' }}"
                                data-company-name="{{ $post->company_name }}"
                                data-normalized-name="{{ strtolower(trim((string) $post->company_name)) }}"
                                data-member-name="{{ $post->name }}"
                                data-member-email="{{ $post->email }}"
                                data-member-job-title="{{ $post->job_title }}"
                                data-member-phone="{{ $post->fullphone ?? $post->phone }}"
                                data-payload='@json($declinedPayload)'
                                title="Aplikasi ini sudah di-decline — klik untuk re-review">
                                <i class="fas fa-redo"></i> Re-review
                            </button>
                        @else
                            <span class="badge badge-warning member-status-badge">
                                <i class="fas fa-clock mr-1"></i>Pending
                            </span>
                            @php
                                $companyVerified = !empty($post->is_verified) || !empty($post->has_verified_company_name);
                                $companyPayload = [
                                    'company_name'         => $post->company_name,
                                    'prefix'               => $post->prefix,
                                    'company_website'      => $post->company_website,
                                    'company_category'     => $post->company_category,
                                    'company_other'        => $post->company_other,
                                    'address'              => $post->address,
                                    'city'                 => $post->city,
                                    'portal_code'          => $post->portal_code,
                                    'prefix_office_number' => $post->prefix_office_number,
                                    'office_number'        => $post->office_number,
                                    'full_office_number'   => $post->full_office_number,
                                    'country'              => $post->country,
                                ];
                            @endphp
                            <button type="button"
                                class="btn btn-xs btn-warning btn-verify-member"
                                data-url="{{ route('users.verify', $post->user_id) }}"
                                data-company-verified="{{ $companyVerified ? '1' : '0' }}"
                                data-company-name="{{ $post->company_name }}"
                                data-normalized-name="{{ strtolower(trim((string) $post->company_name)) }}"
                                data-member-name="{{ $post->name }}"
                                data-member-email="{{ $post->email }}"
                                data-member-job-title="{{ $post->job_title }}"
                                data-member-phone="{{ $post->fullphone ?? $post->phone }}"
                                data-payload='@json($companyPayload)'
                                title="{{ $companyVerified ? 'Verifikasi member' : 'Company belum verified — klik untuk selesaikan dulu' }}">
                                @if (!$companyVerified)
                                    <i class="fas fa-exclamation-triangle"></i>
                                @else
                                    <i class="fas fa-shield-alt"></i>
                                @endif
                                Verify
                            </button>
                        @endif
                    </div>
                </td>

                <td>{{ $post->job_title }}</td>
                <td>{{ $post->company_name }}</td>
                <td><a href="mailto:{{ $post->email }}">{{ $post->email }}</a></td>
                <td class="text-nowrap">{{ $post->fullphone ?? $post->phone }}</td>
                <td class="text-nowrap">{{ $post->office_number ?? $post->full_office_number }}</td>
                <td>{{ $post->address }}</td>
                <td>
                    @if ($post->company_website)
                        <a href="{{ $post->company_website }}" target="_blank" rel="noopener">
                            {{ $post->company_website }}
                        </a>
                    @endif
                </td>
                <td>{{ $post->company_category == 'other' ? $post->company_other : $post->company_category }}</td>

                {{-- CCI & Sponsorship --}}
                <td>
                    <div class="d-flex flex-column align-items-start" style="gap:4px;">
                        @if ($post->cci)
                            <span class="badge badge-info"><i class="fas fa-building mr-1"></i>CCI</span>
                        @else
                            <span class="badge badge-light text-muted" style="font-size:10px;">CCI: No</span>
                        @endif

                        @if ($post->explore)
                            <span class="badge badge-warning"
                                title="Member bersedia menerima penawaran paket sponsorship"
                                data-toggle="tooltip">
                                <i class="fas fa-star mr-1"></i>Open to Sponsorship
                            </span>
                        @else
                            <span class="badge badge-light text-muted" style="font-size:10px;">Sponsorship: No</span>
                        @endif

                        <button type="button"
                            class="btn btn-xs btn-outline-secondary mt-1 btn-import-mailchimp"
                            data-url="{{ route('users.import.mailchimp') }}"
                            data-user-id="{{ $post->user_id }}"
                            data-email="{{ $post->email }}"
                            data-tags='["Register of Membership {{ now()->format('d M Y') }}"]'
                            title="Re-sync data member ini ke Mailchimp">
                            <i class="fas fa-sync-alt"></i> Re-sync MC
                        </button>
                    </div>
                </td>

                {{-- PASSWORD STATUS --}}
                <td class="text-center">
                    @if ($post->password)
                        <span class="badge badge-success" title="Password has been set" data-toggle="tooltip">
                            <i class="fas fa-lock"></i> Set
                        </span>
                    @else
                        <span class="badge badge-danger" title="Password not set yet" data-toggle="tooltip">
                            <i class="fas fa-lock-open"></i> Not Set
                        </span>
                    @endif
                    <div class="mt-1 d-flex flex-column" style="gap:3px;">
                        <button type="button"
                            class="btn btn-xs btn-outline-primary btn-edit-user"
                            data-user-id="{{ $post->user_id }}"
                            data-name="{{ $post->name }}"
                            data-email="{{ $post->email }}"
                            data-job-title="{{ $post->job_title }}"
                            data-phone="{{ $post->fullphone ?? $post->phone }}"
                            data-prefix="{{ $post->prefix }}"
                            data-company-name="{{ $post->company_name }}"
                            data-company-website="{{ $post->company_website }}"
                            data-company-category="{{ $post->company_category }}"
                            data-company-other="{{ $post->company_other }}"
                            data-address="{{ $post->address }}"
                            data-city="{{ $post->city }}"
                            data-portal-code="{{ $post->portal_code }}"
                            data-country="{{ $post->country }}"
                            data-prefix-office-number="{{ $post->prefix_office_number }}"
                            data-office-number="{{ $post->office_number }}"
                            data-full-office-number="{{ $post->full_office_number }}"
                            data-status-member="{{ $post->status_member }}"
                            data-tier="{{ strtolower((string) ($post->tier ?? 'reguler')) }}"
                            data-update-url="{{ route('users.update', $post->user_id) }}"
                            title="Edit data user">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button type="button"
                            class="btn btn-xs btn-outline-secondary btn-view-logs"
                            data-user-id="{{ $post->user_id }}"
                            data-name="{{ $post->name }}"
                            data-logs-url="{{ route('users.logs', $post->user_id) }}"
                            title="Lihat riwayat perubahan">
                            <i class="fas fa-history"></i> Log
                        </button>
                    </div>
                </td>

            </tr>
        @endforeach
    </tbody>
</table>
