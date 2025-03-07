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
                        <button class="btn btn-icon btn-light" onclick="refreshInbox()" title="Refresh">
                            <i class="fas fa-sync"></i>
                        </button>
                        <button class="btn btn-icon btn-light" onclick="deleteSelected()" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>

                    <!-- Daftar Email -->
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <tbody>
                                <!-- Contoh data dummy 1 -->
                                <tr onclick="openEmailDetail(1)" style="cursor: pointer;">
                                    <td class="align-middle" width="5%" onclick="event.stopPropagation();">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="selectMail1">
                                            <label class="custom-control-label" for="selectMail1"></label>
                                        </div>
                                    </td>
                                    <td class="align-middle" width="5%" onclick="event.stopPropagation();">
                                        <a href="javascript:void(0);" onclick="toggleStar(event, 1)">
                                            <i class="far fa-star"></i>
                                        </a>
                                    </td>
                                    <td class="align-middle">
                                        <strong>Postmark</strong> — Your monthly usage report is available
                                    </td>
                                    <td class="align-middle text-right" width="15%">Mar 7</td>
                                </tr>

                                <!-- Contoh data dummy 2 -->
                                <tr onclick="openEmailDetail(2)" style="cursor: pointer;">
                                    <td class="align-middle" width="5%" onclick="event.stopPropagation();">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="selectMail2">
                                            <label class="custom-control-label" for="selectMail2"></label>
                                        </div>
                                    </td>
                                    <td class="align-middle" width="5%" onclick="event.stopPropagation();">
                                        <a href="javascript:void(0);" onclick="toggleStar(event, 2)">
                                            <i class="far fa-star"></i>
                                        </a>
                                    </td>
                                    <td class="align-middle">
                                        <strong>Client A</strong> — Meeting schedule confirmation
                                    </td>
                                    <td class="align-middle text-right" width="15%">Mar 6</td>
                                </tr>

                                <!-- Tambahkan row lain sesuai kebutuhan -->
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
                            <input type="text" id="toEmails" class="form-control"
                                placeholder="recipient@example.com" required>
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

    <!-- MODAL DETAIL EMAIL (opsional) -->
    <div class="modal fade" tabindex="-1" role="dialog" id="emailDetailModal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Email Detail</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        onclick="$('#emailDetailModal').modal('hide');">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="emailDetailContent">Memuat detail email...</p>
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
            alert('Refresh inbox...');
            // Implementasi AJAX/Fetch jika ingin data real
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

        function openEmailDetail(mailId) {
            // Bisa panggil AJAX untuk get detail email
            document.getElementById('emailDetailContent').innerHTML =
                'Detail email ID: ' + mailId + '<br><br>' +
                'Ini contoh konten detail email yang bisa Anda ambil dari database atau API.';
            $('#emailDetailModal').modal('show');
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
