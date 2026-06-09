<!-- Recent Sponsor Inquiries -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0"><i class="fas fa-envelope-open-text text-primary mr-2"></i>Recent Sponsor Inquiries</h4>
                    <p class="text-muted mb-0" style="font-size:12px;">Members who reached out to sponsor representatives</p>
                </div>
                <span class="badge badge-primary" style="font-size:13px;">{{ $recentInquiries->count() }} records</span>
            </div>
            <div class="card-body p-0">
                @if ($recentInquiries->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i><br>No inquiries yet.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size:13px;">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:130px;">Date</th>
                                    <th>Member</th>
                                    <th>Company</th>
                                    <th>Representative</th>
                                    <th>Sponsor</th>
                                    <th>Message</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentInquiries as $inq)
                                    <tr>
                                        <td class="text-muted" style="white-space:nowrap;">
                                            {{ \Carbon\Carbon::parse($inq->created_at)->format('d M Y') }}<br>
                                            <small>{{ \Carbon\Carbon::parse($inq->created_at)->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            <div style="font-weight:500;">{{ $inq->user_name ?? '-' }}</div>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $inq->company_name ?? '-' }}</span>
                                        </td>
                                        <td>
                                            <div style="font-weight:500;">{{ $inq->rep_name }}</div>
                                            @if($inq->rep_title)
                                                <small class="text-muted">{{ $inq->rep_title }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-light border">{{ $inq->sponsor_name }}</span>
                                        </td>
                                        <td style="max-width:220px;">
                                            <span title="{{ strip_tags($inq->message) }}" style="cursor:default;">
                                                {{ \Illuminate\Support\Str::limit(strip_tags($inq->message), 60) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
