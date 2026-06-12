                    {{-- ── NOT RENEWED TAB ── --}}
                    <div class="tab-pane fade {{ ($renewalType === 'not_renewed') ? 'show active' : '' }}"
                         id="notRenewedPane" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="font-size:13px;">
                                <thead>
                                    <tr style="background:#fff5f5; border-bottom: 2px solid #ffcccc;">
                                        <th style="width:40px; padding:12px 16px;">#</th>
                                        <th style="min-width:220px;">Company</th>
                                        <th style="width:90px;">Last Package</th>
                                        <th style="min-width:190px;">Last Period</th>
                                        <th style="min-width:200px;">PIC Contact</th>
                                        <th>Reason / Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($notRenewedSponsors as $i => $n)
                                    @php
                                        $pic = $n->sponsor ? $n->sponsor->firstPic : null;
                                        $pkgColors = ['platinum'=>'#6777ef','gold'=>'#f39c12','silver'=>'#6c757d'];
                                        $pkgColor  = $pkgColors[$n->package] ?? '#6c757d';

                                        $months = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'May','06'=>'Jun',
                                                   '07'=>'Jul','08'=>'Aug','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dec'];
                                        $period = '—';
                                        if ($n->contract_start && $n->contract_end) {
                                            [$sy,$sm] = explode('-', $n->contract_start);
                                            [$ey,$em] = explode('-', $n->contract_end);
                                            $period = ($months[$sm] ?? $sm).' '.$sy.' – '.($months[$em] ?? $em).' '.$ey;
                                        }
                                    @endphp
                                    <tr>
                                        <td style="padding:12px 16px; color:#888;">{{ $i+1 }}</td>
                                        <td style="padding:12px 16px;">
                                            <div class="font-weight-700" style="color:#2d3748;">
                                                {{ $n->sponsor ? $n->sponsor->name : '—' }}
                                            </div>
                                            @if($n->sponsor && $n->sponsor->branding_name && $n->sponsor->branding_name !== $n->sponsor->name)
                                                <div style="font-size:11px;color:#888;">{{ $n->sponsor->branding_name }}</div>
                                            @endif
                                        </td>
                                        <td style="padding:12px 16px;">
                                            @if($n->package)
                                            <span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;color:#fff;background:{{ $pkgColor }};">
                                                {{ ucfirst($n->package) }}
                                            </span>
                                            @else
                                            <span style="font-size:11px;color:#bbb;">—</span>
                                            @endif
                                        </td>
                                        <td style="padding:12px 16px; color:#555; font-size:12px;">{{ $period }}</td>
                                        <td style="padding:12px 16px;">
                                            @include('admin.sponsor.annual-report._pic-contact', ['pic' => $pic, 'color' => '#fc544b'])
                                        </td>
                                        <td style="padding:12px 16px; font-size:12px; color:#555; max-width:280px;">
                                            @if($n->notes)
                                                {{ $n->notes }}
                                            @else
                                                <span class="text-muted" style="font-style:italic;">No notes recorded</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-3 d-block" style="opacity:.3;"></i>
                                            No sponsors marked as not renewed for {{ $year }}.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
