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
@endpush
