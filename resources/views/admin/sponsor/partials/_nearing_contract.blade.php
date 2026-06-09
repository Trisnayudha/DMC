<div class="col-lg-12">
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-0">
                    <i class="fas fa-hourglass-half text-warning mr-2"></i>
                    Nearing Contract End
                    <span class="badge badge-warning ml-1">{{ $nearEndSponsors->count() }}</span>
                </h4>
                <small class="text-muted">Sorted by most urgent — contracts ending within 3 months</small>
            </div>
        </div>
        <div class="card-body p-0">
            @if($nearEndSponsors->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="fas fa-check-circle fa-2x text-success mb-2 d-block"></i>
                    No contracts ending soon.
                </div>
            @else
            <table class="table table-hover mb-0" style="font-size:13px">
                <thead class="thead-light">
                    <tr>
                        <th style="width:8px; padding:0"></th>
                        <th>Sponsor</th>
                        <th style="width:95px">Package</th>
                        <th style="width:120px">Contract End</th>
                        <th style="width:180px">Time Left</th>
                        <th style="width:110px; text-align:center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($nearEndSponsors as $sponsor)
                        @php
                            $endDate  = \Carbon\Carbon::createFromFormat('Y-m', $sponsor->contract_end)->endOfMonth();
                            $daysLeft = (int) now()->diffInDays($endDate, false);
                            if ($daysLeft <= 30) {
                                $rowColor    = '#dc3545';
                                $badgeColor  = 'danger';
                                $urgencyText = 'Urgent';
                            } elseif ($daysLeft <= 60) {
                                $rowColor    = '#ffc107';
                                $badgeColor  = 'warning';
                                $urgencyText = 'Moderate';
                            } else {
                                $rowColor    = '#17a2b8';
                                $badgeColor  = 'info';
                                $urgencyText = 'Upcoming';
                            }
                            $endFormatted = \Carbon\Carbon::createFromFormat('Y-m', $sponsor->contract_end)->format('M Y');
                        @endphp
                        <tr>
                            <td style="padding:0; background:{{ $rowColor }}; width:4px"></td>
                            <td class="font-weight-bold">{{ $sponsor->name }}</td>
                            <td>
                                <span class="badge
                                    @if($sponsor->package === 'platinum') badge-primary
                                    @elseif($sponsor->package === 'gold') badge-warning
                                    @else badge-secondary @endif">
                                    {{ ucfirst($sponsor->package) }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $endFormatted }}</td>
                            <td>
                                <div class="d-flex align-items-center" style="gap:8px">
                                    <span class="badge badge-{{ $badgeColor }}">{{ $urgencyText }}</span>
                                    <span class="text-muted" style="font-size:12px">{{ $daysLeft }} days left</span>
                                </div>
                                <div class="progress mt-1" style="height:4px; border-radius:2px">
                                    @php $pct = max(0, min(100, round((90 - $daysLeft) / 90 * 100))); @endphp
                                    <div class="progress-bar bg-{{ $badgeColor }}" style="width:{{ $pct }}%"></div>
                                </div>
                            </td>
                            <td style="text-align:center">
                                <a href="#" class="btn btn-sm btn-primary update-contract-btn"
                                   data-sponsor-id="{{ $sponsor->id }}"
                                   data-contract-start="{{ $sponsor->contract_start }}"
                                   data-contract-end="{{ $sponsor->contract_end }}"
                                   data-package="{{ $sponsor->package }}"
                                   title="Renew / Update Contract">
                                    <i class="fas fa-sync-alt"></i> Renew
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
        <div class="card-footer text-right py-2">
            <a href="{{ route('sponsors.nearing-contract') }}" class="btn btn-outline-info btn-sm">
                <i class="fas fa-hourglass-half"></i> View All Nearing Contract
            </a>
        </div>
    </div>
</div>
