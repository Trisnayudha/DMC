@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Event Detail Participant Management</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="">Event Detail Participant Management</a>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <h2 class="section-title">Event Detail Participant</h2>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Detail Registration</h4>
                                    <div class="card-header-action">
                                        <a data-collapse="#detail-registration" class="btn btn-icon btn-info"
                                            href="#"><i
                                                class="fas fa-{{ $date <= date('Y-m-d') ? 'minus' : 'plus' }}"></i></a>
                                    </div>
                                </div>
                                <div class="collapse {{ $date <= date('Y-m-d') ? ' show' : '' }}" id="detail-registration">
                                    <div class="card-body" style="background: #f8f9fa">
                                        <div class="row">
                                            <div class="col-lg-6 col-md-6 col-sm-6 col-12">
                                                <div class="card card-statistic-1">
                                                    <div class="card-icon bg-primary">
                                                        <i class="far fa-user"></i>
                                                    </div>
                                                    <div class="card-wrap">
                                                        <div class="card-header">
                                                            <h4>Total Check-in</h4>
                                                        </div>
                                                        <div class="card-body">
                                                            {{ $checkin }}

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-6 col-md-6 col-sm-6 col-12">
                                                <div class="card card-statistic-1">
                                                    <div class="card-icon bg-info">
                                                        <i class="far fa-user"></i>
                                                    </div>
                                                    <div class="card-wrap">
                                                        <div class="card-header">
                                                            <h4>Total Belum Hadir</h4>
                                                        </div>
                                                        <div class="card-body">
                                                            {{ $absent }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="card-header">
                                <h4>Event Detail Participant Management</h4>
                            </div>
                            <div class="card-body">
                                @if ($errors->any())
                                    <div class="alert alert-warning">
                                        <div class="alert-title">Whoops!</div>
                                        @lang('general.validation_error_message')
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if (session('success'))
                                    <div class="alert alert-success">{{ session('success') }}</div>
                                @endif

                                @if (session('error'))
                                    <div class="alert alert-danger">{{ session('error') }}</div>
                                @endif


                                <div class="table-responsive">
                                    <table id="laravel_crud" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="10px">No</th>
                                                <th>Date Register </th>
                                                <th>Code Access</th>
                                                <th>Package</th>
                                                <th>Nama</th>
                                                <th>Email</th>
                                                <th>Job Title</th>
                                                <th>Company Name</th>
                                                <th>Phone Number</th>
                                                <th>Company Address</th>
                                                @if ($list[0]['end_date'] <= date('Y-m-d'))
                                                    <th>Company Category</th>
                                                    <th>Country</th>
                                                @endif
                                                @if ($list[0]['end_date'] >= date('Y-m-d'))
                                                    <th>Confirmation Email</th>
                                                    <th>Confirmation Whatsapp</th>
                                                @endif
                                                <th>Date Present </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; ?>
                                            @foreach ($list as $post)
                                                <tr id="row_{{ $post->id }}">
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ date('d,F Y H:i', strtotime($post->payment_updated)) }}</td>
                                                    <td>{{ $post->code_payment }}</td>
                                                    <td>{{ $post->package }}</td>
                                                    <td>{{ $post->name }}</td>
                                                    <td>{{ $post->email }}</td>
                                                    <td>{{ $post->job_title }}</td>
                                                    <td>{{ $post->company_name . ($post->prefix ? ', ' . $post->prefix : '') }}
                                                    </td>
                                                    <td>{{ $post->phone }}</td>
                                                    <td>{{ $post->address }}</td>
                                                    @if ($post->end_date <= date('Y-m-d'))
                                                        <td>{{ $post->company_category != 'other' ? $post->company_category : $post->company_other }}
                                                        </td>
                                                        <td>{{ $post->country }}</td>
                                                    @endif
                                                    @if ($post->end_date >= date('Y-m-d'))
                                                        <td>
                                                            @if ($post->reminder == null)
                                                                <form action="{{ Route('events-send-participant') }}"
                                                                    method="post">
                                                                    @csrf
                                                                    <input type="hidden" name="users_id"
                                                                        value="{{ $post->users_id }}">
                                                                    <input type="hidden" name="events_id"
                                                                        value="{{ $post->events_id }}">
                                                                    <input type="hidden" name="payment_id"
                                                                        value="{{ $post->payment_id }}">
                                                                    <input type="hidden" name="method"
                                                                        value="confirmation">
                                                                    <button href="#" class="btn btn-primary send"
                                                                        title="Send Confirmation">
                                                                        <span class="fa fa-paper-plane"></span></button>
                                                                </form>
                                                            @else
                                                                {{ date('d,F H:i', strtotime($post->reminder)) . ' ' . $post->name_reminder }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($post->reminder_wa == null)
                                                                <form action="{{ Route('events-send-participant') }}"
                                                                    method="post">
                                                                    @csrf
                                                                    <input type="hidden" name="users_id"
                                                                        value="{{ $post->users_id }}">
                                                                    <input type="hidden" name="events_id"
                                                                        value="{{ $post->events_id }}">
                                                                    <input type="hidden" name="payment_id"
                                                                        value="{{ $post->payment_id }}">
                                                                    <input type="hidden" name="method"
                                                                        value="confirmation_wa">
                                                                    <button href="#" class="btn btn-primary send"
                                                                        title="Send Confirmation">
                                                                        <span class="fa fa-paper-plane"></span></button>
                                                                </form>
                                                            @else
                                                                {{ date('d,F H:i', strtotime($post->reminder_wa)) . ' ' . $post->name_reminder_wa }}
                                                            @endif
                                                        </td>
                                                    @endif
                                                    <td>
                                                        @if ($post->present == null)
                                                            <form action="{{ Route('events-send-participant') }}"
                                                                method="post">
                                                                @csrf
                                                                <input type="hidden" name="users_id"
                                                                    value="{{ $post->users_id }}">
                                                                <input type="hidden" name="events_id"
                                                                    value="{{ $post->events_id }}">
                                                                <input type="hidden" name="payment_id"
                                                                    value="{{ $post->payment_id }}">
                                                                <input type="hidden" name="method" value="present">
                                                                <button href="#" class="btn btn-primary present"
                                                                    title="Send Confirmation">
                                                                    <span class="fa fa-paper-plane"></span></button>
                                                            </form>
                                                        @else
                                                            {{ date('d, F H:i', strtotime($post->present)) . ' ' . $post->name_present }}
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <div id="loader" style="display:none">
        <div class="loader"></div>
    </div>
@endsection

@push('top')
    <style>
        #loader {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loader {
            border: 16px solid #f3f3f3;
            border-top: 16px solid #3498db;
            border-radius: 50%;
            width: 120px;
            height: 120px;
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush

@push('bottom')
    <script>
        $('#modal-2').click(function() {
            $('#example').modal('show');
        });
        $(document).ready(function() {
            $('#laravel_crud').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copyHtml5',
                    'excelHtml5',
                    'csvHtml5',
                    'pdfHtml5'
                ]
            });
        });

        $(document).ready(function() {
            $(".send").click(function() {
                $("#loader").show();
                setTimeout(() => {
                    $("#loader").hide();
                }, 120000);
            });
        });
        $(document).ready(function() {
            $(document).on("click", ".present", function() {
                $("#loader").show();
                setTimeout(() => {
                    $("#loader").hide();
                }, 15000);
            });
        });
    </script>
@endpush
