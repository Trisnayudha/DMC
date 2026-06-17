                    {{-- ── RENEWED TAB ── --}}
                    <div class="tab-pane fade {{ ($renewalType !== 'not_renewed') ? 'show active' : '' }}"
                         id="renewedPane" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="font-size:13px;">
                                <thead>
                                    <tr style="background:#f0fff4; border-bottom: 2px solid #b2dfdb;">
                                        <th style="width:40px; padding:12px 16px;">#</th>
                                        <th style="min-width:220px;">Company</th>
                                        <th style="width:90px;">Package</th>
                                        <th style="min-width:190px;">Period</th>
                                        <th style="width:110px;">Type</th>
                                        <th style="min-width:200px;">PIC Contact</th>
                                        <th>Confirmation / Notes</th>
                                        <th style="width:80px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($renewedSponsors as $i => $r)
                                    @php
                                        $pic = $r->sponsor ? $r->sponsor->firstPic : null;
                                        $typeMap = [
                                            'renewal'    => ['label' => 'Renewal',    'bg' => '#3abaf4', 'icon' => 'fas fa-redo-alt'],
                                            'upgrade'    => ['label' => 'Upgrade',    'bg' => '#f39c12', 'icon' => 'fas fa-arrow-up'],
                                            'new'        => ['label' => 'New',        'bg' => '#47c363', 'icon' => 'fas fa-star'],
                                            'new_member' => ['label' => 'New Member', 'bg' => '#47c363', 'icon' => 'fas fa-user-plus'],
                                        ];
                                        $typeInfo = $typeMap[$r->renewal_type] ?? ['label' => ucfirst($r->renewal_type ?? '—'), 'bg' => '#6c757d', 'icon' => 'fas fa-tag'];
                                        $pkgColors = ['platinum'=>'#6777ef','gold'=>'#f39c12','silver'=>'#6c757d'];
                                        $pkgColor  = $pkgColors[$r->package] ?? '#6c757d';

                                        // Format period
                                        $months = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'May','06'=>'Jun',
                                                   '07'=>'Jul','08'=>'Aug','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dec'];
                                        $period = '—';
                                        if ($r->contract_start && $r->contract_end) {
                                            [$sy,$sm] = explode('-', $r->contract_start);
                                            [$ey,$em] = explode('-', $r->contract_end);
                                            $period = ($months[$sm] ?? $sm).' '.$sy.' – '.($months[$em] ?? $em).' '.$ey;
                                        }

                                        // Format confirmation
                                        if ($r->notes) {
                                            $confirmation = $r->notes;
                                        } else {
                                            $pkgMap = ['platinum'=>'Major','gold'=>'Gold','silver'=>'Silver'];
                                            $pkgLabel = $pkgMap[$r->package] ?? ucfirst($r->package ?? '');
                                            $confirmation = 'Confirmed – '.$pkgLabel.' Sponsorship';
                                            if ($r->amount_usd) $confirmation .= ' / USD '.number_format($r->amount_usd,0,'.',',');
                                            if ($r->amount_idr) $confirmation .= ' / IDR '.number_format($r->amount_idr,0,'.',',');
                                        }
                                    @endphp
                                    <tr>
                                        <td style="padding:12px 16px; color:#888;">{{ $i+1 }}</td>
                                        <td style="padding:12px 16px;">
                                            <div class="font-weight-700" style="color:#2d3748;">
                                                {{ $r->sponsor ? $r->sponsor->name : '—' }}
                                            </div>
                                            @if($r->sponsor && $r->sponsor->branding_name && $r->sponsor->branding_name !== $r->sponsor->name)
                                                <div style="font-size:11px;color:#888;">{{ $r->sponsor->branding_name }}</div>
                                            @endif
                                        </td>
                                        <td style="padding:12px 16px;">
                                            <span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;color:#fff;background:{{ $pkgColor }};">
                                                {{ ucfirst($r->package ?? '—') }}
                                            </span>
                                        </td>
                                        <td style="padding:12px 16px; color:#555; font-size:12px;">{{ $period }}</td>
                                        <td style="padding:12px 16px;">
                                            <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;color:#fff;background:{{ $typeInfo['bg'] }};">
                                                <i class="{{ $typeInfo['icon'] }}" style="font-size:10px;"></i>
                                                {{ $typeInfo['label'] }}
                                            </span>
                                        </td>
                                        <td style="padding:12px 16px;">
                                            @include('admin.sponsor.annual-report._pic-contact', ['pic' => $pic, 'color' => '#6777ef'])
                                        </td>
                                        <td style="padding:12px 16px; font-size:12px; color:#555; max-width:260px;">
                                            {{ $confirmation }}
                                        </td>
                                        <td style="padding:10px 12px;">
                                            @if($r->sponsor)
                                            <a href="{{ route('sponsors.renewal-form.preview', $r->sponsor_id) }}"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-secondary"
                                               title="Generate Renewal Form PDF"
                                               style="white-space:nowrap;font-size:11px;">
                                                <i class="fas fa-file-pdf"></i> Form
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-3 d-block" style="opacity:.3;"></i>
                                            No confirmed sponsors found for the selected filters.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
