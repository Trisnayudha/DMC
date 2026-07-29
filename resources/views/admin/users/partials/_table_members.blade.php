{{-- Table: registered members (User model) --}}
<table id="laravel_crud" class="table table-bordered table-hover">
    <thead class="thead-light">
        <tr>
            <th width="10px">No</th>
            <th>Register</th>
            <th width="90px">
                Source
                <i class="fas fa-info-circle text-muted ml-1"
                    title="Channel pendaftaran member (website, apps, event, dll)."
                    data-toggle="tooltip"></i>
            </th>
            <th>Name</th>
            <th width="80px">
                Status
                <i class="fas fa-info-circle text-muted ml-1"
                    title="Active = sudah diverifikasi admin. Pending = belum diverifikasi."
                    data-toggle="tooltip"></i>
            </th>
            <th width="70px">Actions</th>
            <th>Job Title</th>
            <th>Company</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Office</th>
            <th>Address</th>
            <th>Website</th>
            <th>Category</th>
            <th width="60px">
                WA/Spon.
                <i class="fas fa-info-circle text-muted ml-1"
                    title="WA Updates: member setuju menerima update via WhatsApp. Open to Sponsorship: member bersedia menerima penawaran paket sponsorship."
                    data-toggle="tooltip"></i>
            </th>
            <th width="70px">Password</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1; ?>
        @php
            // Warna tag per channel pendaftaran (Source). Key harus lowercase.
            $sourceColorMap = [
                'website'   => ['color' => '#4e73df', 'icon' => 'fas fa-globe'],
                'apps'      => ['color' => '#1cc88a', 'icon' => 'fas fa-mobile-alt'],
                'scanner'   => ['color' => '#858796', 'icon' => 'fas fa-qrcode'],
                'linkedin'  => ['color' => '#0077b5', 'icon' => 'fab fa-linkedin-in'],
                'instagram' => ['color' => '#e1306c', 'icon' => 'fab fa-instagram'],
                'event'     => ['color' => '#f6a92f', 'icon' => 'fas fa-calendar-alt'],
                'other'     => ['color' => '#6f42c1', 'icon' => 'fas fa-ellipsis-h'],
            ];
        @endphp
        @foreach ($list as $post)
            @php
                $memberStatus  = strtolower($post->status_member ?? '');
                $isActive      = $memberStatus === 'active';
                $isDeclined    = $memberStatus === 'declined';
                $isDeactivated = $memberStatus === 'deactivated';
                $rowBg = $isActive ? '' : ($isDeclined ? 'background-color:#fff5f5;' : ($isDeactivated ? 'background-color:#f0f0f0;' : 'background-color:#fffbee;'));

                $sourceRaw = trim((string) ($post->source ?? ''));
                $sourceKey = strtolower($sourceRaw);
                if ($sourceKey !== '' && !isset($sourceColorMap[$sourceKey]) && strpos($sourceKey, 'event') === 0) {
                    $sourceKey = 'event';
                }
                $sourceStyle = $sourceColorMap[$sourceKey] ?? ['color' => '#adb5bd', 'icon' => 'fas fa-question'];
                $sourceLabel = $sourceRaw !== '' ? $sourceRaw : 'Unknown';
            @endphp
            <tr id="row_{{ $post->user_id }}" style="{{ $rowBg }}">

                <td>{{ $no++ }}</td>

                <td class="text-nowrap">
                    {{ date('d M Y', strtotime($post->user_created_at ?? $post->created_at)) }}<br>
                    <small class="text-muted">{{ date('H:i', strtotime($post->user_created_at ?? $post->created_at)) }}</small>
                </td>

                <td class="text-nowrap">
                    <span class="badge mini-badge" style="background-color:{{ $sourceStyle['color'] }};color:#fff;"
                        title="{{ $sourceLabel }}" data-toggle="tooltip">
                        <i class="{{ $sourceStyle['icon'] }}"></i>
                    </span>
                </td>

                <td>
                    {{ $post->name }}
                    @if (isset($selfEditMap[$post->user_id]))
                        <i class="fas fa-user-edit text-warning ml-1"
                            title="User mengubah data sendiri — {{ \Carbon\Carbon::parse($selfEditMap[$post->user_id])->format('d M Y H:i') }}"
                            data-toggle="tooltip"></i>
                    @endif
                </td>

                {{-- STATUS --}}
                <td>
                    @if ($isDeactivated)
                        <span class="badge badge-secondary member-status-badge mini-badge">Deactivated</span>
                    @elseif ($isActive)
                        <span class="badge badge-success member-status-badge mini-badge">Active</span>
                        @if (!empty($post->two_step_verified))
                            <br>
                            <span class="badge badge-info mini-badge mt-1 d-inline-block"
                                title="Verifikasi 2-Langkah oleh {{ $post->two_step_verified_by ?? 'Staff' }} ({{ $post->two_step_verified_at ? \Carbon\Carbon::parse($post->two_step_verified_at)->format('d M Y H:i') : '' }})"
                                data-toggle="tooltip">
                                <i class="fas fa-check-double mr-1"></i>2-Step
                            </span>
                        @endif
                    @elseif ($isDeclined)
                        <span class="badge badge-danger member-status-badge mini-badge">Disqualified</span>
                    @else
                        <span class="badge badge-warning member-status-badge mini-badge">Pending</span>
                    @endif
                </td>

                {{-- STATUS ACTIONS --}}
                <td>
                    <div class="btn-icon-group">
                        @if ($isDeactivated)
                            <button type="button"
                                class="btn btn-icon btn-outline-success btn-reactivate-member"
                                data-url="{{ route('users.reactivate', $post->user_id) }}"
                                data-name="{{ $post->name }}"
                                title="Reactivate member ini" data-toggle="tooltip">
                                <i class="fas fa-undo"></i>
                            </button>
                        @elseif ($isActive)
                            <button type="button"
                                class="btn btn-icon btn-success btn-verify-member"
                                data-url="{{ route('users.verify', $post->user_id) }}"
                                disabled title="Sudah verified" data-toggle="tooltip">
                                <i class="fas fa-check"></i>
                            </button>
                            <button type="button"
                                class="btn btn-icon {{ !empty($post->two_step_verified) ? 'btn-info' : 'btn-outline-info' }} btn-toggle-two-step"
                                data-url="{{ route('users.toggle_two_step', $post->user_id) }}"
                                data-name="{{ $post->name }}"
                                data-verified="{{ !empty($post->two_step_verified) ? '1' : '0' }}"
                                title="{{ !empty($post->two_step_verified) ? 'Sudah Verifikasi 2-Langkah oleh ' . ($post->two_step_verified_by ?? 'Staff') . ' — Klik untuk batalkan' : 'Tandai Verifikasi 2-Langkah oleh Staff (LinkedIn/Telfon)' }}"
                                data-toggle="tooltip">
                                <i class="fas {{ !empty($post->two_step_verified) ? 'fa-check-circle' : 'fa-user-check' }}"></i>
                            </button>
                            <button type="button"
                                class="btn btn-icon btn-outline-secondary btn-deactivate-member"
                                data-url="{{ route('users.deactivate', $post->user_id) }}"
                                data-name="{{ $post->name }}"
                                title="Deactivate member ini" data-toggle="tooltip">
                                <i class="fas fa-user-slash"></i>
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
                            <button type="button"
                                class="btn btn-icon btn-danger btn-verify-member"
                                data-url="{{ route('users.verify', $post->user_id) }}"
                                data-company-verified="{{ $declinedCompanyVerified ? '1' : '0' }}"
                                data-company-name="{{ $post->company_name }}"
                                data-normalized-name="{{ strtolower(trim((string) $post->company_name)) }}"
                                data-member-name="{{ $post->name }}"
                                data-member-email="{{ $post->email }}"
                                data-member-job-title="{{ $post->job_title }}"
                                data-member-phone="{{ $post->fullphone ?? $post->phone }}"
                                data-payload='@json($declinedPayload)'
                                title="Aplikasi ini sudah di-decline — klik untuk re-review" data-toggle="tooltip">
                                <i class="fas fa-redo"></i>
                            </button>
                        @else
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
                                class="btn btn-icon btn-warning btn-verify-member"
                                data-url="{{ route('users.verify', $post->user_id) }}"
                                data-company-verified="{{ $companyVerified ? '1' : '0' }}"
                                data-company-name="{{ $post->company_name }}"
                                data-normalized-name="{{ strtolower(trim((string) $post->company_name)) }}"
                                data-member-name="{{ $post->name }}"
                                data-member-email="{{ $post->email }}"
                                data-member-job-title="{{ $post->job_title }}"
                                data-member-phone="{{ $post->fullphone ?? $post->phone }}"
                                data-payload='@json($companyPayload)'
                                title="{{ $companyVerified ? 'Verifikasi member' : 'Company belum verified — klik untuk selesaikan dulu' }}"
                                data-toggle="tooltip">
                                @if (!$companyVerified)
                                    <i class="fas fa-exclamation-triangle"></i>
                                @else
                                    <i class="fas fa-shield-alt"></i>
                                @endif
                            </button>
                        @endif
                    </div>
                </td>

                <td><span class="cell-truncate" title="{{ $post->job_title }}">{{ $post->job_title }}</span></td>
                <td><span class="cell-truncate" title="{{ $post->company_name }}">{{ $post->company_name }}</span></td>
                <td><a href="mailto:{{ $post->email }}" class="cell-truncate" title="{{ $post->email }}">{{ $post->email }}</a></td>
                <td class="text-nowrap">{{ $post->fullphone ?? $post->phone }}</td>
                <td class="text-nowrap">{{ $post->full_office_number }}</td>
                <td><span class="cell-truncate" title="{{ $post->address }}">{{ $post->address }}</span></td>
                <td>
                    @if ($post->company_website)
                        <a href="{{ $post->company_website }}" target="_blank" rel="noopener"
                            class="cell-truncate" title="{{ $post->company_website }}">
                            {{ $post->company_website }}
                        </a>
                    @endif
                </td>
                <td>
                    @php $categoryLabel = $post->company_category == 'other' ? $post->company_other : $post->company_category; @endphp
                    <span class="cell-truncate" title="{{ $categoryLabel }}">{{ $categoryLabel }}</span>
                </td>

                {{-- WA Updates & Sponsorship --}}
                <td class="text-center">
                    <div class="btn-icon-group">
                        <i class="fab fa-whatsapp {{ strtolower(trim((string) $post->wa_updates)) === 'agree' ? 'text-success' : 'text-muted' }}"
                            title="WA Updates: {{ strtolower(trim((string) $post->wa_updates)) === 'agree' ? 'Yes' : 'No' }}"
                            data-toggle="tooltip"></i>
                        <i class="fas fa-star {{ $post->explore ? 'text-warning' : 'text-muted' }}"
                            title="Open to Sponsorship: {{ $post->explore ? 'Yes' : 'No' }}"
                            data-toggle="tooltip"></i>
                        <button type="button"
                            class="btn btn-icon btn-outline-secondary btn-import-mailchimp"
                            data-url="{{ route('users.import.mailchimp') }}"
                            data-user-id="{{ $post->user_id }}"
                            data-email="{{ $post->email }}"
                            data-tags='["Register of Membership {{ now()->format('d M Y') }}"]'
                            title="Re-sync data member ini ke Mailchimp" data-toggle="tooltip">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </td>

                {{-- PASSWORD + ACTIONS --}}
                <td>
                    <div class="btn-icon-group">
                        <i class="fas {{ $post->password ? 'fa-lock text-success' : 'fa-lock-open text-danger' }}"
                            title="Password {{ $post->password ? 'has been set' : 'not set yet' }}"
                            data-toggle="tooltip"></i>
                        <button type="button"
                            class="btn btn-icon btn-outline-primary btn-edit-user"
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
                            title="Edit data user" data-toggle="tooltip">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button"
                            class="btn btn-icon btn-outline-secondary btn-view-logs"
                            data-user-id="{{ $post->user_id }}"
                            data-name="{{ $post->name }}"
                            data-logs-url="{{ route('users.logs', $post->user_id) }}"
                            title="Lihat riwayat perubahan" data-toggle="tooltip">
                            <i class="fas fa-history"></i>
                        </button>
                        @php $openFollowUp = $followUpMap[$post->user_id] ?? null; @endphp
                        <button type="button"
                            class="btn btn-icon {{ $openFollowUp ? 'btn-warning' : 'btn-outline-warning' }} btn-open-follow-up-modal"
                            data-user-id="{{ $post->user_id }}"
                            data-member-name="{{ $post->name }}"
                            data-current-company="{{ $post->company_name }}"
                            data-new-company="{{ $openFollowUp->new_company_name ?? '' }}"
                            data-notes="{{ $openFollowUp->notes ?? '' }}"
                            title="{{ $openFollowUp ? 'Follow-up pending — klik untuk edit' : 'Tandai member ini pindah company' }}"
                            data-toggle="tooltip">
                            <i class="fas fa-exchange-alt"></i>
                        </button>
                    </div>
                </td>

            </tr>
        @endforeach
    </tbody>
</table>
