@extends('layouts.inspire.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Dashboard</h1>
            <!-- Breadcrumb -->
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
            </div>

        </div>
        <div class="row">

            {{-- 1. New Members --}}
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="far fa-user-plus"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>New Members</h4>
                        </div>
                        <div class="card-body">
                            24
                            <div class="text-small text-success">+12% this month</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Active Members --}}
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Active Members</h4>
                        </div>
                        <div class="card-body">
                            312
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Expiring Membership --}}
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-hourglass-end"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Expiring (30 Days)</h4>
                        </div>
                        <div class="card-body">
                            18
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. Total Events --}}
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-info">
                        <i class="far fa-calendar-alt"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total Events</h4>
                        </div>
                        <div class="card-body">
                            12
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="row">

            {{-- 5. Upcoming Events --}}
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Upcoming Events</h4>
                        </div>
                        <div class="card-body">
                            4
                        </div>
                    </div>
                </div>
            </div>

            {{-- 6. Event Registrations --}}
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-danger">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Event Registrations</h4>
                        </div>
                        <div class="card-body">
                            486
                        </div>
                    </div>
                </div>
            </div>

            {{-- 7. Published News --}}
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success">
                        <i class="far fa-newspaper"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Published News</h4>
                        </div>
                        <div class="card-body">
                            78
                        </div>
                    </div>
                </div>
            </div>

            {{-- 8. News Views --}}
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-dark">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>News Views</h4>
                        </div>
                        <div class="card-body">
                            12,430
                            <div class="text-small text-muted">This month</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-lg-8 col-md-12 col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Membership Growth</h4>
                        <div class="card-header-action">
                            <span class="badge badge-primary">Last 6 Months</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="membershipGrowthChart" height="150"></canvas>
                    </div>
                </div>
            </div>

            {{-- Summary kanan --}}
            <div class="col-lg-4 col-md-12 col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Quick Insights</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <strong>Best Month</strong><br>
                                November (84 New Members)
                            </li>
                            <li class="mb-3">
                                <strong>Lowest Month</strong><br>
                                August (21 New Members)
                            </li>
                            <li class="mb-3">
                                <strong>Avg / Month</strong><br>
                                52 Members
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 col-md-12 col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Event Registration Trend</h4>
                        <div class="card-header-action">
                            <span class="badge badge-info">Last 6 Months</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="eventRegistrationChart" height="150"></canvas>
                    </div>
                </div>
            </div>

            {{-- Event Status --}}
            <div class="col-lg-4 col-md-12 col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Event Status</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="eventStatusChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Top Events by Attendance</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Date</th>
                                        <th>Attendees</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Indonesia Miner Conference 2025</td>
                                        <td>12 Nov 2025</td>
                                        <td>420</td>
                                        <td><span class="badge badge-success">Completed</span></td>
                                    </tr>
                                    <tr>
                                        <td>Mining Digital Transformation</td>
                                        <td>18 Jan 2026</td>
                                        <td>310</td>
                                        <td><span class="badge badge-success">Completed</span></td>
                                    </tr>
                                    <tr>
                                        <td>Energy Transition Forum</td>
                                        <td>22 Mar 2026</td>
                                        <td>180</td>
                                        <td><span class="badge badge-warning">Upcoming</span></td>
                                    </tr>
                                    <tr>
                                        <td>Mining Safety Workshop</td>
                                        <td>5 Apr 2026</td>
                                        <td>95</td>
                                        <td><span class="badge badge-info">Upcoming</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 col-md-12 col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>News Views Trend</h4>
                        <div class="card-header-action">
                            <span class="badge badge-primary">Last 7 Days</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="newsViewsChart" height="150"></canvas>
                    </div>
                </div>
            </div>

            {{-- News Status --}}
            <div class="col-lg-4 col-md-12 col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>News Status</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="newsStatusChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Top Viewed News</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Views</th>
                                        <th>Status</th>
                                        <th>Published</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Indonesia Mining Outlook 2026</td>
                                        <td>Industry</td>
                                        <td>4,320</td>
                                        <td><span class="badge badge-success">Published</span></td>
                                        <td>2 days ago</td>
                                    </tr>
                                    <tr>
                                        <td>Nickel & EV Supply Chain Update</td>
                                        <td>Commodity</td>
                                        <td>3,180</td>
                                        <td><span class="badge badge-success">Published</span></td>
                                        <td>5 days ago</td>
                                    </tr>
                                    <tr>
                                        <td>Mining Safety Regulation 2026</td>
                                        <td>Regulation</td>
                                        <td>2,450</td>
                                        <td><span class="badge badge-success">Published</span></td>
                                        <td>1 week ago</td>
                                    </tr>
                                    <tr>
                                        <td>Upcoming Mining Events Q2</td>
                                        <td>Event</td>
                                        <td>1,120</td>
                                        <td><span class="badge badge-warning">Draft</span></td>
                                        <td>-</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 col-md-12 col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Member Activity Status</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="membershipStatusChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-12 col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Member Engagement Insights</h4>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <h5>64%</h5>
                                <p class="text-muted mb-0">Active Members</p>
                            </div>
                            <div class="col-4">
                                <h5>38%</h5>
                                <p class="text-muted mb-0">Joined Event</p>
                            </div>
                            <div class="col-4">
                                <h5>5.6</h5>
                                <p class="text-muted mb-0">Avg News / Member</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Inactive Members (30+ Days)</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Company</th>
                                        <th>Last Activity</th>
                                        <th>Joined At</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Ahmad Fauzi</td>
                                        <td>PT Mineral Energi Indonesia</td>
                                        <td>45 days ago</td>
                                        <td>2024</td>
                                        <td><span class="badge badge-warning">Inactive</span></td>
                                    </tr>
                                    <tr>
                                        <td>Siti Rahmawati</td>
                                        <td>PT Tambang Sejahtera</td>
                                        <td>62 days ago</td>
                                        <td>2023</td>
                                        <td><span class="badge badge-danger">Dormant</span></td>
                                    </tr>
                                    <tr>
                                        <td>Budi Santoso</td>
                                        <td>PT Mining Global</td>
                                        <td>90 days ago</td>
                                        <td>2022</td>
                                        <td><span class="badge badge-danger">Dormant</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Invoices</h4>
                        <div class="card-header-action">
                            <a href="{{ url('admin/invoice') }}" class="btn btn-danger">View More <i
                                    class="fas fa-chevron-right"></i></a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive table-invoice">
                            <table class="table table-striped">
                                <tr>
                                    <th>Invoice ID</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                    <th>Due Date</th>
                                    <th>Action</th>
                                </tr>
                                <tr>
                                    <td><a href="#">INV-87239</a></td>
                                    <td class="font-weight-600">Kusnadi</td>
                                    <td>
                                        <div class="badge badge-warning">Unpaid</div>
                                    </td>
                                    <td>July 19, 2018</td>
                                    <td>
                                        <a href="#" class="btn btn-primary">Detail</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><a href="#">INV-48574</a></td>
                                    <td class="font-weight-600">Hasan Basri</td>
                                    <td>
                                        <div class="badge badge-success">Paid</div>
                                    </td>
                                    <td>July 21, 2018</td>
                                    <td>
                                        <a href="#" class="btn btn-primary">Detail</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><a href="#">INV-76824</a></td>
                                    <td class="font-weight-600">Muhamad Nuruzzaki</td>
                                    <td>
                                        <div class="badge badge-warning">Unpaid</div>
                                    </td>
                                    <td>July 22, 2018</td>
                                    <td>
                                        <a href="#" class="btn btn-primary">Detail</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><a href="#">INV-84990</a></td>
                                    <td class="font-weight-600">Agung Ardiansyah</td>
                                    <td>
                                        <div class="badge badge-warning">Unpaid</div>
                                    </td>
                                    <td>July 22, 2018</td>
                                    <td>
                                        <a href="#" class="btn btn-primary">Detail</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><a href="#">INV-87320</a></td>
                                    <td class="font-weight-600">Ardian Rahardiansyah</td>
                                    <td>
                                        <div class="badge badge-success">Paid</div>
                                    </td>
                                    <td>July 28, 2018</td>
                                    <td>
                                        <a href="#" class="btn btn-primary">Detail</a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12 col-12 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4>New Membership</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled list-unstyled-border">
                            <li class="media">
                                <img class="mr-3 rounded-circle" width="50"
                                    src="{{ asset('stisla/assets/img/avatar/avatar-1.png') }}" alt="avatar">
                                <div class="media-body">
                                    <div class="float-right text-primary">Now</div>
                                    <div class="media-title">Farhan A Mujib</div>
                                    <span class="text-small text-muted">IT OFFICER - PT MEDIA MITRA KARYA INDONESIA</span>
                                </div>
                            </li>
                        </ul>
                        <div class="text-center pt-1 pb-1">
                            <a href="{{ url('admin/member') }}" class="btn btn-primary btn-lg btn-round">
                                View All
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>
@endsection
@push('bottom')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('membershipGrowthChart').getContext('2d');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan'],
                datasets: [{
                    label: 'New Members',
                    data: [21, 34, 56, 84, 62, 48],
                    borderWidth: 3,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>

    <script>
        /* Event Registration Trend */
        new Chart(document.getElementById('eventRegistrationChart'), {
            type: 'line',
            data: {
                labels: ['Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan'],
                datasets: [{
                    label: 'Registrations',
                    data: [120, 180, 260, 420, 310, 220],
                    borderWidth: 3,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        /* Event Status Donut */
        new Chart(document.getElementById('eventStatusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Upcoming', 'Ongoing', 'Completed'],
                datasets: [{
                    data: [4, 1, 7]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>

    <script>
        /* News Views Trend */
        new Chart(document.getElementById('newsViewsChart'), {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Views',
                    data: [820, 960, 1230, 1780, 2100, 1950, 2240],
                    borderWidth: 3,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        /* News Status Donut */
        new Chart(document.getElementById('newsStatusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Published', 'Draft', 'Archived'],
                datasets: [{
                    data: [78, 12, 5]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>

    <script>
        new Chart(document.getElementById('membershipStatusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Inactive'],
                datasets: [{
                    data: [312, 94]
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
@endpush
