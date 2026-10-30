            <!-- 1. EXECUTIVE DIAGRAM SECTION (Sesuai Slide Internal Operating Guideline) -->
            <div class="conversion-diagram-container mb-4">
                <div class="row align-items-center mb-4">
                    <div class="col-md-8">
                        <div class="diagram-header-title">
                            <i class="fas fa-project-diagram mr-2 text-warning"></i>Event database and member conversion
                        </div>
                        <div class="diagram-header-subtitle">
                            DMC records all participation while controlling when an event contact becomes a member.
                        </div>
                    </div>
                    <div class="col-md-4 text-md-right mt-3 mt-md-0">
                        <span class="badge badge-dark px-3 py-2" style="font-size: 12px; letter-spacing: 0.5px;">
                            <i class="fas fa-filter mr-1 text-warning"></i> Periode: {{ $selectedYear === 'all' ? 'Semua Tahun' : 'Tahun ' . $selectedYear }}
                        </span>
                    </div>
                </div>

                <!-- Three Column Visual Funnel -->
                <div class="row align-items-stretch">
                    <!-- Column 1: Event Sources (Left) -->
                    <div class="col-lg-3 col-md-4 mb-3 mb-md-0 d-flex flex-column justify-content-between" style="gap: 16px;">
                        <!-- DMC owned events card -->
                        <div class="diagram-card card-owned-events">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="card-type-title">DMC owned events</div>
                                </div>
                                <span class="badge badge-warning font-weight-bold">{{ $funnelOwned['events'] }} Events</span>
                            </div>
                            <div class="card-type-meta">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Entries (Check-in):</span>
                                    <strong>{{ number_format($funnelOwned['attendance']) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Converted Members:</span>
                                    <strong class="text-warning">{{ number_format($funnelOwned['converted']) }} ({{ $funnelOwned['rate'] }}%)</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Supporting events card -->
                        <div class="diagram-card card-supporting-events">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="card-type-title">Supporting events</div>
                                </div>
                                <span class="badge badge-info font-weight-bold">{{ $funnelSupporting['events'] }} Events</span>
                            </div>
                            <div class="card-type-meta">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Booth Visitors:</span>
                                    <strong>{{ number_format($funnelSupporting['attendance']) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Converted Members:</span>
                                    <strong class="text-info">{{ number_format($funnelSupporting['converted']) }} ({{ $funnelSupporting['rate'] }}%)</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: Event Participation (Center Funnel) -->
                    <div class="col-lg-4 col-md-4 mb-3 mb-md-0">
                        <div class="card-participation">
                            <div class="participation-title">
                                <i class="fas fa-tasks mr-2 text-warning"></i>Event Participation
                            </div>
                            <ul class="participation-stage-list">
                                <li class="participation-stage-item">
                                    <span><i class="fas fa-user-plus text-primary mr-2"></i>Registration</span>
                                    <span class="stage-badge bg-primary text-white">{{ number_format($eventParticipation['registration']) }}</span>
                                </li>
                                <li class="participation-stage-item">
                                    <span><i class="fas fa-check-circle text-info mr-2"></i>Confirmation</span>
                                    <span class="stage-badge bg-info text-white">{{ number_format($eventParticipation['confirmation']) }}</span>
                                </li>
                                <li class="participation-stage-item">
                                    <span><i class="fas fa-id-badge text-success mr-2"></i>Check in &amp; attendance</span>
                                    <span class="stage-badge bg-success text-white">{{ number_format($eventParticipation['attendance']) }}</span>
                                </li>
                                <li class="participation-stage-item">
                                    <span><i class="fas fa-star text-warning mr-2"></i>Follow up interest</span>
                                    <span class="stage-badge bg-warning text-dark font-weight-bold">{{ number_format($eventParticipation['interest']) }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Column 3: Outcomes (Right) -->
                    <div class="col-lg-5 col-md-4 d-flex flex-column justify-content-between">
                        <!-- Annual attendance Box -->
                        <div class="card-attendance-outcome">
                            <div class="d-flex justify-content-between align-items-center outcome-title mb-2">
                                <span><i class="fas fa-calendar-check mr-2 text-info"></i>Annual attendance</span>
                                <small class="text-muted" style="color: #94a3b8 !important;">Total Aktivitas</small>
                            </div>
                            <div class="outcome-metrics-grid">
                                <div class="outcome-metric-box">
                                    <div class="outcome-metric-value text-info">{{ number_format($annualAttendance['entries']) }}</div>
                                    <div class="outcome-metric-label">Entries</div>
                                </div>
                                <div class="outcome-metric-box">
                                    <div class="outcome-metric-value text-white">{{ number_format($annualAttendance['unique_people']) }}</div>
                                    <div class="outcome-metric-label">Unique people</div>
                                </div>
                                <div class="outcome-metric-box">
                                    <div class="outcome-metric-value text-success">{{ number_format($annualAttendance['verified_members']) }}</div>
                                    <div class="outcome-metric-label">Verified members</div>
                                </div>
                            </div>
                        </div>

                        <!-- Member conversion Box -->
                        <div class="card-conversion-outcome">
                            <div class="d-flex justify-content-between align-items-center outcome-title mb-2">
                                <span><i class="fas fa-user-check mr-2"></i>Member conversion</span>
                                <span class="badge badge-light font-weight-bold text-dark">{{ $annualConversion['rate'] }}% Rate</span>
                            </div>
                            <div class="outcome-metrics-grid">
                                <div class="outcome-metric-box">
                                    <div class="outcome-metric-value text-warning">{{ number_format($annualConversion['interest']) }}</div>
                                    <div class="outcome-metric-label">Stated Interest</div>
                                </div>
                                <div class="outcome-metric-box">
                                    <div class="outcome-metric-value text-white">{{ number_format($annualConversion['pending']) }}</div>
                                    <div class="outcome-metric-label">Pending Review</div>
                                </div>
                                <div class="outcome-metric-box">
                                    <div class="outcome-metric-value" style="color: #ffffff;">{{ number_format($annualConversion['active']) }}</div>
                                    <div class="outcome-metric-label">Active Member</div>
                                </div>
                            </div>
                            <div class="conversion-explainer">
                                <i class="fas fa-check mr-1"></i> Consent plus direct DMC engagement or stated interest.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Internal Operating Guideline Rule Banner -->
                <div class="guideline-banner">
                    <div class="guideline-quote">
                        <i class="fas fa-shield-alt text-warning mr-2"></i>
                        Owned events may create a pending member when consent and minimum data are present. Supporting events convert only after opt in or direct engagement.
                    </div>
                    <div class="guideline-sub">
                        DJAKARTA MINING CLUB &bull; INTERNAL OPERATING GUIDELINE &bull; {{ $selectedYear === 'all' ? '2026' : $selectedYear }}
                    </div>
                </div>
            </div>
