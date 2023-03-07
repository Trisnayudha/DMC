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
                                                @endif
                                                @if ($list[0]['end_date'] >= date('Y-m-d'))
                                                    <th>Date Send Confirmation</th>
                                                @endif
                                                <th>Date Present </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; ?>
                                            @foreach ($list as $post)
                                                <tr id="row_{{ $post->id }}">
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ date('d,F Y H:i', strtotime($post->payment_update)) }}</td>
                                                    <td>{{ $post->code_payment }}</td>
                                                    <td>{{ $post->package }}</td>
                                                    <td>{{ $post->name }}</td>
                                                    <td>{{ $post->email }}</td>
                                                    <td>{{ $post->job_title }}</td>
                                                    <td>{{ $post->company_name }}</td>
                                                    <td>{{ $post->phone }}</td>
                                                    <td>{{ $post->address }}</td>
                                                    @if ($post->end_date <= date('Y-m-d'))
                                                        <td>{{ $post->company_category != null ? $post->company_category : $post->company_other }}
                                                    @endif
                                                    </td>
                                                    @if ($post->end_date >= date('Y-m-d'))
                                                        <td>
                                                            @if ($post->create == null)
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
                                                                {{ date('d,F H:i', strtotime($post->create)) }}
                                                            @endif
                                                        </td>
                                                    @endif
                                                    <td>
                                                        @if ($post->update == null)
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
                                                            {{ date('d,F H:i', strtotime($post->update)) }}
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
            $(".present").click(function() {
                $("#loader").show();
                setTimeout(() => {
                    $("#loader").hide();
                }, 15000);
            });
        });
    </script>
@endpush
