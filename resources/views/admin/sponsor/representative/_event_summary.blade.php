@if ($eventSummary !== null)
    @php
        $selectedEvent = $eventFilterOptions->firstWhere('id', $filterEvent);
        $summaryCards = [
            'registered' => ['Registered', 'At least one representative registered', 'user-check', 'indigo'],
            'not_registered' => ['Not Registered', 'No representatives registered', 'user-plus', 'amber'],
            'attended' => ['Attended', 'At least one representative checked in', 'check-circle', 'green'],
            'not_attended' => ['Not Attended', 'No check-in, including unregistered sponsors', 'clock', 'slate'],
        ];
        // Total orang (representative) di samping total sponsor: Registered = semua yang
        // terdaftar, Attended = yang sudah check-in.
        $peopleCounts = [
            'registered' => collect($eventSummary['registered'])->sum(function ($entry) { return $entry['people']->count(); }),
            'attended'   => collect($eventSummary['attended'])->sum(function ($entry) { return $entry['present']->count(); }),
        ];
    @endphp
    <style>
        .event-summary-heading { display:flex; align-items:baseline; justify-content:space-between; gap:8px; flex-wrap:wrap; margin:4px 0 12px; }
        .event-summary-heading h2 { margin:0; font-size:15px; font-weight:700; color:#34395e; }
        .event-summary-heading span { font-size:12px; color:#68778b; }
        .event-summary-card { display:flex; flex-direction:column; height:100%; background:#fff; border:1px solid #e8ecf3; border-radius:8px; padding:17px 18px 14px; box-shadow:0 4px 16px rgba(30,43,74,.04); }
        .event-summary-top { display:flex; align-items:center; justify-content:space-between; gap:8px; color:#566276; font-size:13px; font-weight:600; }
        .event-summary-icon { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:9px; flex-shrink:0; }
        .event-summary-icon.indigo { background:#eef0ff; color:#5c6ce0; } .event-summary-icon.amber { background:#fff4dd; color:#a86a06; }
        .event-summary-icon.green { background:#e5f6ee; color:#188a58; } .event-summary-icon.slate { background:#edf1f5; color:#5e7084; }
        .event-summary-count { margin-top:8px; color:#25314e; font-size:28px; font-weight:700; line-height:1.1; }
        .event-summary-count small { font-size:12px; font-weight:400; color:#748195; }
        .event-summary-count .event-summary-people strong { font-size:16px; font-weight:700; color:#25314e; }
        .event-summary-caption { min-height:32px; margin:6px 0 8px; font-size:11px; line-height:1.4; color:#718096; }
        .event-summary-link { padding:0; border:0; background:none; text-align:left; font-size:12px; font-weight:600; color:#5869da; cursor:pointer; }
        .event-summary-link:hover, .event-summary-link:focus { color:#394abe; text-decoration:underline; }
        .event-summary-list { max-height:55vh; overflow-y:auto; }
        .event-summary-entry { padding:11px 0; border-bottom:1px solid #edf0f5; }
        .event-summary-entry:last-child { border-bottom:0; }
        .event-summary-person { display:flex; align-items:baseline; justify-content:space-between; gap:12px; margin-top:5px; color:#66758a; font-size:12px; }
        .event-summary-person span:last-child { white-space:nowrap; font-size:11px; }
    </style>
    <div class="event-summary-heading">
        <h2>Event Sponsor Overview</h2>
        <span>{{ $selectedEvent ? $selectedEvent->name : 'Selected event' }} &middot; Active sponsors</span>
    </div>
    <div class="row mb-3">
        @foreach ($summaryCards as $key => $card)
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="event-summary-card">
                    <div class="event-summary-top"><span>{{ $card[0] }}</span><span class="event-summary-icon {{ $card[3] }}" aria-hidden="true"><i class="fas fa-{{ $card[2] }}"></i></span></div>
                    <div class="event-summary-count">{{ count($eventSummary[$key]) }} <small>sponsors</small>
                        @isset($peopleCounts[$key])
                            <small class="event-summary-people">&middot; <strong>{{ $peopleCounts[$key] }}</strong> {{ $peopleCounts[$key] === 1 ? 'person' : 'people' }}</small>
                        @endisset
                    </div>
                    <div class="event-summary-caption">{{ $card[1] }}</div>
                    <button type="button" class="event-summary-link" data-toggle="modal" data-target="#event-summary-{{ $key }}">View sponsors <i class="fas fa-arrow-right ml-1" aria-hidden="true"></i></button>
                </div>
            </div>
        @endforeach
    </div>

    @foreach ($summaryCards as $key => $card)
        <div class="modal fade" id="event-summary-{{ $key }}" tabindex="-1" role="dialog" aria-labelledby="event-summary-title-{{ $key }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <div><h5 class="modal-title" id="event-summary-title-{{ $key }}">{{ $card[0] }} Sponsors <span class="badge badge-light">{{ count($eventSummary[$key]) }}</span>@isset($peopleCounts[$key]) <span class="badge badge-light">{{ $peopleCounts[$key] }} {{ $peopleCounts[$key] === 1 ? 'person' : 'people' }}</span>@endisset</h5>
                            <div class="small text-muted mt-1">{{ $card[1] }}{{ $selectedEvent ? ' · ' . $selectedEvent->name : '' }}</div></div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body event-summary-list">
                        @forelse ($eventSummary[$key] as $entry)
                            <div class="event-summary-entry">
                                <div class="font-weight-bold text-dark">{{ $entry['name'] }}</div>
                                @if (in_array($key, ['registered', 'attended', 'not_attended']))
                                    @foreach (($key === 'attended' ? $entry['present'] : $entry['people']) as $person)
                                        <div class="event-summary-person"><span>{{ $person->representative_name }}</span><span>{{ $person->present ? 'Checked in' : 'Registered' }}</span></div>
                                    @endforeach
                                @endif
                            </div>
                        @empty
                            <p class="text-center text-muted py-3 mb-0">No sponsors in this category.</p>
                        @endforelse
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Close</button></div>
                </div>
            </div>
        </div>
    @endforeach
@endif
