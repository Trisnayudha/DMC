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
                                                <th>nama</th>
                                                <th>nama_ayah
                                                </th>
                                                <th>no_telp
                                                </th>
                                                <th>email
                                                </th>
                                                <th>alamat
                                                </th>
                                                <th>lahir
                                                </th>
                                                <th>ttl
                                                </th>
                                                <th>nama_ibu
                                                </th>
                                                <th>no_hp
                                                </th>
                                                <th>kota
                                                </th>
                                                <th>kode_pos
                                                </th>
                                                <th>rumah
                                                </th>
                                                <th>rumah_lainnya
                                                </th>
                                                <th>biaya_pendidikan
                                                </th>
                                                <th>biaya_pendidikan_lainnya
                                                </th>
                                                <th>ukt
                                                </th>
                                                <th>ukt_lainnya
                                                </th>
                                                <th>beasiswa
                                                </th>
                                                <th>beasiswa_lainnya
                                                </th>
                                                <th>alasan
                                                </th>
                                                <th>nama_kampus
                                                </th>
                                                <th>jurusan
                                                </th>
                                                <th>indeks_prestasi
                                                </th>
                                                <th>fakultas
                                                </th>
                                                <th>semester
                                                </th>
                                                <th>indeks_prestasi_kumulatif
                                                </th>
                                                <th>prestasi
                                                </th>
                                                <th>kta
                                                </th>
                                                <th>ipk
                                                </th>
                                                <th>ktp
                                                </th>
                                                <th>kk
                                                </th>
                                                <th>slip_gaji
                                                </th>
                                                <th>bop
                                                </th>
                                                <th>sertifikat_prestasi
                                                </th>
                                                <th>pas_foto
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; ?>
                                            @foreach ($list as $post)
                                                <tr id="row_{{ $post->id }}">
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ date('d,F Y H:i', strtotime($post->created_at)) }}</td>
                                                    <td>{{ $post->nama }}</td>
                                                    <td>{{ $post->nama_ayah }}</td>
                                                    <td>{{ $post->no_telp }}</td>
                                                    <td>{{ $post->email }}</td>
                                                    <td>{{ $post->alamat }}</td>
                                                    <td>{{ $post->lahir }}</td>
                                                    <td>{{ $post->ttl }}</td>
                                                    <td>{{ $post->nama_ibu }}</td>
                                                    <td>{{ $post->no_hp }}</td>
                                                    <td>{{ $post->kota }}</td>
                                                    <td>{{ $post->kode_pos }}</td>
                                                    <td>{{ $post->rumah }}</td>
                                                    <td>{{ $post->rumah_lainnya }}</td>
                                                    <td>{{ $post->biaya_pendidikan }}</td>
                                                    <td>{{ $post->biaya_pendidikan_lainnya }}</td>
                                                    <td>{{ $post->ukt }}</td>
                                                    <td>{{ $post->ukt_lainnya }}</td>
                                                    <td>{{ $post->beasiswa }}</td>
                                                    <td>{{ $post->beasiswa_lainnya }}</td>
                                                    <td>{!! $post->alasan !!}</td>
                                                    <td>{{ $post->nama_kampus }}</td>
                                                    <td>{{ $post->jurusan }}</td>
                                                    <td>{{ $post->indeks_prestasi }}</td>
                                                    <td>{{ $post->fakultas }}</td>
                                                    <td>{{ $post->semester }}</td>
                                                    <td>{{ $post->indeks_prestasi_kumulatif }}</td>
                                                    <td>{!! $post->prestasi !!}</td>
                                                    <td>{{ $post->kta }}</td>
                                                    <td>{{ $post->ipk }}</td>
                                                    <td>{{ $post->ktp }}</td>
                                                    <td>{{ $post->kk }}</td>
                                                    <td>{{ $post->slip_gaji }}</td>
                                                    <td>{{ $post->bop }}</td>
                                                    <td>{{ $post->sertifikat_prestasi }}</td>
                                                    <td>{{ $post->pas_foto }}</td>
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
    </script>
@endpush
