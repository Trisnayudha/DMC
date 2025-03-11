@extends('layouts.inspire.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Inbox</h1>
            <div class="section-header-button ml-auto">
                <!-- Tombol Compose -->
                <button class="btn btn-primary" onclick="openComposeModal()">
                    <i class="fas fa-edit"></i> Compose
                </button>
            </div>
        </div>
        <div class="section-body">

            <!-- ALERT MESSAGES (untuk menampilkan pesan sukses/gagal) -->
            <div id="alert-container"></div>

            <!-- Kartu Daftar Email (Inbox) -->
            <div class="card">
                <div class="card-header">
                    <h4>Inbox</h4>
                    <div class="card-header-action">
                        <!-- Bagian Search mirip Gmail -->
                        <form action="#" method="GET" class="d-flex">
                            <input type="text" class="form-control" name="q" placeholder="Search mail...">
                            <button type="submit" class="btn btn-primary ml-2">Search</button>
                        </form>
                    </div>
                </div>

                <div class="card-body p-2">
                    <!-- Baris Action (Select All, Refresh, Delete, dsb.) -->
                    <div class="mb-3">
                        <button class="btn btn-icon btn-light" onclick="toggleSelectAll()" title="Select All">
                            <i class="far fa-square"></i>
                        </button>
                        <button id="refreshButton" class="btn btn-icon btn-light" onclick="refreshInbox()" title="Refresh">
                            <i class="fas fa-sync"></i>
                        </button>

                        <button class="btn btn-icon btn-light" onclick="deleteSelected()" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>

                    <!-- Daftar Email -->
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th width="5%"></th>
                                    <th width="5%"></th>
                                    <th>Message ID</th>
                                    <th>Email Info</th>
                                    <th width="20%">Date &amp; Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($list as $callback)
                                    <tr onclick="openEmailDetail('{{ $callback->message_id }}')" style="cursor: pointer;">
                                        <td class="align-middle" width="5%" onclick="event.stopPropagation();">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input"
                                                    id="selectMail{{ $callback->id }}">
                                                <label class="custom-control-label"
                                                    for="selectMail{{ $callback->id }}"></label>
                                            </div>
                                        </td>
                                        <td class="align-middle" width="5%" onclick="event.stopPropagation();">
                                            <a href="javascript:void(0);" onclick="toggleStar(event, {{ $callback->id }})">
                                                <i class="far fa-star"></i>
                                            </a>
                                        </td>
                                        <!-- Menampilkan message_id secara unik, disingkat agar tampilan tetap rapi -->
                                        <td class="align-middle">
                                            <strong>{{ substr($callback->message_id, 0, 8) . '...' }}</strong>
                                        </td>
                                        <!-- Menampilkan jenis record dan recipient -->
                                        <td class="align-middle">
                                            <strong>{{ $callback->record_type ?? 'Unknown' }}</strong>
                                            — {{ $callback->recipient ?? '-' }}
                                        </td>
                                        <!-- Menampilkan tanggal dan waktu -->
                                        <td class="align-middle text-right">
                                            {{ $callback->created_at ? $callback->created_at->format('M d, Y H:i') : '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada email yang diterima.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- MODAL COMPOSE EMAIL -->
    <div class="modal fade" tabindex="-1" role="dialog" id="composeModal">
        <div class="modal-dialog modal-lg" role="document">
            <form id="composeForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">New Message</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                            onclick="closeComposeModal()">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">

                        <!-- BAGIAN DITAMBAHKAN: FROM EMAIL (Dropdown) -->
                        <div class="form-group">
                            <label for="fromEmail">From</label>
                            <select class="form-control" id="fromEmail" name="fromEmail">
                                <option value="register@djakarta-miningclub.com" selected>
                                    register@djakarta-miningclub.com
                                </option>
                                <option value="secretariat@djakarta-miningclub.com">
                                    secretariat@djakarta-miningclub.com
                                </option>
                            </select>
                        </div>
                        <!-- /FROM EMAIL -->

                        <!-- To -->
                        <div class="form-group">
                            <label>To</label>
                            <input type="text" id="toEmails" class="form-control" placeholder="recipient@example.com"
                                required>
                        </div>

                        <!-- CC -->
                        <div class="form-group">
                            <label>CC</label>
                            <input type="text" id="ccEmails" class="form-control" placeholder="cc@example.com">
                        </div>

                        <!-- BCC -->
                        <div class="form-group">
                            <label>BCC</label>
                            <input type="text" id="bccEmails" class="form-control" placeholder="bcc@example.com">
                        </div>

                        <!-- Subject -->
                        <div class="form-group">
                            <label>Subject</label>
                            <input type="text" name="subject" class="form-control" placeholder="Subject" required>
                        </div>

                        <!-- Body (Summernote) -->
                        <div class="form-group">
                            <label>Message</label>
                            <textarea name="body" id="bodyEditor" class="form-control" rows="6"
                                placeholder="Write your message here..." required></textarea>
                        </div>

                        <!-- Attachment (multiple) -->
                        <div class="form-group">
                            <label>Attachments</label>
                            <input type="file" id="attachments" name="attachments[]" class="filepond" multiple
                                data-max-file-size="10MB" data-max-files="10" />
                        </div>
                    </div>

                    <div class="modal-footer bg-whitesmoke br">
                        <button type="button" class="btn btn-secondary" onclick="closeComposeModal()">Close</button>
                        <button type="button" class="btn btn-primary" onclick="sendEmail()">Send</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EMAIL DETAIL -->
    <div class="modal fade" id="emailDetailModal" tabindex="-1" role="dialog" aria-labelledby="emailDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="emailDetailModalLabel">Email Detail</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"
                        onclick="closeEmailDetailModal()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="emailDetailContent">
                    <!-- Konten detail email akan ditampilkan di sini -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"
                        onclick="closeEmailDetailModal()">Close</button>
                </div>
            </div>
        </div>
    </div>




    <!-- SCRIPT UTAMA (inline) -->
    <script>
        // Toggle semua checkbox
        function toggleSelectAll() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach((cb) => {
                cb.checked = !cb.checked;
            });
        }

        function refreshInbox() {
            var $refreshBtn = $('#refreshButton');
            $refreshBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: "{{ route('email.refresh') }}",
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.status === 'success') {
                        $('tbody').html(response.data);
                    } else {
                        showAlert('danger', response.message || 'Gagal menyegarkan inbox.');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    showAlert('danger', 'Error refreshing inbox: ' + errorThrown);
                },
                complete: function() {
                    $refreshBtn.prop('disabled', false).html('<i class="fas fa-sync"></i>');
                }
            });
        }



        function deleteSelected() {
            alert('Delete selected emails...');
            // Implementasi AJAX/Fetch untuk menghapus email terpilih
        }

        function toggleStar(e, mailId) {
            e.stopPropagation();
            alert('Toggle star for mail ID: ' + mailId);
            // Implementasi AJAX untuk menandai/unmark star
        }

        function openEmailDetail(messageId) {
            // Tampilkan modal dengan loading spinner terlebih dahulu
            $('#emailDetailContent').html(`
        <div class="text-center py-5">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p>Loading email details...</p>
        </div>
    `);
            $('#emailDetailModal').modal('show');

            $.ajax({
                url: "{{ route('email.detailAjax', '') }}/" + messageId,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.status === 'success') {
                        let details = response.details;
                        let html = '';

                        // Bagian Header Email dengan Card
                        html += '<div class="card mb-3">';
                        html += '  <div class="card-body">';
                        html += '    <h5 class="card-title">' + (details.subject || '-') + '</h5>';
                        html += '    <p class="card-text"><small class="text-muted">' +
                            (details.date ? moment(details.date).format('LLL') : '-') + '</small></p>';
                        html += '    <p class="mb-1"><strong>From:</strong> ' + (details.from || '-') + '</p>';
                        if (details.to && details.to.length > 0) {
                            let toEmails = details.to.map(function(item) {
                                return item.Email;
                            }).join(', ');
                            html += '    <p class="mb-1"><strong>To:</strong> ' + toEmails + '</p>';
                        } else {
                            html += '    <p class="mb-1"><strong>To:</strong> -</p>';
                        }
                        html += '  </div>';
                        html += '</div>';

                        // Bagian Body Email (HTML)
                        html += '<div class="mb-3">';
                        html += '  <h6>Message</h6>';
                        html += '  <div class="border p-3" style="background-color: #f8f9fa;">' + (details
                            .htmlbody || '-') + '</div>';
                        html += '</div>';

                        // Bagian Message Events (dengan list-group)
                        if (details.messageevents && details.messageevents.length > 0) {
                            html += '<div class="mb-3">';
                            html += '  <h6>Message Events</h6>';
                            html += '  <ul class="list-group">';
                            details.messageevents.forEach(function(event) {
                                // Format tanggal event menjadi format yang mudah dibaca
                                let eventTime = event.ReceivedAt ? moment(event.ReceivedAt).format(
                                    'LLL') : '-';
                                html +=
                                    '<li class="list-group-item d-flex justify-content-between align-items-center">';
                                html += '  <span><strong>' + (event.Type || 'Unknown') +
                                    '</strong></span>';
                                html += '  <span class="badge badge-secondary badge-pill">' +
                                    eventTime + '</span>';
                                html += '</li>';
                            });
                            html += '  </ul>';
                            html += '</div>';
                        }

                        // Masukkan HTML ke modal dan tampilkan modal
                        $('#emailDetailContent').html(html);
                    } else {
                        showAlert('danger', response.message || 'Gagal memuat detail email.');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    showAlert('danger', 'Error loading details: ' + errorThrown);
                }
            });
        }


        function closeEmailDetailModal() {
            $('#emailDetailModal').modal('hide');
        }


        function closeEmailDetailModal() {
            $('#emailDetailModal').modal('hide');
        }



        // Compose Modal
        function openComposeModal() {
            // Reset form
            document.getElementById('composeForm').reset();
            $('#composeModal').modal('show');
        }

        function closeComposeModal() {
            $('#composeModal').modal('hide');
        }

        // Kirim email via AJAX menggunakan jQuery
        function sendEmail() {
            let form = document.getElementById('composeForm');
            let formData = new FormData(form);

            // BAGIAN DITAMBAHKAN: fromEmail
            let fromValue = document.getElementById('fromEmail').value;
            formData.append('fromEmail', fromValue);

            // 1) Ambil file-file dari FilePond
            let pondFiles = window.pond.getFiles(); // 'pond' adalah instance FilePond

            // 2) Append file ke formData
            pondFiles.forEach(fileItem => {
                formData.append('attachments[]', fileItem.file, fileItem.file.name);
            });

            // 3) (Opsional) Ambil Tagify (To, CC, BCC)
            if (window.tagifyTo) {
                window.tagifyTo.value.forEach(tag => {
                    formData.append('to[]', tag.value);
                });
            }
            if (window.tagifyCc) {
                window.tagifyCc.value.forEach(tag => {
                    formData.append('cc[]', tag.value);
                });
            }
            if (window.tagifyBcc) {
                window.tagifyBcc.value.forEach(tag => {
                    formData.append('bcc[]', tag.value);
                });
            }

            // 4) Kirim AJAX
            $.ajax({
                url: "{{ route('email.send') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(data) {
                    if (data.status === 'success') {
                        showAlert('success', data.message || 'Email sent successfully!');
                        closeComposeModal();
                    } else {
                        showAlert('danger', data.message || 'Failed to send email.');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error(errorThrown);
                    showAlert('danger', 'Error sending email: ' + errorThrown);
                }
            });
        }

        // Fungsi menampilkan alert
        function showAlert(type, message) {
            const alertContainer = document.getElementById('alert-container');
            alertContainer.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // INISIALISASI SUMMERNOTE
        $(document).ready(function() {
            $('#bodyEditor').summernote({
                height: 200, // tinggi editor
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link', 'picture', 'video']],
                    ['misc', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
    </script>
@endsection

{{--
    BAGIAN PENTING:
    CSS (Tagify, FilePond) ditempatkan di stack 'top' => akan di-load di <head> layout
--}}
@push('top')
    <style>
        /* Contoh menyesuaikan tampilan Tagify agar mirip form-control */
        .tagify__input {
            min-height: auto !important;
            line-height: 1.5 !important;
        }

        .tagify {
            border-radius: 4px;
            border: 1px solid #ced4da;
            padding: 0.375rem 0.75rem;
        }

        .tagify__input {
            box-shadow: none !important;
            outline: none !important;
        }
    </style>

    <!-- Tagify CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" />

    <!-- FilePond CSS -->
    <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />

    <!-- FilePond (library utama) -->
    <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>

    <!-- Plugin Preview (untuk thumbnail gambar) -->
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css"
        rel="stylesheet" />
@endpush

{{--
    Script plugin & inisialisasi di stack 'bottom' => di-load sebelum </body>
--}}
@push('bottom')
    <!-- Plugin FilePond Preview, Resize, Compress -->
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-resize/dist/filepond-plugin-image-resize.js"></script>
    <!-- Ganti 'latest' dengan versi spesifik, misal '2.2.6' -->
    <script src="https://unpkg.com/filepond-plugin-image-preview@4.6.12/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

    <!-- Tagify JS -->
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>

    <script>
        // INISIALISASI TAGIFY
        document.addEventListener('DOMContentLoaded', function() {
            var inputTo = document.querySelector('#toEmails');
            var tagifyTo = new Tagify(inputTo, {
                delimiters: ", ",
                pattern: /[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,4}$/i,
                maxTags: 20
            });

            var inputCc = document.querySelector('#ccEmails');
            var tagifyCc = new Tagify(inputCc, {
                delimiters: ", "
            });

            var inputBcc = document.querySelector('#bccEmails');
            var tagifyBcc = new Tagify(inputBcc, {
                delimiters: ", "
            });

            window.tagifyTo = tagifyTo;
            window.tagifyCc = tagifyCc;
            window.tagifyBcc = tagifyBcc;
        });

        // INISIALISASI FILEPOND + PLUGIN
        document.addEventListener('DOMContentLoaded', function() {
            // Daftarkan plugin
            FilePond.registerPlugin(
                FilePondPluginImagePreview,
                FilePondPluginImageResize
            );

            // Pilih elemen input
            const inputElement = document.querySelector('#attachments');

            // Buat instance FilePond
            const pond = FilePond.create(inputElement, {
                imageResizeTargetWidth: 1920,
                imageResizeTargetHeight: 1080,
                imageCompressQuality: 0.7,
                allowImagePreview: true,
                allowMultiple: true
            });

            window.pond = pond;
        });
    </script>
@endpush
