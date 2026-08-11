{{-- Main table card: status tabs + PIC filter + table --}}

<div class="card">
    <div class="card-header">
        <h4 class="mb-0">
            <i class="fas fa-bullseye mr-1"></i>Lead Follow-Up
            <i class="fas fa-info-circle text-muted ml-1" style="font-size:12px;"
                title="Otomatis dibuat saat member dengan Explore Marketing (company.explore) di-verify. SLA follow up: 48 jam sejak verifikasi."
                data-toggle="tooltip"></i>
        </h4>
    </div>

    <div class="card-body">

        {{-- Status tabs + PIC filter --}}
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-3" style="gap:10px;">
            <div class="d-flex flex-wrap" style="gap:6px;">
                <a href="{{ url('admin/leads?result=pending') }}"
                    class="btn btn-sm {{ $result === 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">
                    <i class="fas fa-bullseye mr-1"></i> Pending
                    <span class="badge badge-light ml-1">{{ $countPending }}</span>
                </a>
                <a href="{{ url('admin/leads?result=win') }}"
                    class="btn btn-sm {{ $result === 'win' ? 'btn-success' : 'btn-outline-success' }}">
                    <i class="fas fa-check-circle mr-1"></i> Win
                    <span class="badge badge-light ml-1">{{ $countWin }}</span>
                </a>
                <a href="{{ url('admin/leads?result=loss') }}"
                    class="btn btn-sm {{ $result === 'loss' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                    <i class="fas fa-times-circle mr-1"></i> Loss
                    <span class="badge badge-light ml-1">{{ $countLoss }}</span>
                </a>
                <a href="{{ url('admin/leads?result=all') }}"
                    class="btn btn-sm {{ $result === 'all' ? 'btn-dark' : 'btn-outline-dark' }}">
                    <i class="fas fa-list mr-1"></i> All
                </a>
            </div>

            <form action="{{ url('admin/leads') }}" method="GET" class="d-flex align-items-end" style="gap:6px;">
                <input type="hidden" name="result" value="{{ $result }}">
                <div class="form-group mb-0">
                    <label class="mb-1 small text-muted">PIC</label>
                    <select name="pic_id" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">All PIC</option>
                        @foreach ($pics as $pic)
                            <option value="{{ $pic->id }}" {{ (string) $picId === (string) $pic->id ? 'selected' : '' }}>
                                {{ $pic->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th width="10px">No</th>
                        <th class="text-nowrap">Created</th>
                        <th>Member</th>
                        <th>Company</th>
                        <th class="text-nowrap">Phone</th>
                        <th class="text-nowrap">Deadline</th>
                        <th>PIC</th>
                        <th>Channel</th>
                        <th>Notes</th>
                        <th class="text-nowrap">1st / 2nd Follow-Up</th>
                        <th width="90px">Result</th>
                        <th width="170px">Actions</th>
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

                            $isPending = $item->result === \App\Models\MemberLeadFollowUp::RESULT_PENDING;
                            $isOverSla = $isPending && $item->deadline_at && $item->deadline_at->isPast();
                        @endphp
                        <tr @if ($isOverSla) style="background-color:#fff5f5;" @endif>
                            <td>{{ $no++ }}</td>
                            <td class="text-nowrap">{{ $item->created_at ? $item->created_at->format('d M Y H:i') : '-' }}</td>
                            <td>{{ optional($member)->name ?? '(deleted user)' }}<br>
                                <small class="text-muted">{{ optional($member)->email }}</small>
                            </td>
                            <td>{{ optional(optional($member)->company)->company_name }}</td>
                            <td class="text-nowrap">
                                {{ $phone }}
                                @if ($waDigits !== '')
                                    <a href="https://wa.me/{{ $waDigits }}" target="_blank" rel="noopener"
                                        class="btn btn-xs btn-outline-success ml-1" title="Chat via WhatsApp">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                {{ optional($item->deadline_at)->format('d M Y H:i') }}
                                @if ($isOverSla)
                                    <br><span class="badge badge-danger mini-badge">Over SLA</span>
                                @endif
                            </td>
                            <td class="text-nowrap">{{ $item->pic_name ?: '-' }}</td>
                            <td>{{ $item->channel ? ucfirst($item->channel) : '-' }}</td>
                            <td><span class="cell-truncate" style="max-width:160px;" title="{{ $item->notes }}">{{ $item->notes }}</span></td>
                            <td class="text-nowrap">
                                {{ optional($item->first_follow_up_at)->format('d M Y') ?: '-' }} /
                                {{ optional($item->second_follow_up_at)->format('d M Y') ?: '-' }}
                            </td>
                            <td>
                                @if ($item->result === \App\Models\MemberLeadFollowUp::RESULT_WIN)
                                    <span class="badge badge-success">Win</span>
                                @elseif ($item->result === \App\Models\MemberLeadFollowUp::RESULT_LOSS)
                                    <span class="badge badge-secondary">Loss</span>
                                @else
                                    <span class="badge badge-warning">Pending</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column" style="gap:3px;">
                                    <button type="button"
                                        class="btn btn-xs btn-outline-primary btn-open-assign-pic-modal"
                                        data-lead-id="{{ $item->id }}"
                                        data-member-name="{{ optional($member)->name }}"
                                        data-pic-id="{{ $item->pic_id }}"
                                        data-assign-url="{{ route('admin.member_leads.assign_pic', $item->id) }}"
                                        title="Assign PIC">
                                        <i class="fas fa-user-tag"></i> PIC
                                    </button>
                                    @if ($isPending)
                                        <button type="button"
                                            class="btn btn-xs btn-outline-secondary btn-open-follow-up-log-modal"
                                            data-lead-id="{{ $item->id }}"
                                            data-member-name="{{ optional($member)->name }}"
                                            data-channel="{{ $item->channel }}"
                                            data-notes="{{ $item->notes }}"
                                            data-log-url="{{ route('admin.member_leads.log_follow_up', $item->id) }}"
                                            title="Catat follow up">
                                            <i class="fas fa-comment-dots"></i> Log
                                        </button>
                                        <div class="btn-group" role="group">
                                            <button type="button"
                                                class="btn btn-xs btn-success btn-mark-lead-result"
                                                data-url="{{ route('admin.member_leads.mark_result', $item->id) }}"
                                                data-result="win"
                                                title="Tandai Win">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button"
                                                class="btn btn-xs btn-outline-secondary btn-mark-lead-result"
                                                data-url="{{ route('admin.member_leads.mark_result', $item->id) }}"
                                                data-result="loss"
                                                title="Tandai Loss">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
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
