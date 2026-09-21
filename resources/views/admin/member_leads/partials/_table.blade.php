{{-- Main table card: status tabs + search/filter + table --}}

<div class="card">
    <div class="card-header">
        <h4 class="mb-0">
            <i class="fas fa-bullseye mr-1"></i>Lead Follow-Up
            <i class="fas fa-info-circle text-muted ml-1" style="font-size:12px;"
                title="Automatically created when a member with Explore Marketing (company.explore) is verified. Follow-up SLA: 48 hours since the last action (verification / sponsor kit sent / follow-up)."
                data-toggle="tooltip"></i>
        </h4>
    </div>

    <div class="card-body">

        {{-- Status tabs --}}
        <div class="mb-3">
            <div class="d-flex flex-wrap" style="gap:6px;">
                <a href="{{ url('admin/leads?' . http_build_query(array_merge(request()->except('result'), ['result' => 'pending']))) }}"
                    class="btn btn-sm {{ $result === 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">
                    <i class="fas fa-bullseye mr-1"></i> Pending
                    <span class="badge badge-light ml-1">{{ $countPending }}</span>
                </a>
                <a href="{{ url('admin/leads?' . http_build_query(array_merge(request()->except('result'), ['result' => 'win']))) }}"
                    class="btn btn-sm {{ $result === 'win' ? 'btn-success' : 'btn-outline-success' }}">
                    <i class="fas fa-check-circle mr-1"></i> Win
                    <span class="badge badge-light ml-1">{{ $countWin }}</span>
                </a>
                <a href="{{ url('admin/leads?' . http_build_query(array_merge(request()->except('result'), ['result' => 'loss']))) }}"
                    class="btn btn-sm {{ $result === 'loss' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                    <i class="fas fa-times-circle mr-1"></i> Loss
                    <span class="badge badge-light ml-1">{{ $countLoss }}</span>
                </a>
                <a href="{{ url('admin/leads?' . http_build_query(array_merge(request()->except('result'), ['result' => 'do_not_send']))) }}"
                    class="btn btn-sm {{ $result === 'do_not_send' ? 'btn-danger' : 'btn-outline-danger' }}">
                    <i class="fas fa-ban mr-1"></i> Do Not Send
                    <span class="badge badge-light ml-1">{{ $countDoNotSend }}</span>
                </a>
                <a href="{{ url('admin/leads?' . http_build_query(array_merge(request()->except('result'), ['result' => 'all']))) }}"
                    class="btn btn-sm {{ $result === 'all' ? 'btn-dark' : 'btn-outline-dark' }}">
                    <i class="fas fa-list mr-1"></i> All
                </a>
            </div>
        </div>

        {{-- Search + PIC filter --}}
        <form action="{{ url('admin/leads') }}" method="GET"
            class="d-flex flex-wrap align-items-end mb-3 border-top pt-3" style="gap:10px;">
            <input type="hidden" name="result" value="{{ $result }}">
            <div class="form-group mb-0" style="min-width:220px; flex:1 1 220px;">
                <label class="mb-1 small text-muted">Search</label>
                <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm"
                    placeholder="Name, email, or company...">
            </div>
            <div class="form-group mb-0">
                <label class="mb-1 small text-muted">PIC</label>
                <select name="pic_id" class="form-control form-control-sm">
                    <option value="">All PIC</option>
                    @foreach ($pics as $pic)
                        <option value="{{ $pic->id }}" {{ (string) $picId === (string) $pic->id ? 'selected' : '' }}>
                            {{ $pic->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-0">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i> Filter</button>
                <a href="{{ url('admin/leads?result=' . $result) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times mr-1"></i> Clear
                </a>
            </div>
        </form>

        <div class="table-responsive lead-table-wrap">
            <table class="table table-hover">
                <thead>
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
                        <th class="text-nowrap">
                            Progress
                            <i class="fas fa-info-circle text-muted ml-1" style="font-size:12px;"
                                title="Sponsor Kit Sent → Follow-up 1 → Follow-up 2." data-toggle="tooltip"></i>
                        </th>
                        <th width="90px">Result</th>
                        <th width="150px" class="text-nowrap">Actions</th>
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
                            $nextStepKey = $item->nextStepKey();
                        @endphp
                        <tr @if ($item->do_not_send) style="background-color:#fbeaea;" @elseif ($isOverSla) style="background-color:#fff5f5;" @endif>
                            <td>{{ $no++ }}</td>
                            <td class="text-nowrap"><small>{{ $item->created_at ? $item->created_at->format('d M Y H:i') : '-' }}</small></td>
                            <td>
                                <span class="cell-truncate font-weight-bold" title="{{ optional($member)->name }}">{{ optional($member)->name ?? '(deleted user)' }}</span>
                                <br>
                                <span class="cell-truncate text-muted" style="font-size:10.5px;" title="{{ optional($member)->email }}">{{ optional($member)->email }}</span>
                            </td>
                            <td><span class="cell-truncate" title="{{ optional(optional($member)->company)->company_name }}">{{ optional(optional($member)->company)->company_name }}</span></td>
                            <td class="text-nowrap">
                                {{ $phone }}
                                @if ($waDigits !== '')
                                    <a href="https://wa.me/{{ $waDigits }}" target="_blank" rel="noopener"
                                        class="btn btn-icon btn-outline-success" title="Chat via WhatsApp" data-toggle="tooltip">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                <small>{{ optional($item->deadline_at)->format('d M Y H:i') ?: '-' }}</small>
                                @if ($isOverSla)
                                    <br><span class="badge badge-danger mini-badge">Over SLA</span>
                                @endif
                            </td>
                            <td class="text-nowrap"><small>{{ $item->pic_name ?: '-' }}</small></td>
                            <td><small>{{ $item->channel ? ucfirst($item->channel) : '-' }}</small></td>
                            <td><span class="cell-truncate" title="{{ $item->notes }}">{{ $item->notes ?: '—' }}</span></td>
                            <td class="text-nowrap" style="font-size:11px;">
                                @if ($item->do_not_send)
                                    <div class="text-danger font-weight-bold" title="Flagged by {{ $item->do_not_send_by_name }} on {{ optional($item->do_not_send_at)->format('d M Y H:i') }}{{ $item->do_not_send_reason ? ' — ' . $item->do_not_send_reason : '' }}" data-toggle="tooltip">
                                        <i class="fas fa-ban mr-1"></i>
                                        Do Not Send Kit{{ $item->do_not_send_reason ? ' (' . $item->do_not_send_reason . ')' : '' }}
                                    </div>
                                @endif
                                <div class="{{ $item->sponsorkit_sent_at ? 'text-success' : 'text-muted' }}">
                                    <i class="fas {{ $item->sponsorkit_sent_at ? 'fa-check-circle' : 'fa-circle' }} mr-1"></i>
                                    Sponsor Kit: {{ optional($item->sponsorkit_sent_at)->format('d M Y H:i') ?: '-' }}
                                </div>
                                <div class="{{ $item->first_follow_up_at ? 'text-success' : 'text-muted' }}">
                                    <i class="fas {{ $item->first_follow_up_at ? 'fa-check-circle' : 'fa-circle' }} mr-1"></i>
                                    Follow-up 1: {{ optional($item->first_follow_up_at)->format('d M Y H:i') ?: '-' }}
                                </div>
                                <div class="{{ $item->second_follow_up_at ? 'text-success' : 'text-muted' }}">
                                    <i class="fas {{ $item->second_follow_up_at ? 'fa-check-circle' : 'fa-circle' }} mr-1"></i>
                                    Follow-up 2: {{ optional($item->second_follow_up_at)->format('d M Y H:i') ?: '-' }}
                                </div>
                            </td>
                            <td>
                                @if ($item->result === \App\Models\MemberLeadFollowUp::RESULT_WIN)
                                    <span class="badge badge-success mini-badge">Win</span>
                                @elseif ($item->result === \App\Models\MemberLeadFollowUp::RESULT_LOSS)
                                    <span class="badge badge-secondary mini-badge">Loss</span>
                                @else
                                    <span class="badge badge-warning mini-badge">Pending</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                @php
                                    $hasAnyLeadAction = ($isPending && $nextStepKey) || $isPending || $item->do_not_send || !$item->sponsorkit_sent_at;
                                @endphp
                                @if ($hasAnyLeadAction)
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border py-1 px-2" type="button"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v text-muted"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                            @if ($isPending && $nextStepKey)
                                                <button type="button"
                                                    class="dropdown-item btn-open-follow-up-log-modal"
                                                    data-lead-id="{{ $item->id }}"
                                                    data-member-name="{{ optional($member)->name }}"
                                                    data-step-key="{{ $nextStepKey }}"
                                                    data-step-label="{{ \App\Models\MemberLeadFollowUp::stepLabel($nextStepKey) }}"
                                                    data-channel="{{ $item->channel }}"
                                                    data-notes="{{ $item->notes }}"
                                                    data-log-url="{{ route('admin.member_leads.log_follow_up', $item->id) }}">
                                                    <i class="fas {{ $nextStepKey === 'sponsorkit' ? 'fa-gift' : 'fa-comment-dots' }} mr-2 text-secondary"></i>
                                                    Log {{ \App\Models\MemberLeadFollowUp::stepLabel($nextStepKey) }}
                                                </button>
                                            @endif
                                            @if ($isPending)
                                                <button type="button"
                                                    class="dropdown-item btn-mark-lead-result"
                                                    data-url="{{ route('admin.member_leads.mark_result', $item->id) }}"
                                                    data-result="win">
                                                    <i class="fas fa-check mr-2 text-success"></i>Mark as Win
                                                </button>
                                                <button type="button"
                                                    class="dropdown-item btn-mark-lead-result"
                                                    data-url="{{ route('admin.member_leads.mark_result', $item->id) }}"
                                                    data-result="loss">
                                                    <i class="fas fa-times mr-2 text-secondary"></i>Mark as Loss
                                                </button>
                                            @endif
                                            @if ($item->do_not_send)
                                                @if ($isPending || $nextStepKey)
                                                    <div class="dropdown-divider"></div>
                                                @endif
                                                <button type="button"
                                                    class="dropdown-item btn-toggle-do-not-send"
                                                    data-url="{{ route('admin.member_leads.do_not_send', $item->id) }}"
                                                    data-do-not-send="0">
                                                    <i class="fas fa-undo mr-2 text-danger"></i>Remove Do Not Send Flag
                                                </button>
                                            @elseif (!$item->sponsorkit_sent_at)
                                                @if ($isPending)
                                                    <div class="dropdown-divider"></div>
                                                @endif
                                                <button type="button"
                                                    class="dropdown-item text-danger btn-open-do-not-send-modal"
                                                    data-lead-id="{{ $item->id }}"
                                                    data-member-name="{{ optional($member)->name }}"
                                                    data-url="{{ route('admin.member_leads.do_not_send', $item->id) }}">
                                                    <i class="fas fa-ban mr-2"></i>Flag as Do Not Send
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center text-muted py-4">No data available.</td>
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
    .lead-table-wrap table {
        border-collapse: separate !important;
        border: none;
    }
    .lead-table-wrap table thead th {
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
    .lead-table-wrap table tbody td {
        border-left: none !important;
        border-right: none !important;
        border-top: none !important;
        border-bottom: 1px solid #eef0f4 !important;
        vertical-align: middle;
        font-size: 11.5px;
        padding: .4rem .5rem;
    }
    .lead-table-wrap table tbody tr:hover {
        background-color: #f7f9fc;
    }

    /* Icon-only action buttons — same as Members DMC's Actions column */
    .lead-table-wrap .btn-icon {
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

    /* Small pill badges (result, over-SLA) */
    .lead-table-wrap .mini-badge {
        font-size: 9.5px;
        padding: .2em .45em;
        font-weight: 600;
    }

    /* Long free-text fields (member, company, notes) — truncate with
       ellipsis, full value available via title="" on hover. */
    .lead-table-wrap .cell-truncate {
        display: inline-block;
        max-width: 160px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
    }
</style>
