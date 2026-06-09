<div class="row">
    <!-- Top 5 Sponsor Representative Attend -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h4>Top 5 Sponsor Representative Attend</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Company</th>
                                <th>Count Attend</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topSponsors as $sponsor)
                                <tr>
                                    <td>{{ $sponsor->company }}</td>
                                    <td>{{ $sponsor->count_attend }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-right mt-3">
                    <a href="{{ url('/admin/sponsors-representative-count') }}" class="btn btn-primary">Show More</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Sponsor Engagement Count -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h4>Sponsor Engagement Count</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="engagementCountTable">
                        <thead>
                            <tr>
                                <th>Sponsor</th>
                                <th>Engagement Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allSponsors as $sponsor)
                                <tr>
                                    <td>{{ $sponsor->name }}</td>
                                    <td>{{ $engagementCount->has($sponsor->id) ? $engagementCount[$sponsor->id] : 0 }}</td>
                                </tr>
                            @endforeach
                            @if ($allSponsors->isEmpty())
                                <tr>
                                    <td colspan="2" class="text-center">No sponsor data found for the selected filters.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="text-right mt-3">
                    <a href="{{ url('/admin/sponsor-engagement') }}" class="btn btn-primary">Show More</a>
                </div>
            </div>
        </div>
    </div>
</div>
