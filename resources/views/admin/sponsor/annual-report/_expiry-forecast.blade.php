            {{-- ═══ CONTRACT EXPIRY FORECAST ═══ --}}
            @php
                $monthNames = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',
                               7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
                $monthShort = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',
                               7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'];
                $totalExpiring = $expiryForecast->sum(fn($g) => $g->count());
                $maxExpiry = $expiryForecast->isNotEmpty() ? $expiryForecast->map(fn($g) => $g->count())->max() : 1;

                // Split: months already passed vs remaining (only meaningful for the current year)
                if ($year == now()->year) {
                    $firstUpcomingMonth = (int) now()->month;
                } elseif ($year < now()->year) {
                    $firstUpcomingMonth = 13; // whole year has passed
                } else {
                    $firstUpcomingMonth = 1;  // future year: everything still upcoming
                }
                $passedExpiring   = $expiryForecast->filter(fn ($g, $m) => $m <  $firstUpcomingMonth)->sum(fn ($g) => $g->count());
                $upcomingExpiring = $totalExpiring - $passedExpiring;
                $passedUnresolved = $expiryForecast
                    ->filter(fn ($g, $m) => $m < $firstUpcomingMonth)
                    ->sum(fn ($g) => $g->where('followup_status', 'pending')->count());
            @endphp

            <div class="card" id="priorityContracts" style="border-top: 3px solid #f39c12;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">
                            <i class="fas fa-calendar-times mr-2" style="color:#f39c12;"></i>
                            30-Day Priority Contracts — {{ $year }}
                        </h4>
                        <small class="text-muted">Contracts that expire during {{ $year }} — these sponsors will need follow-up for renewal</small>
                    </div>
                    <div class="text-right">
                        @if($peakExpiryMonth)
                            <span style="font-size:12px;color:#888;">Peak month:</span>
                            <span class="ml-1 font-weight-700" style="color:#f39c12;font-size:14px;">
                                {{ $monthNames[$peakExpiryMonth] }}
                                <span style="font-size:12px;color:#888;">({{ $expiryForecast->get($peakExpiryMonth)->count() }} contracts)</span>
                            </span>
                        @endif
                        <div style="font-size:12px;color:#888;margin-top:2px;">
                            Total expiring: <strong>{{ $totalExpiring }}</strong>
                            @if($passedExpiring > 0 && $upcomingExpiring > 0)
                                <span style="color:#bbb;">·</span>
                                Passed: <strong>{{ $passedExpiring }}</strong>
                                <span style="color:#bbb;">·</span>
                                Upcoming ({{ $monthShort[$firstUpcomingMonth] }}–Dec): <strong style="color:#f39c12;">{{ $upcomingExpiring }}</strong>
                            @endif
                        </div>
                        @if($passedUnresolved > 0)
                            <div style="font-size:11px;font-weight:600;color:#fc544b;margin-top:2px;">
                                <i class="fas fa-exclamation-triangle mr-1"></i>{{ $passedUnresolved }} Pending — contract{{ $passedUnresolved != 1 ? 's' : '' }} already passed with no confirmation
                            </div>
                        @endif
                    </div>
                </div>

                @if($expiryForecast->isNotEmpty())
                <div class="card-body pb-2">
                    {{-- Month heatmap strip — click any month to see sponsors --}}
                    <div class="row flex-nowrap mb-0" style="margin:0 -3px;">
                        @for($m = 1; $m <= 12; $m++)
                        @php
                            $mCount = $expiryForecast->has($m) ? $expiryForecast->get($m)->count() : 0;
                            $isPeak = ($m == $peakExpiryMonth);
                            $intensity = $mCount > 0 ? min(100, round($mCount / $maxExpiry * 100)) : 0;
                            if ($mCount == 0) {
                                $bg = '#f8f9fa'; $textColor = '#bbb'; $border = '#e9ecef';
                            } elseif ($isPeak) {
                                $bg = '#f39c12'; $textColor = '#fff'; $border = '#e67e22';
                            } elseif ($intensity >= 70) {
                                $bg = '#fde3aa'; $textColor = '#8a4a00'; $border = '#f39c12';
                            } elseif ($intensity >= 40) {
                                $bg = '#fef3dc'; $textColor = '#8a4a00'; $border = '#fac56a';
                            } else {
                                $bg = '#fffbf0'; $textColor = '#a07030'; $border = '#fde3aa';
                            }
                        @endphp
                        <div style="flex:1;padding:0 3px 8px;">
                            <div id="monthBox{{ $m }}"
                                 onclick="selectMonth({{ $m }})"
                                 onmouseenter="this.style.transform='translateY(-2px)'"
                                 onmouseleave="this.style.transform=''"
                                 style="background:{{ $bg }};border:2px solid {{ $border }};border-radius:8px;text-align:center;padding:10px 4px;cursor:pointer;transition:transform .15s,box-shadow .15s;">
                                <div style="font-size:10px;font-weight:600;color:{{ $textColor }};letter-spacing:.3px;text-transform:uppercase;">{{ $monthShort[$m] }}</div>
                                <div style="font-size:20px;font-weight:800;color:{{ $textColor }};line-height:1.2;margin:2px 0;">{{ $mCount > 0 ? $mCount : '0' }}</div>
                                @if($isPeak)
                                    <div style="font-size:9px;color:#fff;background:#e67e22;border-radius:4px;padding:1px 5px;display:inline-block;margin-top:2px;font-weight:700;">PEAK</div>
                                @else
                                    <div style="font-size:9px;color:{{ $textColor }};opacity:.6;">contract{{ $mCount != 1 ? 's' : '' }}</div>
                                @endif
                            </div>
                        </div>
                        @endfor
                    </div>
                </div>

                {{-- Detail area: shows selected month's sponsors --}}
                <div id="expiryDetailArea" style="display:none; border-top:2px solid #f39c12;">

                {{-- Pre-render all 12 months (hidden), JS swaps them in --}}
                @for($m = 1; $m <= 12; $m++)
                @php $mSponsorsForSlot = $expiryForecast->has($m) ? $expiryForecast->get($m) : collect(); @endphp
                <div id="expiryMonth{{ $m }}" style="display:none;">
                    {{-- Month header --}}
                    <div style="background:#fffbf0;padding:10px 20px 8px;display:flex;align-items:center;gap:12px;">
                        <span style="font-weight:700;font-size:14px;color:#8a4a00;">
                            <i class="fas fa-calendar-alt mr-1" style="color:#f39c12;"></i>
                            {{ $monthNames[$m] }} {{ $year }}
                        </span>
                        @if($mSponsorsForSlot->isNotEmpty())
                            <span style="font-size:12px;color:#aaa;">{{ $mSponsorsForSlot->count() }} contract{{ $mSponsorsForSlot->count() != 1 ? 's' : '' }} expiring</span>
                            @php
                                $fuConfirmed = $mSponsorsForSlot->whereIn('followup_status', ['renewed', 'upgraded'])->count();
                                $fuStopped   = $mSponsorsForSlot->where('followup_status', 'stopped')->count();
                                $fuOpen      = $mSponsorsForSlot->count() - $fuConfirmed - $fuStopped;
                                // Urgency label based on the month position relative to the current month
                                if ($m < $firstUpcomingMonth) {
                                    $fuOpenLabel = 'pending'; $fuOpenColor = '#fc544b'; $fuOpenBg = '#fde8e8'; $fuOpenIcon = 'fas fa-exclamation-triangle';
                                } elseif ($m == $firstUpcomingMonth && $year == now()->year) {
                                    $fuOpenLabel = 'awaiting'; $fuOpenColor = '#8a4a00'; $fuOpenBg = '#fde3aa'; $fuOpenIcon = 'fas fa-hourglass-half';
                                } else {
                                    $fuOpenLabel = 'upcoming'; $fuOpenColor = '#6c757d'; $fuOpenBg = '#f0f1f5'; $fuOpenIcon = 'fas fa-clock';
                                }
                            @endphp
                            <span style="display:inline-flex;gap:6px;margin-left:auto;">
                                @if($fuConfirmed > 0)
                                    <span style="font-size:11px;font-weight:600;color:#47c363;background:#eafaf0;border-radius:10px;padding:2px 8px;"><i class="fas fa-check mr-1"></i>{{ $fuConfirmed }} confirmed</span>
                                @endif
                                @if($fuStopped > 0)
                                    <span style="font-size:11px;font-weight:600;color:#fc544b;background:#fde8e8;border-radius:10px;padding:2px 8px;"><i class="fas fa-ban mr-1"></i>{{ $fuStopped }} stopped</span>
                                @endif
                                @if($fuOpen > 0)
                                    <span style="font-size:11px;font-weight:600;color:{{ $fuOpenColor }};background:{{ $fuOpenBg }};border-radius:10px;padding:2px 8px;"><i class="{{ $fuOpenIcon }} mr-1"></i>{{ $fuOpen }} {{ $fuOpenLabel }}</span>
                                @endif
                            </span>
                        @endif
                    </div>
                    {{-- Sponsors table or empty state --}}
                    @if($mSponsorsForSlot->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size:13px;">
                            <thead>
                                <tr style="background:#fff8ee;">
                                    <th style="width:36px;padding:8px 16px;">#</th>
                                    <th style="min-width:200px;">Company</th>
                                    <th style="width:90px;">Package</th>
                                    <th style="width:140px;">Contract End</th>
                                    <th style="width:110px;">Renewal Type</th>
                                    <th style="min-width:160px;">Follow-up Status</th>
                                    <th>PIC Contact</th>
                                    <th style="width:140px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($mSponsorsForSlot as $ei => $er)
                                @php
                                    $ePic = $er->sponsor ? $er->sponsor->firstPic : null;
                                    $pkgColors = ['platinum'=>'#6777ef','gold'=>'#f39c12','silver'=>'#6c757d'];
                                    $ePkgColor = $pkgColors[$er->package] ?? '#6c757d';
                                    $eTypeMap  = [
                                        'renewal'    => ['label'=>'Renewal',    'bg'=>'#3abaf4'],
                                        'upgrade'    => ['label'=>'Upgrade',    'bg'=>'#f39c12'],
                                        'new'        => ['label'=>'New',        'bg'=>'#47c363'],
                                        'new_member' => ['label'=>'New Member', 'bg'=>'#47c363'],
                                    ];
                                    $eType = $eTypeMap[$er->renewal_type] ?? ['label'=>ucfirst($er->renewal_type ?? '—'), 'bg'=>'#6c757d'];
                                    $endDate  = $er->contract_end ? \Carbon\Carbon::createFromFormat('Y-m', $er->contract_end)->endOfMonth() : null;
                                    $daysLeft = $endDate ? (int) now()->diffInDays($endDate, false) : null;
                                @endphp
                                <tr>
                                    <td style="padding:10px 16px;color:#aaa;">{{ $ei+1 }}</td>
                                    <td style="padding:10px 16px;">
                                        <div class="font-weight-700" style="color:#2d3748;">{{ $er->sponsor ? $er->sponsor->name : '—' }}</div>
                                        @if($er->sponsor && $er->sponsor->branding_name && $er->sponsor->branding_name !== $er->sponsor->name)
                                            <div style="font-size:11px;color:#888;">{{ $er->sponsor->branding_name }}</div>
                                        @endif
                                    </td>
                                    <td style="padding:10px 16px;">
                                        <span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;color:#fff;background:{{ $ePkgColor }};">
                                            {{ ucfirst($er->package ?? '—') }}
                                        </span>
                                    </td>
                                    <td style="padding:10px 16px;">
                                        <div style="font-size:12px;font-weight:600;color:#555;">
                                            {{ $er->contract_end ? \Carbon\Carbon::createFromFormat('Y-m', $er->contract_end)->format('M Y') : '—' }}
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
                                    <td style="padding:10px 16px;">
                                        <span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;color:#fff;background:{{ $eType['bg'] }};">
                                            {{ $eType['label'] }}
                                        </span>
                                    </td>
                                    <td style="padding:10px 16px;">
                                        @php
                                            $fu  = $er->followup_status ?? 'pending';
                                            $fuR = $er->followup_record ?? null;
                                            $fuPeriod = null;
                                            if ($fuR && $fuR->contract_start && $fuR->contract_end) {
                                                $fuPeriod = \Carbon\Carbon::createFromFormat('Y-m', $fuR->contract_start)->format('M Y')
                                                    . ' – '
                                                    . \Carbon\Carbon::createFromFormat('Y-m', $fuR->contract_end)->format('M Y');
                                            }
                                        @endphp
                                        @if($fu === 'renewed')
                                            <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;color:#fff;background:#47c363;">
                                                <i class="fas fa-check" style="font-size:10px;"></i> Renewed
                                            </span>
                                            <div style="font-size:10px;color:#888;margin-top:3px;">
                                                {{ ucfirst($fuR->package ?? '') }}{{ $fuPeriod ? ' · ' . $fuPeriod : '' }}
                                            </div>
                                        @elseif($fu === 'upgraded')
                                            <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;color:#fff;background:#6777ef;">
                                                <i class="fas fa-arrow-up" style="font-size:10px;"></i> Upgraded
                                            </span>
                                            <div style="font-size:10px;color:#888;margin-top:3px;">
                                                {{ ucfirst($er->package ?? '?') }} → <strong>{{ ucfirst($fuR->package ?? '?') }}</strong>{{ $fuPeriod ? ' · ' . $fuPeriod : '' }}
                                            </div>
                                        @elseif($fu === 'stopped')
                                            <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;color:#fff;background:#fc544b;">
                                                <i class="fas fa-ban" style="font-size:10px;"></i> Stopped
                                            </span>
                                            @if($fuR && $fuR->notes)
                                                <div style="font-size:10px;color:#888;margin-top:3px;max-width:180px;">{{ Str::limit($fuR->notes, 60) }}</div>
                                            @endif
                                        @else
                                            @include('admin.sponsor.annual-report._pending-stage-badge', ['stage' => $er->pending_stage ?? 'upcoming'])
                                            {{-- Alur follow-up: Renewal Form Submitted → Follow Up N → tanggal | PIC --}}
                                            @if(($er->followup_count ?? 0) > 0)
                                                <div style="margin-top:6px;border-left:2px solid #e9ecef;padding-left:8px;">
                                                    <div style="font-size:10px;font-weight:700;color:#47c363;">
                                                        <i class="fas fa-file-signature mr-1"></i>Renewal Form Submitted
                                                    </div>
                                                    @if($er->first_followup)
                                                        <div style="font-size:10px;color:#aaa;">{{ $er->first_followup->followed_up_at->format('d M Y') }}</div>
                                                    @endif
                                                    <div style="font-size:11px;font-weight:600;color:#3a7bd5;margin-top:2px;">
                                                        <i class="fas fa-phone-volume mr-1" style="font-size:9px;"></i>Follow Up {{ $er->followup_count }}
                                                    </div>
                                                    @if($er->last_followup)
                                                        <div style="font-size:10px;color:#888;">
                                                            {{ $er->last_followup->followed_up_at->format('d M Y') }}{{ $er->last_followup->creator ? ' | ' . $er->last_followup->creator->name : '' }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <div style="margin-top:6px;font-size:10px;color:#aaa;">
                                                    <i class="fas fa-user-slash mr-1"></i>No follow-up yet
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                    <td style="padding:10px 16px;">
                                        @include('admin.sponsor.annual-report._pic-contact', ['pic' => $ePic, 'color' => '#f39c12'])
                                    </td>
                                    <td style="padding:10px 16px;">
                                        @if($fu === 'pending')
                                            @php
                                                $exNextStart = $er->contract_end ? \Carbon\Carbon::createFromFormat('Y-m', $er->contract_end)->addMonth()->format('Y-m') : '';
                                                $exNextEnd   = $er->contract_end ? \Carbon\Carbon::createFromFormat('Y-m', $er->contract_end)->addMonths(12)->format('Y-m') : '';
                                            @endphp
                                            <div class="d-flex" style="gap:4px;">
                                                <button class="btn btn-sm action-icon-btn followup-btn"
                                                    style="background:#f39c12;color:#fff;"
                                                    data-id="{{ $er->sponsor_id }}"
                                                    data-name="{{ $er->sponsor ? $er->sponsor->name : '' }}"
                                                    data-toggle="tooltip" title="Renewal Follow-up">
                                                    <i class="fas fa-phone-volume"></i>
                                                </button>
                                                <button class="btn btn-sm btn-primary action-icon-btn update-contract-btn"
                                                    data-sponsor-id="{{ $er->sponsor_id }}"
                                                    data-contract-start="{{ $exNextStart }}"
                                                    data-contract-end="{{ $exNextEnd }}"
                                                    data-package="{{ $er->package }}"
                                                    data-toggle="tooltip" title="Confirm Renewal / Update Contract">
                                                    <i class="fas fa-file-signature"></i>
                                                </button>
                                                <button class="btn btn-sm btn-warning action-icon-btn not-renewed-btn"
                                                    data-id="{{ $er->sponsor_id }}"
                                                    data-name="{{ $er->sponsor ? $er->sponsor->name : '' }}"
                                                    data-contract-start="{{ $er->contract_start }}"
                                                    data-contract-end="{{ $er->contract_end }}"
                                                    data-toggle="tooltip" title="Mark Not Renewed">
                                                    <i class="fas fa-times-circle"></i>
                                                </button>
                                                <a href="{{ route('sponsors.renewal-form.preview', $er->sponsor_id) }}"
                                                    target="_blank"
                                                    class="btn btn-sm btn-outline-secondary action-icon-btn"
                                                    data-toggle="tooltip" title="Preview Renewal Form">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                            </div>
                                        @else
                                            <span class="text-muted" style="font-size:11px;">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-calendar-check fa-lg mb-2 d-block" style="opacity:.3;"></i>
                        <span style="font-size:13px;">No contracts expiring in {{ $monthNames[$m] }} {{ $year }}</span>
                    </div>
                    @endif
                </div>
                @endfor
                </div>{{-- end expiryDetailArea --}}

                @else
                <div class="card-body text-center py-5 text-muted">
                    <i class="fas fa-calendar-check fa-2x mb-3 d-block" style="opacity:.3;"></i>
                    No contracts expiring in {{ $year }}.
                </div>
                @endif
            </div>

@push('bottom')
<script>
    var activeMonth = null;

    function selectMonth(monthNum) {
        var detailArea = document.getElementById('expiryDetailArea');

        // Hide current active month panel
        if (activeMonth !== null) {
            var prev = document.getElementById('expiryMonth' + activeMonth);
            if (prev) prev.style.display = 'none';
            // Remove active style from previous box
            resetBoxStyle(activeMonth);
        }

        // If clicking the same month again — close it
        if (activeMonth === monthNum) {
            detailArea.style.display = 'none';
            activeMonth = null;
            return;
        }

        // Show new month panel
        var el = document.getElementById('expiryMonth' + monthNum);
        if (el) {
            detailArea.style.display = 'block';
            el.style.display = 'block';
            activeMonth = monthNum;
            // Mark box as active
            setBoxActive(monthNum);
            // Smooth scroll to detail area
            setTimeout(function() {
                detailArea.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 50);
        }
    }

    function setBoxActive(monthNum) {
        var box = document.getElementById('monthBox' + monthNum);
        if (box) {
            box.style.boxShadow = '0 0 0 3px #f39c12, 0 4px 12px rgba(243,156,18,.35)';
            box.style.transform = 'translateY(-3px)';
        }
    }

    function resetBoxStyle(monthNum) {
        var box = document.getElementById('monthBox' + monthNum);
        if (box) {
            box.style.boxShadow = '';
            box.style.transform = '';
        }
    }

    // Auto-open peak month on load
    @if($peakExpiryMonth)
    document.addEventListener('DOMContentLoaded', function() {
        selectMonth({{ $peakExpiryMonth }});
    });
    @endif
</script>
@endpush
