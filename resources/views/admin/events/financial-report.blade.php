@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Financial Report</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('events-details', ['slug' => $slug]) }}">Event Detail</a>
                    </div>
                    <div class="breadcrumb-item active">Financial Report</div>
                </div>
            </div>

            <div class="section-body">
                <h2 class="section-title">{{ $event->name }}</h2>

                {{-- FILTER BAR --}}
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('events-financial-report', ['slug' => $slug]) }}">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>Start Date</label>
                                    <input type="date" name="start_date" class="form-control"
                                        value="{{ $filters['start_date'] ?? '' }}">
                                </div>
                                <div class="col-md-3">
                                    <label>End Date</label>
                                    <input type="date" name="end_date" class="form-control"
                                        value="{{ $filters['end_date'] ?? '' }}">
                                </div>
                                <div class="col-md-3">
                                    <label>Payment Method</label>
                                    <select name="payment_method" class="form-control">
                                        <option value="">All</option>
                                        @foreach ($methods as $m)
                                            <option value="{{ $m }}"
                                                {{ ($filters['payment_method'] ?? '') == $m ? 'selected' : '' }}>
                                                {{ $m }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Search</label>
                                    <input type="text" name="keyword" class="form-control"
                                        placeholder="name, email, company, code..." value="{{ $filters['keyword'] ?? '' }}">
                                </div>
                            </div>

                            <div class="mt-3 d-flex justify-content-between">
                                <div>
                                    <button class="btn btn-primary"><i class="fa fa-filter"></i> Apply Filter</button>
                                    <a href="{{ route('events-financial-report', ['slug' => $slug]) }}"
                                        class="btn btn-outline-primary">Clear</a>
                                </div>

                                <div>
                                    <a class="btn btn-success"
                                        href="{{ route('events-financial-report-excel', array_merge(['slug' => $slug], request()->query())) }}">
                                        <i class="fa fa-file-excel"></i> Export Excel
                                    </a>
                                    <a class="btn btn-danger"
                                        href="{{ route('events-financial-report-pdf', array_merge(['slug' => $slug], request()->query())) }}">
                                        <i class="fa fa-file-pdf"></i> Download PDF
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- KPI CARDS --}}
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary"><i class="far fa-list-alt"></i></div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Paid Transactions</h4>
                                </div>
                                <div class="card-body">{{ $kpi->paid_trx ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info"><i class="fas fa-receipt"></i></div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Gross Revenue</h4>
                                </div>
                                <div class="card-body">{{ number_format($kpi->gross_total ?? 0, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning"><i class="fas fa-tags"></i></div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Discount</h4>
                                </div>
                                <div class="card-body">{{ number_format($kpi->discount_total ?? 0, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success"><i class="fas fa-coins"></i></div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Net Revenue</h4>
                                </div>
                                <div class="card-body">{{ number_format($kpi->net_total ?? 0, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CHARTS --}}
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Net Revenue by Day</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="chartDaily" height="140"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Net Revenue by Payment Method</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="chartMethod" height="140"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Top Tickets by Net Revenue</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="chartTicket" height="110"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TABLE --}}
                <div class="card">
                    <div class="card-header">
                        <h4>Paid Transactions Table</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="laravel_crud" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Paid At</th>
                                        <th>Code</th>
                                        <th>Buyer</th>
                                        <th>Company</th>
                                        <th>Ticket</th>
                                        <th>Method</th>
                                        <th class="text-right">Gross</th>
                                        <th class="text-right">Discount</th>
                                        <th class="text-right">Net</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rows as $r)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ date('d M Y H:i', strtotime($r->paid_at)) }}</td>
                                            <td>{{ $r->code_payment }}</td>
                                            <td>
                                                <b>{{ $r->name }}</b><br>
                                                <small>{{ $r->email }}</small>
                                            </td>
                                            <td>{{ $r->company_name }}</td>
                                            <td>{{ $r->ticket_title }}</td>
                                            <td><span class="badge badge-light">{{ $r->payment_method }}</span></td>
                                            <td class="text-right">{{ number_format($r->gross_amount ?? 0, 0, ',', '.') }}
                                            </td>
                                            <td class="text-right">{{ number_format($r->discount ?? 0, 0, ',', '.') }}
                                            </td>
                                            <td class="text-right">
                                                <b>{{ number_format($r->net_amount ?? 0, 0, ',', '.') }}</b>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

            </div>
        </section>
    </div>
@endsection

@push('bottom')
    <script>
        $(document).ready(function() {
            $('#laravel_crud').DataTable({
                dom: 'Bfrtip',
                buttons: ['copyHtml5', 'excelHtml5', 'csvHtml5', 'pdfHtml5']
            });
        });

        // ===============================
        // FORMAT RUPIAH
        // ===============================
        function formatRupiah(value) {
            return 'Rp ' + Number(value).toLocaleString('id-ID');
        }

        // ===============================
        // CHART: DAILY NET REVENUE
        // ===============================
        const daily = {!! json_encode($chartDaily) !!};
        const dailyLabels = daily.map(x => x.trx_date);
        const dailyValues = daily.map(x => Number(x.net_total));

        const ctxDaily = document.getElementById('chartDaily').getContext('2d');
        const gradientBlue = ctxDaily.createLinearGradient(0, 0, 0, 400);
        gradientBlue.addColorStop(0, 'rgba(63, 81, 181, 0.4)');
        gradientBlue.addColorStop(1, 'rgba(63, 81, 181, 0)');

        new Chart(ctxDaily, {
            type: 'line',
            data: {
                labels: dailyLabels,
                datasets: [{
                    label: 'Net Revenue',
                    data: dailyValues,
                    backgroundColor: gradientBlue,
                    borderColor: '#3f51b5',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#3f51b5',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return formatRupiah(context.raw);
                            }
                        }
                    }
                }
            }
        });

        // ===============================
        // CHART: PAYMENT METHOD
        // ===============================
        const method = {!! json_encode($chartMethod) !!};

        new Chart(document.getElementById('chartMethod'), {
            type: 'doughnut',
            data: {
                labels: method.map(x => x.label),
                datasets: [{
                    data: method.map(x => Number(x.value)),
                    backgroundColor: [
                        '#4CAF50',
                        '#2196F3',
                        '#FF9800',
                        '#9C27B0',
                        '#F44336',
                        '#00BCD4',
                        '#795548',
                        '#607D8B'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + formatRupiah(context.raw);
                            }
                        }
                    }
                }
            }
        });

        // ===============================
        // CHART: TOP TICKETS
        // ===============================
        const ticket = {!! json_encode($chartTicket) !!};

        const ctxTicket = document.getElementById('chartTicket').getContext('2d');
        const gradientGreen = ctxTicket.createLinearGradient(0, 0, 0, 400);
        gradientGreen.addColorStop(0, 'rgba(76, 175, 80, 0.8)');
        gradientGreen.addColorStop(1, 'rgba(76, 175, 80, 0.2)');

        new Chart(ctxTicket, {
            type: 'bar',
            data: {
                labels: ticket.map(x => x.label),
                datasets: [{
                    label: 'Net Revenue',
                    data: ticket.map(x => Number(x.value)),
                    backgroundColor: gradientGreen,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return formatRupiah(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: function(value) {
                                return formatRupiah(value);
                            }
                        }
                    }
                }
            }
        });
    </script>
@endpush
