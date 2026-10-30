@extends('layouts.inspire.master')

@php
    // [label, field, tipe]. Tipe: text | date | long (esai, dibuka lewat modal) | file (link dokumen) | link.
    $columns = [
        ['Date Register', 'created_at', 'date'],
        ['Name', 'nama', 'text'],
        ['Email', 'email', 'text'],
        ['Phone', 'no_telp', 'text'],
        ['Mobile', 'no_hp', 'text'],
        ['Place of Birth', 'lahir', 'text'],
        ['Date of Birth', 'ttl', 'text'],
        ['Address', 'alamat', 'text'],
        ['City', 'kota', 'text'],
        ['Postal Code', 'kode_pos', 'text'],
        ["Father's Name", 'nama_ayah', 'text'],
        ["Mother's Name", 'nama_ibu', 'text'],
        ['House', 'rumah', 'text'],
        ['House (Other)', 'rumah_lainnya', 'text'],
        ['Education Funding', 'biaya_pendidikan', 'text'],
        ['Education Funding (Other)', 'biaya_pendidikan_lainnya', 'text'],
        ['UKT', 'ukt', 'text'],
        ['UKT (Other)', 'ukt_lainnya', 'text'],
        ['Other Scholarship', 'beasiswa', 'text'],
        ['Other Scholarship (Detail)', 'beasiswa_lainnya', 'text'],
        ['University', 'nama_kampus', 'text'],
        ['Faculty', 'fakultas', 'text'],
        ['Major', 'jurusan', 'text'],
        ['Semester', 'semester', 'text'],
        ['IP', 'indeks_prestasi', 'text'],
        ['IPK', 'indeks_prestasi_kumulatif', 'text'],
        ['Tuition Fee', 'nominal_biaya', 'text'],
        ['Reason', 'alasan', 'long'],
        ['Achievements', 'prestasi', 'long'],
        ['LinkedIn', 'linkedin', 'link'],
        ['KTA', 'kta', 'file'],
        ['Transcript (IPK)', 'ipk', 'file'],
        ['KTP', 'ktp', 'file'],
        ['KK', 'kk', 'file'],
        ['Salary Slip', 'slip_gaji', 'file'],
        ['BOP', 'bop', 'file'],
        ['Achievement Certificate', 'sertifikat_prestasi', 'file'],
        ['Photo', 'pas_foto', 'file'],
    ];

    // Esai disimpan sebagai HTML dari editor form publik → ditampilkan sebagai teks biasa.
    $plainText = function ($html) {
        $text = str_ireplace(['</p>', '<br>', '<br/>', '<br />'], "\n", (string) $html);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8');
        return trim(preg_replace("/\n{3,}/", "\n\n", str_replace("\u{a0}", ' ', $text)));
    };
@endphp

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Scholarship Applicants</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active">Scholarship</div>
                </div>
            </div>
            <div class="section-body">
                <div class="card">
                    <div class="card-header">
                        <h4>Scholarship Applicants <span class="badge badge-light ml-1">{{ count($list) }}</span></h4>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <div class="table-responsive">
                            <table id="scholarship-table" class="table table-sm table-bordered table-hover scholarship-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        @foreach ($columns as [$label])
                                            <th>{{ $label }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($list as $post)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            @foreach ($columns as [$label, $field, $type])
                                                @php $value = $post->{$field} ?? null; @endphp
                                                @if ($type === 'date')
                                                    <td data-order="{{ $value }}">{{ $value ? date('d M Y H:i', strtotime($value)) : '-' }}</td>
                                                @elseif ($type === 'long')
                                                    @php $text = $plainText($value); @endphp
                                                    <td class="cell-long" data-export="{{ $text }}">
                                                        @if ($text !== '')
                                                            <span class="cell-snippet">{{ \Illuminate\Support\Str::limit(str_replace("\n", ' ', $text), 60) }}</span>
                                                            <button type="button" class="btn btn-link btn-sm p-0 ml-1 btn-read-more"
                                                                data-title="{{ $label }} — {{ $post->nama }}" data-text="{{ $text }}">Read</button>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                @elseif ($type === 'file' || $type === 'link')
                                                    <td data-export="{{ $value }}">
                                                        @if ($value)
                                                            <a href="{{ $value }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary py-0">
                                                                <i class="fas fa-{{ $type === 'file' ? 'file-alt' : 'external-link-alt' }}"></i> Open
                                                            </a>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                @else
                                                    <td title="{{ $value }}">{{ $value !== null && $value !== '' ? $value : '-' }}</td>
                                                @endif
                                            @endforeach
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

    {{-- Teks lengkap kolom esai (Reason / Achievements) --}}
    <div class="modal fade" id="scholarshipTextModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="scholarship-text-title"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body" id="scholarship-text-body" style="white-space:pre-line;"></div>
            </div>
        </div>
    </div>
@endsection

@push('top')
    <style>
        /* Satu baris per sel; isi kepanjangan dipotong "…" (isi lengkap di tooltip / export). */
        .scholarship-table th,
        .scholarship-table td {
            white-space: nowrap;
            vertical-align: middle;
            font-size: 12.5px;
        }
        .scholarship-table td {
            max-width: 220px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .scholarship-table td.cell-long { max-width: 320px; }
        .scholarship-table .cell-snippet { color: #6c757d; }
    </style>
@endpush

@push('bottom')
    <script>
        $(document).ready(function() {
            // Export memakai data-export (teks esai utuh / URL dokumen), bukan teks tombol di sel.
            var exportOptions = {
                format: {
                    body: function(data, row, column, node) {
                        var full = $(node).attr('data-export');
                        return full !== undefined ? full : $(node).text().trim();
                    }
                }
            };
            $('#scholarship-table').DataTable({
                dom: 'Bfrtip',
                pageLength: 20,
                autoWidth: false,
                order: [[1, 'desc']],
                buttons: [
                    { extend: 'copyHtml5', exportOptions: exportOptions },
                    { extend: 'excelHtml5', exportOptions: exportOptions },
                    { extend: 'csvHtml5', exportOptions: exportOptions },
                    { extend: 'pdfHtml5', exportOptions: exportOptions, orientation: 'landscape', pageSize: 'A3' }
                ]
            });

            $(document).on('click', '.btn-read-more', function() {
                $('#scholarship-text-title').text($(this).data('title'));
                $('#scholarship-text-body').text($(this).data('text'));
                $('#scholarshipTextModal').modal('show');
            });
        });
    </script>
@endpush
