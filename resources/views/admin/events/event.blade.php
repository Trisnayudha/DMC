@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Event Management</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="">Event Management</a>
                    </div>
                </div>
            </div>
            <div class="section-body">
                @if (auth()->user()->canAccessModule('event_conversions'))
                    <div id="conversion-diagram" data-url="{{ route('admin.event_conversions.diagram') }}">
                        <div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat Member Conversion...</div>
                    </div>
                @endif
                <h2 class="section-title">Event </h2>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Event Management</h4>
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

                                <div class="float-right d-flex" style="gap: 8px;">
                                    <a href="{{ route('admin.event_conversions.index') }}"
                                        class="btn btn-icon icon-left btn-outline-primary btn-filter mb-3"
                                        title="Lihat Analisis Konversi Member dari Event">
                                        <i class="fas fa-chart-line"></i>
                                        Member Conversion
                                    </a>
                                    <a href="{{ Route('events.create') }}"
                                        class="btn btn-icon icon-left btn-success btn-filter mb-3"
                                        id="addNewCategory">
                                        <i class="fas fa-plus-circle"></i>
                                        Add Event</a>
                                </div>

                                <form method="GET" action="{{ route('events') }}" class="form-inline mb-3">
                                    <label for="sponsor-filter" class="mr-2">Sponsor Company</label>
                                    <select name="sponsor_id" id="sponsor-filter" class="form-control mr-2" onchange="this.form.submit()">
                                        <option value="">All Sponsors</option>
                                        @foreach ($sponsors as $sponsor)
                                            <option value="{{ $sponsor->id }}" {{ (string) $sponsorId === (string) $sponsor->id ? 'selected' : '' }}>{{ $sponsor->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                </form>
                                <div class="table-responsive">
                                    <table id="laravel_crud" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="10px">No</th>
                                                <th>Date Event</th>
                                                <th>Image</th>
                                                <th>Nama</th>
                                                <th>Category</th>
                                                <th>Registrant</th>
                                                <th>Status</th>
                                                <th width="15%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; ?>
                                            @foreach ($list as $post)
                                                <tr>
                                                    <td>{{ $no++ }}</td>
                                                    <td data-order="{{ $post->start_date }}" class="text-nowrap">{{ date('d F Y', strtotime($post->start_date)) }}</td>
                                                    <td>
                                                        <img alt="image" src="{{ asset($post->image) }}"
                                                            class="rounded-circle" width="35" data-toggle="tooltip">
                                                    </td>
                                                    <td>{{ $post->name }}</td>
                                                    <td class="text-nowrap">
                                                        @php
                                                            $typeClass = [
                                                                'DMC Event' => 'badge-primary',
                                                                'DMC Partnership Event' => 'badge-info',
                                                                'Partnership Event' => 'badge-light',
                                                            ][$post->event_type] ?? 'badge-secondary';
                                                        @endphp
                                                        {{-- Label tampilan saja; nilai event_type di DB tetap. --}}
                                                        @php $typeLabel = ['DMC Event' => 'Event DMC', 'DMC Partnership Event' => 'Event DMC Partnership', 'Partnership Event' => 'Event Partnership'][$post->event_type] ?? $post->event_type; @endphp
                                                        <span class="badge {{ $typeClass }}">{{ $typeLabel ?: '-' }}</span>
                                                    </td>
                                                    @if ($post->registrant_total)
                                                        <td data-order="{{ $post->registrant_total }}" class="text-nowrap">
                                                            <strong>{{ number_format($post->registrant_total) }}</strong>
                                                            <small class="text-muted d-block">{{ number_format($post->registrant_attended) }} attendance</small>
                                                        </td>
                                                    @else
                                                        <td data-order="{{ $post->visitor_total }}" class="text-nowrap">
                                                            <strong>{{ number_format($post->visitor_total) }}</strong>
                                                            @if ($post->visitor_total)
                                                                <small class="text-muted d-block">booth visitors</small>
                                                            @endif
                                                        </td>
                                                    @endif
                                                    <td>
                                                        <div class="badge {{ $post->status == 'publish' ? 'badge-success' : 'badge-warning' }}"
                                                            style="text-transform: uppercase">
                                                            {{ $post->status }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <a href="javascript:void(0)" class="btn btn-success btn-share"
                                                            data-slug="{{ $post->slug }}"
                                                            data-url="{{ $post->share_url }}">
                                                            <span>Share</span>
                                                        </a>

                                                        <a href="{{ Route('events-details', $post->slug) }}"
                                                            class="btn btn-primary" title="Lihat Peserta">
                                                            <span class="fa fa-user"></span></a>
                                                        <a href="{{ Route('events.edit', $post->id) }}"
                                                            class="btn btn-success" title="Edit Data">
                                                            <span class="fa fa-edit"></span>
                                                        </a>
                                                        <a href="javascript:void(0)" data-id="{{ $post->id }}"
                                                            class="btn btn-danger delete"><span class=" fa fa-trash"></a>
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
    <!-- Share Link Modal -->
    <div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Share Event Link</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <p class="text-muted mb-2">Copy this link and share it anywhere:</p>
                    <div class="input-group">
                        <input type="text" id="shareLinkInput" class="form-control" readonly>
                        <button class="btn btn-primary" id="copyShareLink">Copy</button>
                    </div>
                    <div id="copySuccess" class="text-success mt-2" style="display:none;">
                        <i class="fa fa-check-circle"></i> Link copied!
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('bottom')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).ready(function() {
            $('#laravel_crud').DataTable();
        });
        $(document).on('click', '.delete', function() {

            var id = $(this).data('id');
            Swal.fire({
                title: "Anda Yakin ?",
                text: "Ingin Menghapus Data ini.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "POST",
                        url: "{{ url('admin/events/delete') }}",
                        data: {
                            id: id
                        },
                        dataType: 'json',
                        success: function(res) {
                            Swal.fire({
                                    title: "Success",
                                    icon: "success",
                                    showConfirmButton: false,
                                    position: 'center',
                                    timer: 1500
                                }),
                                window.location.reload();
                        }
                    });
                }
            });


        });
        // Diagram Member Conversion: dimuat terpisah karena hitungan metriknya berat.
        function loadConversionDiagram(year) {
            var $box = $('#conversion-diagram');
            if (!$box.length) { return; }
            $box.css('opacity', 0.5);
            $.get($box.data('url'), year ? { year: year } : {})
                .done(function (html) { $box.html(html); })
                .fail(function () { $box.html('<div class="alert alert-light">Member Conversion gagal dimuat.</div>'); })
                .always(function () { $box.css('opacity', ''); });
        }
        loadConversionDiagram();
        $(document).on('change', '#conversion-diagram-year', function () {
            loadConversionDiagram($(this).val());
        });

        // Handle Share Button Click
        $(document).on('click', '.btn-share', function() {
            const url = $(this).data('url');
            $('#shareLinkInput').val(url);
            $('#copySuccess').hide();
            $('#shareModal').modal('show');
        });

        // Handle Copy Button
        $('#copyShareLink').on('click', function() {
            const input = document.getElementById('shareLinkInput');
            input.select();
            input.setSelectionRange(0, 99999); // mobile support
            document.execCommand('copy');

            $('#copySuccess').fadeIn(200).delay(1500).fadeOut(300);
        });
    </script>
@endpush
