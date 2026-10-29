<div class="lead-table-toolbar">
    <div id="bulk-bar" class="bulk-bar">
        <span id="bulk-hint">{{ number_format($list->total()) }} leads <span class="lead-toolbar-divider">·</span> Select leads on this page for bulk actions</span>
        <span class="d-none" id="bulk-selected"><strong id="bulk-count">0</strong> selected</span>
        <button type="button" class="bulk-btn-link d-none" id="bulk-clear">Clear selection</button>
    </div>
    <div class="lead-utilities">
        <span class="lead-sla-count" title="Pending leads past their deadline">{{ $countOverSla }} over SLA</span>
        <a href="{{ route('admin.member_leads.export', request()->except(['page', 'per_page'])) }}" class="btn btn-sm btn-outline-success"><i class="fas fa-file-excel mr-1"></i> Export Excel</a>
        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#addLeadModal"><i class="fas fa-plus mr-1"></i> Add Member to Lead</button>
    </div>
</div>
<div class="table-responsive lead-table-wrap" role="region" aria-label="Lead follow-up table" tabindex="0">
    <table class="table table-sm table-hover lead-table">
        <colgroup>
            @foreach ([32, 30, 78, 152, 128, 128, 76, 88, 76, 105, 100, 100, 100, 76, 140, 48] as $width)
                <col style="width: {{ $width }}px;">
            @endforeach
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2" scope="col"><input type="checkbox" id="bulk-select-all" aria-label="Select all leads on this page"></th>
                <th rowspan="2" scope="col">No.</th>
                <th rowspan="2" scope="col">Created</th>
                <th rowspan="2" scope="col">Name &amp; Position</th>
                <th rowspan="2" scope="col">Company</th>
                <th rowspan="2" scope="col">Email</th>
                <th rowspan="2" scope="col">Phone</th>
                <th rowspan="2" scope="col">Channel</th>
                <th rowspan="2" scope="col">Temperature <i class="fas fa-info-circle text-muted" title="HOT 70–100, WARM 40–69, COLD 0–39. Click a score to update." data-toggle="tooltip"></i></th>
                <th rowspan="2" scope="col">Result</th>
                <th colspan="4" scope="colgroup" class="text-center">Follow-up Process</th>
                <th rowspan="2" scope="col">PIC</th>
                <th rowspan="2" scope="col">Notes</th>
                <th rowspan="2" scope="col">Actions</th>
            </tr>
            <tr>
                <th scope="col"><i class="far fa-paper-plane"></i> Sponsorship Kit</th>
                <th scope="col"><i class="far fa-envelope"></i> Follow-up 1</th>
                <th scope="col"><i class="far fa-envelope"></i> Follow-up 2</th>
                <th scope="col"><i class="far fa-calendar-alt"></i> Next Action</th>
            </tr>
        </thead>
                <tbody>
                    @php $no = $list->firstItem() ?? 1; @endphp
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
                            $isOverSla = $item->isOverSla();
                            $nextStepKey = $item->nextStepKey();
                            $latestNote = $item->notesHistory->first();
                            $lastDoneStep = $item->second_follow_up_at ? 'follow_up_2' : ($item->first_follow_up_at ? 'follow_up_1' : ($item->sponsorkit_sent_at ? 'sponsorkit' : ''));
                        @endphp
                        <tr class="{{ $item->do_not_send ? 'lead-excluded' : '' }}">
                            <td><input type="checkbox" class="bulk-select" aria-label="Select {{ optional($member)->name ?? 'lead' }}" value="{{ $item->id }}"></td>
                            <td>{{ $no++ }}</td>
                            <td class="text-nowrap">{{ optional($item->created_at)->format('d M Y') ?: '—' }}<small class="d-block">{{ optional($item->created_at)->format('H:i') }}</small></td>
                            <td>
                                <span class="cell-truncate font-weight-bold" title="{{ optional($member)->name }} — {{ optional($member)->email }}">{{ optional($member)->name ?? '(deleted user)' }}</span>
                                @if ($jobTitle = optional(optional($member)->profile)->job_title)
                                    <br>
                                    <span class="cell-truncate" style="font-size:10.5px;" title="{{ $jobTitle }}">{{ $jobTitle }}</span>
                                @endif
                            </td>
                            <td><span class="cell-truncate" title="{{ optional(optional($member)->company)->company_name }}">{{ optional(optional($member)->company)->company_name }}</span></td>
                            <td>@if (optional($member)->email)<a href="mailto:{{ $member->email }}">{{ $member->email }}</a>@else — @endif</td>
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
                                <small class="lead-channel"><i class="{{ $item->channel === 'whatsapp' ? 'fab fa-whatsapp' : ($item->channel === 'email' ? 'far fa-envelope' : 'fas fa-phone') }} mr-1" @if (!$item->channel) hidden @endif></i>{{ $item->channel ? ucfirst($item->channel) : '-' }}</small>
                                <a href="#" class="text-muted ml-1 btn-open-channel-modal" title="Edit channel" data-toggle="tooltip"
                                    data-member-name="{{ optional($member)->name }}"
                                    data-channel="{{ $item->channel }}"
                                    data-url="{{ route('admin.member_leads.update_channel', $item->id) }}"><i class="fas fa-pen" style="font-size:9px;"></i></a>
                            </td>
                            <td class="text-nowrap">
                                @if ($item->do_not_send)
                                    <span class="badge lead-badge-excluded">—</span>
                                @else
                                <a href="#" class="badge temp-badge temp-{{ $item->temperature ?: 'cold' }} btn-open-temperature-modal"
                                    data-member-name="{{ optional($member)->name }}"
                                    data-get-url="{{ route('admin.member_leads.temperature', $item->id) }}"
                                    data-save-url="{{ route('admin.member_leads.update_temperature', $item->id) }}">
                                    {{ strtoupper($item->temperature ?: 'cold') }} · {{ (int) $item->temperature_score }}
                                </a>
                                @endif
                            </td>
                            <td>
                                @if ($item->do_not_send)
                                    <span class="badge lead-badge-excluded" title="{{ $item->do_not_send_reason }}">Excluded</span>
                                @elseif ($item->result === \App\Models\MemberLeadFollowUp::RESULT_WIN)
                                    <span class="badge badge-success mini-badge">Win</span>
                                @elseif ($item->result === \App\Models\MemberLeadFollowUp::RESULT_LOSS)
                                    <span class="badge badge-danger mini-badge">Loss</span>
                                @else
                                    <span class="badge badge-warning mini-badge">Pending</span>
                                @endif
                            </td>
                            @foreach (['sponsorkit' => 'sponsorkit_sent_at', 'follow_up_1' => 'first_follow_up_at', 'follow_up_2' => 'second_follow_up_at'] as $step => $field)
                            <td class="text-nowrap">
                                @if ($item->$field)
                                    <div class="lead-step lead-step-sent"><i class="fas fa-check-circle"></i><span><strong>Sent</strong><small>{{ $item->$field->format('d M H:i') }}</small></span></div>
                                @elseif ($item->do_not_send)
                                    <span class="lead-dash">—</span>
                                @elseif ($isPending && $nextStepKey === $step && $isOverSla)
                                    <div class="lead-step lead-step-overdue"><i class="fas fa-exclamation-circle"></i><span><strong>Overdue</strong><small>{{ optional($item->deadline_at)->format('d M H:i') }}</small></span></div>
                                @else
                                    <span class="lead-step lead-step-empty" title="Not scheduled"><i class="far fa-circle"></i> —</span>
                                @endif
                            </td>
                            @endforeach
                            <td class="text-nowrap">
                                @if ($isPending && !$item->do_not_send && $nextStepKey)
                                    <button type="button" class="lead-next-action btn-open-follow-up-log-modal"
                                        data-log-url="{{ route('admin.member_leads.log_follow_up', $item->id) }}"
                                        data-step-label="{{ \App\Models\MemberLeadFollowUp::stepLabel($nextStepKey) }}"
                                        data-member-name="{{ optional($member)->name }}" data-channel="{{ $item->channel }}">
                                        <i class="far fa-calendar-alt"></i><span><strong>{{ ['sponsorkit' => 'Send Kit', 'follow_up_1' => 'FU 1', 'follow_up_2' => 'FU 2'][$nextStepKey] }}</strong><small>{{ optional($item->deadline_at)->format('d M H:i') ?: 'No deadline' }}</small></span>
                                    </button>
                                @else
                                    <span class="lead-dash">—</span>
                                @endif
                            </td>
                            <td class="text-nowrap"><small>{{ $item->pic_name ?: '-' }}</small></td>
                            <td>
                                @if ($item->do_not_send && $item->do_not_send_reason)
                                    <span class="lead-note" title="{{ $item->do_not_send_reason }}">{{ $item->do_not_send_reason }}</span>
                                @elseif ($latestNote)
                                    <span class="cell-truncate" title="{{ $latestNote->stepLabel() }}: {{ $latestNote->note }}">
                                        <span class="text-muted">[{{ $latestNote->stepLabel() }}]</span> {{ $latestNote->note }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                                <br>
                                <a href="#" class="small btn-open-notes-modal"
                                    data-member-name="{{ optional($member)->name }}"
                                    data-default-step="{{ $lastDoneStep }}"
                                    data-list-url="{{ route('admin.member_leads.notes', $item->id) }}"
                                    data-store-url="{{ route('admin.member_leads.store_note', $item->id) }}">
                                    <i class="fas fa-history mr-1"></i>{{ $item->notesHistory->count() ? $item->notesHistory->count() . ' note(s)' : 'Add note' }}
                                </a>
                            </td>
                            <td class="text-nowrap">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border py-1 px-2" type="button"
                                        aria-label="Actions for {{ optional($member)->name ?? 'lead' }}" data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v text-muted"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                        <button type="button"
                                            class="dropdown-item btn-open-notes-modal"
                                            data-member-name="{{ optional($member)->name }}"
                                            data-default-step="{{ $lastDoneStep }}"
                                            data-list-url="{{ route('admin.member_leads.notes', $item->id) }}"
                                            data-store-url="{{ route('admin.member_leads.store_note', $item->id) }}">
                                            <i class="fas fa-history mr-2 text-secondary"></i>Notes / Reply
                                        </button>
                                        <button type="button"
                                            class="dropdown-item btn-open-channel-modal"
                                            data-member-name="{{ optional($member)->name }}"
                                            data-channel="{{ $item->channel }}"
                                            data-url="{{ route('admin.member_leads.update_channel', $item->id) }}">
                                            <i class="fas fa-pen mr-2 text-secondary"></i>Edit Channel
                                        </button>
                                        @if ($isPending || $item->do_not_send || !$item->sponsorkit_sent_at)
                                            <div class="dropdown-divider"></div>
                                        @endif
                                        @if ($isPending && $nextStepKey)
                                            <button type="button"
                                                class="dropdown-item btn-open-follow-up-log-modal"
                                                data-lead-id="{{ $item->id }}"
                                                data-member-name="{{ optional($member)->name }}"
                                                data-step-key="{{ $nextStepKey }}"
                                                data-step-label="{{ \App\Models\MemberLeadFollowUp::stepLabel($nextStepKey) }}"
                                                data-channel="{{ $item->channel }}"
                                                data-log-url="{{ route('admin.member_leads.log_follow_up', $item->id) }}">
                                                <i class="fas {{ $nextStepKey === 'sponsorkit' ? 'fa-gift' : 'fa-comment-dots' }} mr-2 text-secondary"></i>
                                                Log {{ \App\Models\MemberLeadFollowUp::stepLabel($nextStepKey) }}
                                            </button>
                                        @endif
                                        @if ($item->result !== 'win')
                                            <button type="button"
                                                class="dropdown-item btn-mark-lead-result"
                                                data-url="{{ route('admin.member_leads.mark_result', $item->id) }}"
                                                data-result="win">
                                                <i class="fas fa-check mr-2 text-success"></i>Mark as Win
                                            </button>
                                        @endif
                                        @if ($item->result !== 'loss')
                                            <button type="button"
                                                class="dropdown-item btn-mark-lead-result"
                                                data-url="{{ route('admin.member_leads.mark_result', $item->id) }}"
                                                data-result="loss">
                                                <i class="fas fa-times mr-2 text-secondary"></i>Mark as Loss
                                            </button>
                                        @endif
                                        @if (!$isPending)
                                            <button type="button" class="dropdown-item btn-mark-lead-result"
                                                data-url="{{ route('admin.member_leads.mark_result', $item->id) }}" data-result="pending">
                                                <i class="fas fa-undo mr-2 text-secondary"></i>Mark as Pending
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
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="17" class="text-center text-muted py-4">No leads match these filters. Try clearing the filters or add a member to lead.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
</div>
<div class="lead-pagination">
    <div class="lead-page-info">
        <label for="lead-per-page">Show</label>
        <select id="lead-per-page" class="lead-page-size" aria-label="Leads per page">
            @foreach ([10, 25, 50] as $size)
                <option value="{{ $size }}" {{ $perPage === $size ? 'selected' : '' }}>{{ $size }}</option>
            @endforeach
        </select>
        <span>per page <span class="lead-toolbar-divider">·</span> {{ $list->firstItem() ?? 0 }}–{{ $list->lastItem() ?? 0 }} of {{ number_format($list->total()) }} leads</span>
    </div>
    {{ $list->onEachSide(1)->links('pagination::bootstrap-4') }}
</div>
