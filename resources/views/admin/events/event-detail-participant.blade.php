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
                                                    <th>WA Manual</th> <!-- NEW -->
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
                                                    <td>{{ $post->prefix_phone != null ? $post->fullphone : $post->phone }}
                                                    </td>
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
                                                                <button type="button" class="btn btn-primary open-modal"
                                                                    data-users-id="{{ $post->users_id }}"
                                                                    data-events-id="{{ $post->events_id }}"
                                                                    data-payment-id="{{ $post->payment_id }}"
                                                                    data-phone="{{ $post->prefix_phone != null ? $post->fullphone : $post->phone }}">
                                                                    <span class="fa fa-paper-plane"></span>
                                                                </button>
                                                            @else
                                                                {{ date('d,F H:i', strtotime($post->reminder_wa)) . ' ' . $post->name_reminder_wa }}
                                                            @endif
                                                        </td>
                                                    @endif
                                                    <td>
                                                        <button type="button" class="btn btn-success open-wa-manual"
                                                            title="Kirim via WhatsApp Manual"
                                                            data-users-id="{{ $post->users_id }}"
                                                            data-events-id="{{ $post->events_id }}"
                                                            data-payment-id="{{ $post->payment_id }}"
                                                            data-name="{{ $post->name }}"
                                                            data-phone="{{ $post->prefix_phone != null ? $post->fullphone : $post->phone }}"
                                                            data-event-name="{{ $post->event_name ?? ($event->name ?? 'Our Event') }}"
                                                            data-location="{{ $post->location ?? ($event->location ?? 'Jakarta, Indonesia') }}"
                                                            data-start-time="{{ isset($post->start_time) ? date('h.i a', strtotime($post->start_time)) : (isset($event->start_time) ? date('h.i a', strtotime($event->start_time)) : '01.30 pm') }}"
                                                            data-end-time="{{ isset($post->end_time) ? date('h.i a', strtotime($post->end_time)) : (isset($event->end_time) ? date('h.i a', strtotime($event->end_time)) : '06.00 pm') }}"
                                                            data-event-date="{{ isset($post->start_date)
                                                                ? \Carbon\Carbon::parse($post->start_date)->isoFormat('dddd, D MMMM YYYY')
                                                                : (isset($event->start_date)
                                                                    ? \Carbon\Carbon::parse($event->start_date)->isoFormat('dddd, D MMMM YYYY')
                                                                    : \Carbon\Carbon::parse($post->end_date)->isoFormat('dddd, D MMMM YYYY')) }}"
                                                            data-ticket-url="{{ $post->ticket_url ?? (isset($post->code_payment) ? url('/storage/ticket/ticket_' . $post->code_payment . '_' . time() . '.pdf') : '') }}">
                                                            <span class="fa fa-whatsapp"></span>
                                                        </button>
                                                    </td>
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

    <div class="modal fade" id="phoneModal" tabindex="-1" role="dialog" aria-labelledby="phoneModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="phoneForm" method="post" action="{{ route('events-send-participant') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="phoneModalLabel">Konfirmasi Nomor Telepon</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="phoneNumber">Nomor Telepon</label>
                            <input type="text" class="form-control" id="phoneNumber" name="phone" required>
                        </div>
                        <input type="hidden" name="users_id" id="usersId">
                        <input type="hidden" name="events_id" id="eventsId">
                        <input type="hidden" name="payment_id" id="paymentId">
                        <input type="hidden" name="method" value="confirmation_wa">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Kirim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal WA Manual -->
    <div class="modal fade" id="waManualModal" tabindex="-1" role="dialog" aria-labelledby="waManualModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="waManualModalLabel">Kirim WhatsApp Manual (wa.me)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nomor Tujuan (Internasional, tanpa +)</label>
                        <input type="text" class="form-control" id="waManualPhone" placeholder="62xxxxxxxxxxx"
                            required>
                        <small class="form-text text-muted">Otomatis diformat: “0xxxx” → “62xxxx”. Simbol + akan
                            dihapus.</small>
                    </div>
                    <div class="form-group">
                        <label>Pesan</label>
                        <textarea class="form-control" id="waManualText" rows="10" required></textarea>
                        <small class="form-text text-muted"><span id="waCharCount">0</span> karakter</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button class="btn btn-success" id="waManualSend">Buka WhatsApp</button>
                </div>
            </div>
        </div>
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
                pageLength: 20, // Set the number of rows to be displayed on each page
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

        $(document).ready(function() {
            $(document).on('click', '.open-modal', function() {
                var usersId = $(this).data('users-id');
                var eventsId = $(this).data('events-id');
                var paymentId = $(this).data('payment-id');
                var phone = $(this).data('phone'); // Ambil nomor telepon dari atribut data-phone

                // Isi data ke dalam input form di modal
                $('#usersId').val(usersId);
                $('#eventsId').val(eventsId);
                $('#paymentId').val(paymentId);
                $('#phoneNumber').val(phone); // Isi input nomor telepon

                // Tampilkan modal
                $('#phoneModal').modal('show');
            });
        });
    </script>
    <script>
        function formatPhoneTo62(raw) {
            let p = (raw || '').toString().trim();
            p = p.replace(/[^\d]/g, ''); // keep digits only
            if (p.startsWith('0')) { // 08xxx -> 628xxx
                p = '62' + p.slice(1);
            }
            if (p.startsWith('62')) { // ok
                return p;
            }
            // kalau kosong atau format lain, tetap kembalikan apa adanya (user bisa edit)
            return p;
        }

        function buildDefaultMessage(d) {
            // d = {eventName, userName, eventDate, location, startTime, endTime, ticketUrl}
            const ticketLine = d.ticketUrl ? `Your E-Ticket here: ${d.ticketUrl}\n\n` : '';
            return (
                `📌"REMINDER to attend ${d.eventName}"

Hi ${d.userName},

This is a confirmation that you are registered to attend our event on ${d.eventDate} at ${d.location}, starting at ${d.startTime} - ${d.endTime} (WIB) and followed by Networking Dinner and Drinks.

Please confirm your attendance by replying "YES" to this message. If you are unable to attend, kindly respond with "NO" so that we may offer your spot to someone on the waitlist.

${ticketLine}For the event rundown and agenda, please visit our website at www.djakarta-miningclub.com.

We look forward to seeing you there. Thank you 😊🙏🏻

Regards,
The Djakarta Mining Club Team`
            );
        }

        $(document).on('click', '.open-wa-manual', function() {
            const $btn = $(this);
            const data = {
                userName: $btn.data('name') || 'Participant',
                phone: $btn.data('phone') || '',
                eventName: $btn.data('event-name') || 'Our Event',
                location: $btn.data('location') || 'Jakarta, Indonesia',
                startTime: $btn.data('start-time') || '01.30 pm',
                endTime: $btn.data('end-time') || '06.00 pm',
                eventDate: $btn.data('event-date') || '',
                ticketUrl: $btn.data('ticket-url') || ''
            };

            // Prefill modal
            const formatted = formatPhoneTo62(data.phone);
            $('#waManualPhone').val(formatted);
            const msg = buildDefaultMessage(data);
            $('#waManualText').val(msg);
            $('#waCharCount').text(msg.length);

            $('#waManualModal').modal('show');
        });

        $('#waManualText').on('input', function() {
            $('#waCharCount').text($(this).val().length);
        });

        $('#waManualSend').on('click', function() {
            const phone = formatPhoneTo62($('#waManualPhone').val());
            const text = $('#waManualText').val() || '';
            if (!phone) {
                alert('Nomor tujuan belum diisi.');
                return;
            }
            const url = `https://wa.me/${phone}?text=${encodeURIComponent(text)}`;
            window.open(url, '_blank');
            // opsional: close modal
            $('#waManualModal').modal('hide');
        });
    </script>
@endpush
