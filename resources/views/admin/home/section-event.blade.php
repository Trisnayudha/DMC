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
