                    {{-- ── PENDING RENEWAL TAB ── --}}
                    <div class="tab-pane fade" id="pendingPane" role="tabpanel">
                        @if($pendingRenewals->isNotEmpty())
                        @php
                            $stageCounts = $pendingRenewals->countBy(fn ($p) => $p->pending_stage ?? 'upcoming');
                        @endphp
                        <div style="padding:16px 20px 0;">
                            <div style="background:#fffbf0;border:1px solid #fde3aa;border-left:4px solid #f39c12;border-radius:8px;padding:14px 18px;">
                                <div class="font-weight-700 mb-1" style="color:#8a4a00;font-size:13px;">
                                    <i class="fas fa-info-circle mr-1" style="color:#f39c12;"></i> About this list
                                </div>
                                <div style="font-size:12.5px;color:#7a6243;line-height:1.6;">
                                    These sponsors are <strong>still active</strong>, but their contracts end during {{ $year }} and we have
                                    <strong>not yet received their decision</strong> for the next period — renew, upgrade, or stop sponsoring.
                                    Sponsors who already renewed this year may still appear here if their new contract also ends within {{ $year }}.
                                    Once a renewal is recorded or the sponsor is marked as &ldquo;Not Renewed&rdquo;, they automatically leave this list.
                                </div>
                                <div style="font-size:12.5px;color:#7a6243;line-height:1.7;margin-top:6px;">
                                    <strong style="color:#fc544b;">Pending</strong> — the contract has already passed without confirmation (overdue, follow up immediately).
                                    <br><strong style="color:#e67e22;">Awaiting</strong> — the contract ends this month; a decision is expected now.
                                    <br><strong style="color:#6c757d;">Upcoming</strong> — the contract ends in a later month; on the radar, not urgent yet.
                                </div>
                                <div class="d-flex flex-wrap mt-2" style="gap:8px;">
                                    @if(($stageCounts['pending'] ?? 0) > 0)
                                        <span style="font-size:11.5px;font-weight:700;color:#fc544b;background:#fde8e8;border-radius:10px;padding:3px 10px;">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>{{ $stageCounts['pending'] }} Pending
                                        </span>
                                    @endif
                                    @if(($stageCounts['awaiting'] ?? 0) > 0)
                                        <span style="font-size:11.5px;font-weight:700;color:#8a4a00;background:#fde3aa;border-radius:10px;padding:3px 10px;">
                                            <i class="fas fa-hourglass-half mr-1"></i>{{ $stageCounts['awaiting'] }} Awaiting
                                        </span>
                                    @endif
                                    @if(($stageCounts['upcoming'] ?? 0) > 0)
                                        <span style="font-size:11.5px;font-weight:700;color:#6c757d;background:#f0f1f5;border-radius:10px;padding:3px 10px;">
                                            <i class="fas fa-clock mr-1"></i>{{ $stageCounts['upcoming'] }} Upcoming
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="font-size:13px;">
                                <thead>
                                    <tr style="background:#fffbf0; border-bottom: 2px solid #fde3aa;">
                                        <th style="width:40px; padding:12px 16px;">#</th>
                                        <th style="min-width:220px;">Company</th>
                                        <th style="width:90px;">Package</th>
                                        <th style="min-width:190px;">Current Period</th>
                                        <th style="width:140px;">Contract End</th>
                                        <th style="width:130px;">Status</th>
                                        <th style="width:110px;">Type</th>
                                        <th style="min-width:200px;">PIC Contact</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pendingRenewals as $i => $p)
                                    @php
                                        $pic = $p->sponsor ? $p->sponsor->firstPic : null;
                                        $pkgColors = ['platinum'=>'#6777ef','gold'=>'#f39c12','silver'=>'#6c757d'];
                                        $pkgColor  = $pkgColors[$p->package] ?? '#6c757d';
                                        $typeMap = [
                                            'renewal'    => ['label' => 'Renewal',    'bg' => '#3abaf4', 'icon' => 'fas fa-redo-alt'],
                                            'upgrade'    => ['label' => 'Upgrade',    'bg' => '#f39c12', 'icon' => 'fas fa-arrow-up'],
                                            'new'        => ['label' => 'New',        'bg' => '#47c363', 'icon' => 'fas fa-star'],
                                            'new_member' => ['label' => 'New Member', 'bg' => '#47c363', 'icon' => 'fas fa-user-plus'],
                                        ];
                                        $typeInfo = $typeMap[$p->renewal_type] ?? ['label' => ucfirst($p->renewal_type ?? '—'), 'bg' => '#6c757d', 'icon' => 'fas fa-tag'];

                                        $mShort = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'May','06'=>'Jun',
                                                   '07'=>'Jul','08'=>'Aug','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dec'];
                                        $period = '—';
                                        if ($p->contract_start && $p->contract_end) {
                                            [$sy,$sm] = explode('-', $p->contract_start);
                                            [$ey,$em] = explode('-', $p->contract_end);
                                            $period = ($mShort[$sm] ?? $sm).' '.$sy.' – '.($mShort[$em] ?? $em).' '.$ey;
                                        }
                                        $endDate  = $p->contract_end ? \Carbon\Carbon::createFromFormat('Y-m', $p->contract_end)->endOfMonth() : null;
                                        $daysLeft = $endDate ? (int) now()->diffInDays($endDate, false) : null;
                                    @endphp
                                    <tr>
                                        <td style="padding:12px 16px; color:#888;">{{ $i+1 }}</td>
                                        <td style="padding:12px 16px;">
                                            <div class="font-weight-700" style="color:#2d3748;">
                                                {{ $p->sponsor ? $p->sponsor->name : '—' }}
                                            </div>
                                            @if($p->sponsor && $p->sponsor->branding_name && $p->sponsor->branding_name !== $p->sponsor->name)
                                                <div style="font-size:11px;color:#888;">{{ $p->sponsor->branding_name }}</div>
                                            @endif
                                        </td>
                                        <td style="padding:12px 16px;">
                                            <span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;color:#fff;background:{{ $pkgColor }};">
                                                {{ ucfirst($p->package ?? '—') }}
                                            </span>
                                        </td>
                                        <td style="padding:12px 16px; color:#555; font-size:12px;">{{ $period }}</td>
                                        <td style="padding:12px 16px;">
                                            <div style="font-size:12px;font-weight:600;color:#555;">
                                                {{ $p->contract_end ? \Carbon\Carbon::createFromFormat('Y-m', $p->contract_end)->format('M Y') : '—' }}
                                            </div>
                                            @if($daysLeft !== null)
                                                @if($daysLeft < 0)
                                                    <div style="font-size:10px;color:#fc544b;font-weight:600;"><i class="fas fa-exclamation-circle"></i> Expired {{ abs($daysLeft) }}d ago</div>
                                                @elseif($daysLeft <= 30)
                                                    <div style="font-size:10px;color:#fc544b;font-weight:600;"><i class="fas fa-fire"></i> {{ $daysLeft }}d left</div>
                                                @elseif($daysLeft <= 90)
                                                    <div style="font-size:10px;color:#f39c12;font-weight:600;"><i class="fas fa-clock"></i> {{ $daysLeft }}d left</div>
                                                @else
                                                    <div style="font-size:10px;color:#47c363;">{{ $daysLeft }}d left</div>
                                                @endif
                                            @endif
                                        </td>
                                        <td style="padding:12px 16px;">
                                            @include('admin.sponsor.annual-report._pending-stage-badge', ['stage' => $p->pending_stage ?? 'upcoming'])
                                        </td>
                                        <td style="padding:12px 16px;">
                                            <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;color:#fff;background:{{ $typeInfo['bg'] }};">
                                                <i class="{{ $typeInfo['icon'] }}" style="font-size:10px;"></i>
                                                {{ $typeInfo['label'] }}
                                            </span>
                                        </td>
                                        <td style="padding:12px 16px;">
                                            @include('admin.sponsor.annual-report._pic-contact', ['pic' => $pic, 'color' => '#f39c12'])
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="fas fa-check-circle fa-2x mb-3 d-block" style="opacity:.3;color:#47c363;"></i>
                                            All expiring contracts in {{ $year }} have been followed up — nothing pending.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
