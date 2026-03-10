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

@push('top')
    <style>
        .clickable-card {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .clickable-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }
    </style>
@endpush
@push('bottom')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // ===== Membership Growth =====
        const mgLabels = @json($membershipGrowthLabels ?? []);
        const mgData = @json($membershipGrowthData ?? []);

        const ctx = document.getElementById('membershipGrowthChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: mgLabels,
                datasets: [{
                    label: 'New Members',
                    data: mgData,
                    borderWidth: 3,
                    tension: 0.35,
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

        // ===== Membership Status Donut =====
        const activeMembers = {{ (int) ($activeMembers ?? 0) }};
        const inactiveMembers = {{ (int) ($inactiveMembers ?? 0) }};

        new Chart(document.getElementById('membershipStatusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Inactive'],
                datasets: [{
                    data: [activeMembers, inactiveMembers]
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

    <script>
        // ===== Event Registration Trend =====
        const erLabels = @json($eventRegLabels ?? []);
        const erData = @json($eventRegData ?? []);

        new Chart(document.getElementById('eventRegistrationChart'), {
            type: 'line',
            data: {
                labels: erLabels,
                datasets: [{
                    label: 'Registrations',
                    data: erData,
                    borderWidth: 3,
                    tension: 0.35,
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

        // ===== Event Status Donut =====
        const evUpcoming = {{ (int) ($eventUpcoming ?? 0) }};
        const evOngoing = {{ (int) ($eventOngoing ?? 0) }};
        const evCompleted = {{ (int) ($eventCompleted ?? 0) }};

        new Chart(document.getElementById('eventStatusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Upcoming', 'Ongoing', 'Completed'],
                datasets: [{
                    data: [evUpcoming, evOngoing, evCompleted]
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
        // ===== News Views Trend (Last 7 Days) =====
        const nvLabels = @json($newsTrendLabels ?? []);
        const nvData = @json($newsTrendData ?? []);

        new Chart(document.getElementById('newsViewsChart'), {
            type: 'line',
            data: {
                labels: nvLabels,
                datasets: [{
                    label: 'Views',
                    data: nvData,
                    borderWidth: 3,
                    tension: 0.35,
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

        // ===== News Status Donut =====
        const newsPublished = {{ (int) ($newsPublished ?? 0) }};
        const newsDraft = {{ (int) ($newsDraft ?? 0) }};
        const newsArchived = {{ (int) ($newsArchived ?? 0) }};

        new Chart(document.getElementById('newsStatusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Published', 'Draft', 'Archived'],
                datasets: [{
                    data: [newsPublished, newsDraft, newsArchived]
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
@endpush
