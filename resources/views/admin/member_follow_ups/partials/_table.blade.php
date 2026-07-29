{{-- Main table card: status tabs + table --}}

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
                <a href="{{ url('admin/company-follow-ups?status=needs_follow_up') }}"
                    class="btn btn-sm {{ $status === 'needs_follow_up' ? 'btn-warning' : 'btn-outline-warning' }}">
                    <i class="fas fa-exchange-alt mr-1"></i> Need Follow Up
                    <span class="badge badge-light ml-1">{{ $countNeedsFollowUp }}</span>
                </a>
                <a href="{{ url('admin/company-follow-ups?status=verified') }}"
                    class="btn btn-sm {{ $status === 'verified' ? 'btn-success' : 'btn-outline-success' }}">
                    <i class="fas fa-check-circle mr-1"></i> Verified
                    <span class="badge badge-light ml-1">{{ $countVerified }}</span>
                </a>
                <a href="{{ url('admin/company-follow-ups?status=all') }}"
                    class="btn btn-sm {{ $status === 'all' ? 'btn-dark' : 'btn-outline-secondary' }}">
                    <i class="fas fa-list mr-1"></i> All
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th width="10px">No</th>
                        <th class="text-nowrap">Flagged Date</th>
                        <th>Member</th>
                        <th>Email</th>
                        <th class="text-nowrap">Phone</th>
                        <th>Previous Company</th>
                        <th>New Company (claimed)</th>
                        <th>Notes</th>
                        <th width="140px">Status</th>
                        <th>Flagged By</th>
                        <th>Verified By</th>
                        <th width="160px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    @forelse ($list as $item)
                        @php
                            $member = $item->user;
                            $phone = optional(optional($member)->profile)->fullphone
                                ?? optional(optional($member)->profile)->phone;

                            $waDigits = preg_replace('/\D/', '', (string) $phone);
                            if ($waDigits !== '' && substr($waDigits, 0, 1) === '0') {
                                $waDigits = '62' . substr($waDigits, 1);
                            } elseif ($waDigits !== '' && substr($waDigits, 0, 2) !== '62') {
                                $waDigits = '62' . $waDigits;
                            }

                            $isVerified = $item->status === \App\Models\MemberCompanyFollowUp::STATUS_VERIFIED;
                        @endphp
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td class="text-nowrap">{{ $item->created_at ? $item->created_at->format('d M Y H:i') : '-' }}</td>
                            <td>{{ optional($member)->name ?? '(deleted user)' }}</td>
                            <td>{{ optional($member)->email }}</td>
                            <td class="text-nowrap">
                                {{ $phone }}
                                @if ($waDigits !== '')
                                    <a href="https://wa.me/{{ $waDigits }}" target="_blank" rel="noopener"
                                        class="btn btn-xs btn-outline-success ml-1" title="Chat via WhatsApp">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                @endif
                            </td>
                            <td>{{ $item->previous_company_name }}</td>
                            <td>{{ $item->new_company_name }}</td>
                            <td>{{ $item->notes }}</td>
                            <td>
                                @if ($isVerified)
                                    <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Update Verified</span>
                                @else
                                    <span class="badge badge-warning"><i class="fas fa-clock mr-1"></i>Needs Follow Up</span>
                                @endif
                            </td>
                            <td class="text-nowrap">{{ $item->flagged_by_name }}</td>
                            <td class="text-nowrap">
                                @if ($isVerified)
                                    {{ $item->verified_by_name }}<br>
                                    <small class="text-muted">{{ optional($item->verified_at)->format('d M Y H:i') }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column" style="gap:3px;">
                                    @if (!$isVerified && $member)
                                        <button type="button"
                                            class="btn btn-xs btn-outline-primary btn-open-follow-up-modal"
                                            data-user-id="{{ $member->id }}"
                                            data-member-name="{{ $member->name }}"
                                            data-current-company="{{ $item->previous_company_name }}"
                                            data-new-company="{{ $item->new_company_name }}"
                                            data-notes="{{ $item->notes }}"
                                            title="Edit follow-up ini">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button type="button"
                                            class="btn btn-xs btn-success btn-open-verify-modal"
                                            data-follow-up-id="{{ $item->id }}"
                                            data-verify-url="{{ route('admin.member_follow_ups.verify', $item->id) }}"
                                            data-member-name="{{ optional($member)->name }}"
                                            data-new-company="{{ $item->new_company_name }}"
                                            data-member-email="{{ optional($member)->email }}"
                                            data-member-job-title="{{ optional(optional($member)->profile)->job_title }}"
                                            title="Verify — buat akun baru dengan company terbaru">
                                            <i class="fas fa-check"></i> Verify
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center text-muted py-4">Tidak ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>{{-- /card --}}
