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
                            <i class="fas fa-filter mr-1 text-warning"></i> Period: {{ $selectedYear === 'all' ? 'All Years' : 'Year ' . $selectedYear }}
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
                                    <span>Registrants:
                                        @include('admin.event_conversions.partials._stage_info', ['title' => 'Registrants', 'sources' => [
                                            ['DMC owned events', $funnelOwned['all_registrations'] ?? 0, 'Every registration in the event Table of Registration, in all statuses (Paid Off, Waiting, Reject, Cancel, Expired).'],
                                        ]])
                                    </span>
                                    <a href="#" class="cdm-open text-white font-weight-bold" title="View all registrants"
                                        data-url="{{ route('admin.event_conversions.registrants') }}" data-title="DMC owned events — registrants (all statuses)">
                                        {{ number_format($funnelOwned['all_registrations'] ?? 0) }} <i class="fas fa-external-link-alt fa-xs"></i>
                                    </a>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Approved registrants:
                                        @include('admin.event_conversions.partials._stage_info', ['title' => 'Approved registrants', 'sources' => [
                                            ['DMC owned events', $funnelOwned['registrations'], 'Registrations with status Paid Off: paid, sponsor and approved free tickets.'],
                                        ]])
                                    </span>
                                    <a href="#" class="cdm-open text-white font-weight-bold" title="View approved registrants"
                                        data-url="{{ route('admin.event_conversions.registrants', ['approved' => 1]) }}" data-title="DMC owned events — approved registrants (Paid Off)">
                                        {{ number_format($funnelOwned['registrations']) }} <i class="fas fa-external-link-alt fa-xs"></i>
                                    </a>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>DMC Check-in:
                                        @include('admin.event_conversions.partials._stage_info', ['title' => 'DMC Check-in', 'sources' => [
                                            ['DMC owned events', $funnelOwned['attendance'], 'People who actually attended: check-ins scanned at the event (Scan Present page).'],
                                        ]])
                                    </span>
                                    <a href="#" class="cdm-open text-white font-weight-bold" title="View who checked in"
                                        data-url="{{ route('admin.event_conversions.check_ins') }}" data-title="DMC owned events — check-ins">
                                        {{ number_format($funnelOwned['attendance']) }} <i class="fas fa-external-link-alt fa-xs"></i>
                                    </a>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Converted Members:</span>
                                    <a href="#" class="cdm-open text-warning font-weight-bold" data-type="owned"
                                        data-title="DMC owned events — converted members" title="View converted members">
                                        {{ number_format($funnelOwned['converted']) }} ({{ $funnelOwned['rate'] }}%) <i class="fas fa-external-link-alt fa-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Partnership events card -->
                        <div class="diagram-card card-supporting-events">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="card-type-title">Partnership events</div>
                                </div>
                                <span class="badge badge-info font-weight-bold"
                                    title="Events with booth visitor data / total partnership events">{{ $funnelSupporting['events_with_data'] ?? 0 }}/{{ $funnelSupporting['events'] }} Events</span>
                            </div>
                            <div class="card-type-meta">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Booth Visitors:</span>
                                    <a href="#" class="cdm-open text-white font-weight-bold" title="View booth visitors"
                                        data-url="{{ route('admin.event_conversions.booth_visitors') }}" data-title="Partnership events — booth visitors">
                                        {{ number_format($funnelSupporting['attendance']) }} <i class="fas fa-external-link-alt fa-xs"></i>
                                    </a>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Converted Members:</span>
                                    <a href="#" class="cdm-open text-info font-weight-bold" data-type="supporting"
                                        data-title="Partnership events — converted members" title="View converted members">
                                        {{ number_format($funnelSupporting['converted']) }} ({{ $funnelSupporting['rate'] }}%) <i class="fas fa-external-link-alt fa-xs"></i>
                                    </a>
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
                                    <span><i class="fas fa-user-plus text-primary mr-2"></i>Registration
                                        @include('admin.event_conversions.partials._stage_info', ['title' => 'Registration — sources', 'sources' => [
                                            ['DMC owned events', $funnelOwned['registrations'], 'Registrations in the event Table of Registration with status Paid Off, for every package: paid (member, non-member, Premium), sponsor and free tickets (a free invitation is set to Paid Off once approved). Waiting, Reject, Cancel and Expired are not counted.'],
                                            ['Partnership events', $funnelSupporting['registrations'], 'Booth visitor rows entered on the Partnership Event page.'],
                                        ]])
                                    </span>
                                    <span class="stage-badge bg-primary text-white">{{ number_format($eventParticipation['registration']) }}</span>
                                </li>
                                <li class="participation-stage-item">
                                    <span><i class="fas fa-check-circle text-info mr-2"></i>Confirmation
                                        @include('admin.event_conversions.partials._stage_info', ['title' => 'Confirmation — sources', 'sources' => [
                                            ['DMC owned events', $funnelOwned['confirmed'], 'Approved registrations (status Paid Off), including approved free tickets. Same as Registration for DMC owned events.'],
                                            ['Partnership events', $funnelSupporting['confirmed'], 'Booth visitors with an email or mobile number recorded.'],
                                        ]])
                                    </span>
                                    <span class="stage-badge bg-info text-white">{{ number_format($eventParticipation['confirmation']) }}</span>
                                </li>
                                <li class="participation-stage-item">
                                    <span><i class="fas fa-id-badge text-success mr-2"></i>Check in &amp; attendance
                                        @include('admin.event_conversions.partials._stage_info', ['title' => 'Check in & attendance — sources', 'sources' => [
                                            ['DMC owned events', $funnelOwned['attendance'], 'Check-ins scanned at the event (Scan Present page). An event with no scans uses its confirmed registrations instead.'],
                                            ['Partnership events', $funnelSupporting['attendance'], 'Every booth visitor row counts as one visit.'],
                                        ]])
                                    </span>
                                    <span class="stage-badge bg-success text-white">{{ number_format($eventParticipation['attendance']) }}</span>
                                </li>
                                <li class="participation-stage-item">
                                    <span><i class="fas fa-star text-warning mr-2"></i>Follow up interest
                                        @include('admin.event_conversions.partials._stage_info', ['title' => 'Follow up interest — sources', 'sources' => [
                                            ['DMC owned events', $funnelOwned['interest'], 'Registrations ticked as Potential Member on the event detail page.'],
                                            ['Partnership events', $funnelSupporting['interest'], 'Booth visitors already in the users table who showed sponsorship interest: ticked "receive information about corporate sponsorship opportunities" on their member profile, or a booth Remark mentions sponsorship. One count per person per event.'],
                                        ]])
                                    </span>
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
                                <small class="text-muted" style="color: #94a3b8 !important;">Total Activity</small>
                            </div>
                            <div class="outcome-metrics-grid">
                                <div class="outcome-metric-box">
                                    <div class="outcome-metric-value text-info">{{ number_format($annualAttendance['entries']) }}</div>
                                    <div class="outcome-metric-label">Entries
                                        @include('admin.event_conversions.partials._stage_info', ['title' => 'Entries — sources', 'placement' => 'top', 'sources' => [
                                            ['DMC owned events', $funnelOwned['attendance'], 'Check-ins scanned at the event (Scan Present page). An event with no scans uses its approved (Paid Off) registrations.'],
                                            ['Partnership events', $funnelSupporting['attendance'], 'Booth visitor rows.'],
                                            ['Note', null, 'Counts visits, not people: someone who attended 3 events counts 3 times.'],
                                        ]])
                                    </div>
                                </div>
                                <div class="outcome-metric-box">
                                    <div class="outcome-metric-value text-white">{{ number_format($annualAttendance['unique_people']) }}</div>
                                    <div class="outcome-metric-label">Unique people
                                        @include('admin.event_conversions.partials._stage_info', ['title' => 'Unique people — sources', 'placement' => 'top', 'sources' => [
                                            ['DMC owned events', $annualAttendance['unique_owned'] ?? 0, 'Different people (by email) who checked in.'],
                                            ['Partnership events', $annualAttendance['unique_partner'] ?? 0, 'Different booth visitor emails. Visitors with no email are not counted.'],
                                            ['Note', null, 'The total removes people who appear in both, so it is less than the sum.'],
                                        ]])
                                    </div>
                                </div>
                                <div class="outcome-metric-box">
                                    <div class="outcome-metric-value text-success">{{ number_format($annualAttendance['verified_members']) }}</div>
                                    <div class="outcome-metric-label">Verified members
                                        @include('admin.event_conversions.partials._stage_info', ['title' => 'Verified members — sources', 'placement' => 'top', 'sources' => [
                                            ['DMC owned events', $annualAttendance['verified_owned'] ?? 0, 'Attendees whose membership status is Active today.'],
                                            ['Partnership events', $annualAttendance['verified_partner'] ?? 0, 'Booth visitors whose email matches an Active member (not counting members registered via "Register as Member" at the booth).'],
                                            ['Note', null, 'Counted by email, once per person; the total removes people who appear in both.'],
                                        ]])
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Member conversion Box -->
                        <div class="card-conversion-outcome">
                            <div class="d-flex justify-content-between align-items-center outcome-title mb-2">
                                <span><i class="fas fa-user-check mr-2"></i>Member conversion</span>
                                <span class="badge badge-light font-weight-bold text-dark">{{ number_format((float) $annualConversion['rate'], 1) }}% Rate
                                    @include('admin.event_conversions.partials._stage_info', ['title' => 'Conversion rate', 'placement' => 'top', 'sources' => [
                                        ['Formula', null, 'Converted members (Pending Review + Active Member) ÷ Unique people in Annual attendance.'],
                                        ['This period', null, number_format($annualConversion['total_converted']) . ' ÷ ' . number_format($annualAttendance['unique_people']) . ' = ' . number_format((float) $annualConversion['rate'], 1) . '%'],
                                    ]])
                                </span>
                            </div>
                            <div class="outcome-metrics-grid">
                                <a href="#" class="outcome-metric-box cdm-open d-block text-decoration-none" title="View the people behind this number"
                                    data-url="{{ route('admin.event_conversions.interest') }}" data-title="Stated interest">
                                    <div class="outcome-metric-value text-warning">{{ number_format($annualConversion['interest']) }}</div>
                                    <div class="outcome-metric-label">Stated Interest <i class="fas fa-external-link-alt fa-xs"></i>
                                        @include('admin.event_conversions.partials._stage_info', ['title' => 'Stated interest — sources', 'placement' => 'top', 'iconClass' => 'text-white-50', 'sources' => [
                                            ['DMC owned events', $funnelOwned['interest'], 'Registrations ticked as Potential Member on the event detail page.'],
                                            ['Partnership events', $funnelSupporting['interest'], 'Booth visitors already in the users table who ticked "receive information about corporate sponsorship opportunities", or whose booth Remark mentions sponsorship.'],
                                        ]])
                                    </div>
                                </a>
                                <a href="#" class="outcome-metric-box cdm-open d-block text-decoration-none" title="View the people behind this number"
                                    data-type="all" data-status="pending" data-title="Pending review — converted members">
                                    <div class="outcome-metric-value text-white">{{ number_format($annualConversion['pending']) }}</div>
                                    <div class="outcome-metric-label">Pending Review <i class="fas fa-external-link-alt fa-xs"></i>
                                        @include('admin.event_conversions.partials._stage_info', ['title' => 'Pending review — sources', 'placement' => 'top', 'iconClass' => 'text-white-50', 'sources' => [
                                            ['DMC owned events', $funnelOwned['pending'], 'Non-member attendees who applied for membership after registering for the event (same year), still waiting for admin approval.'],
                                            ['Partnership events', $funnelSupporting['pending'], 'Booth visitors who applied for membership on or after the event start date (same year), still waiting for admin approval.'],
                                        ]])
                                    </div>
                                </a>
                                <a href="#" class="outcome-metric-box cdm-open d-block text-decoration-none" title="View the people behind this number"
                                    data-type="all" data-status="active" data-title="Active members — converted from events">
                                    <div class="outcome-metric-value" style="color: #ffffff;">{{ number_format($annualConversion['active']) }}</div>
                                    <div class="outcome-metric-label">Active Member <i class="fas fa-external-link-alt fa-xs"></i>
                                        @include('admin.event_conversions.partials._stage_info', ['title' => 'Active member — sources', 'placement' => 'top', 'iconClass' => 'text-white-50', 'sources' => [
                                            ['DMC owned events', $funnelOwned['active'], 'Non-member attendees who became Active members after registering for the event (same year). Members who joined through a partnership booth link (EP/…) are counted under Partnership instead.'],
                                            ['Partnership events', $funnelSupporting['active'], 'Booth visitors (matched by email) who became Active members on or after the event start date (same year) through the partnership link (EP/…) or "Register as Member" at the booth.'],
                                        ]])
                                    </div>
                                </a>
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

@include('admin.event_conversions.partials._converted_members_modal')
