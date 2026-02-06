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
        @include('admin.home.section-kpi')

        @include('admin.home.section-membership')

        @include('admin.home.section-event')

        @include('admin.home.section-news')

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
