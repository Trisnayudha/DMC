@extends('layouts.inspire.master')
@section('content-title', 'Tambah Program')
@section('content')
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ Route('news') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Add News</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ Route('news') }}">Berita Management</a></div>
                <div class="breadcrumb-item active"><a href="">Add News</a></div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">Add News</h2>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4>Keterangan Berita</h4>
                        </div>
                        <div class="card-body">
                            {!! Form::open(['method' => 'POST', 'route' => 'news.store', 'enctype' => 'multipart/form-data']) !!}
                            <div class="form-group{{ $errors->has('title') ? ' has-error' : '' }}">
                                {!! Form::label('Judul Berita') !!}
                                {!! Form::text('title', old('title'), ['class' => 'form-control', 'placeholder' => 'Judul Berita']) !!}
                                @if ($errors->has('title'))
                                    <span class="help-block"><strong
                                            style="color:red">{{ $errors->first('title') }}</strong></span>
                                @endif
                            </div>

                            <div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">
                                {!! Form::label('Deskripsi') !!}
                                {!! Form::textarea('description', old('description'), [
                                    'id' => 'my-editor',
                                    'class' => 'form-control',
                                    'placeholder' => 'Berita',
                                ]) !!}
                                @if ($errors->has('description'))
                                    <span class="help-block"><strong
                                            style="color:red">{{ $errors->first('description') }}</strong></span>
                                @endif
                            </div>

                            <div class="form-group{{ $errors->has('description2') ? ' has-error' : '' }}">
                                {!! Form::label('Deskripsi 2') !!}
                                {!! Form::textarea('description2', old('description2'), [
                                    'id' => 'my-editor2',
                                    'class' => 'form-control',
                                    'placeholder' => 'Berita Bagian 2',
                                ]) !!}
                                @if ($errors->has('description2'))
                                    <span class="help-block"><strong
                                            style="color:red">{{ $errors->first('description2') }}</strong></span>
                                @endif
                            </div>

                            <div class="form-group{{ $errors->has('reference_image') ? ' has-error' : '' }}">
                                <label>Reference Image</label>
                                {!! Form::textarea('reference_image', old('reference_image'), [
                                    'cols' => '30',
                                    'rows' => '5',
                                    'class' => 'form-control',
                                    'placeholder' => 'Image Reference',
                                ]) !!}
                                @if ($errors->has('reference_image'))
                                    <span class="help-block"><strong
                                            style="color:red">{{ $errors->first('reference_image') }}</strong></span>
                                @endif
                            </div>

                            <div class="form-group{{ $errors->has('reference_link') ? ' has-error' : '' }}">
                                <label>Reference Link</label>
                                {!! Form::textarea('reference_link', old('reference_link'), [
                                    'cols' => '30',
                                    'rows' => '5',
                                    'class' => 'form-control',
                                    'placeholder' => 'Link Reference',
                                ]) !!}
                                @if ($errors->has('reference_link'))
                                    <span class="help-block"><strong
                                            style="color:red">{{ $errors->first('reference_link') }}</strong></span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h4>Featured Image</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group{{ $errors->has('image') ? ' has-error' : '' }}">
                                {!! Form::label('Thumbnails') !!}
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="image" name="image">
                                        <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                    </div>
                                </div>
                                @if ($errors->has('image'))
                                    <span class="help-block"><strong
                                            style="color:red">{{ $errors->first('thumb') }}</strong></span>
                                @endif
                                <img id="holder" style="margin-top:15px;max-height:100px;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Categories</h4>
                                </div>
                                <div class="card-body">
                                    <div class="form-group{{ $errors->has('category_id') ? ' has-error' : '' }}">
                                        {!! Form::label('Kategori *') !!}
                                        {!! Form::select('category_id[]', $categories->pluck('name_category', 'id'), null, [
                                            'multiple' => 'multiple',
                                            'class' => 'form-control select2',
                                        ]) !!}
                                        @if ($errors->has('category_id'))
                                            <span class="help-block"><strong
                                                    style="color:red">{{ $errors->first('category_id') }}</strong></span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Publish</h4>
                                </div>
                                <div class="card-body">
                                    <div class="form-group{{ $errors->has('date_news') ? ' has-error' : '' }}">
                                        {!! Form::label('Date News *') !!}
                                        <div class="input-group date">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text"><i class="fas fa-calendar"></i></div>
                                            </div>
                                            {!! Form::text('date_news', date('Y-m-d H:i'), [
                                                'class' => 'form-control datepicker',
                                                'placeholder' => 'Tanggal Mulai',
                                            ]) !!}
                                        </div>
                                        @if ($errors->has('date_news'))
                                            <span class="help-block"><strong
                                                    style="color:red">{{ $errors->first('date_news') }}</strong></span>
                                        @endif
                                    </div>

                                    <div class="form-group{{ $errors->has('status') ? ' has-error' : '' }}">
                                        {!! Form::label('Status') !!}
                                        {!! Form::select('status', ['draft' => 'Draft', 'publish' => 'Publish'], null, ['class' => 'form-control']) !!}
                                        @if ($errors->has('status'))
                                            <span class="help-block"><strong
                                                    style="color:red">{{ $errors->first('status') }}</strong></span>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-footer text-right">
                                    <div class="pull-right">
                                        <a href="{{ route('news') }}" class="btn btn-warning">Close</a>
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </section>

    {{-- libs --}}
    <script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
    <script src="{{ asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>

    <script>
        $(function() {
            bsCustomFileInput.init();

            // Inisialisasi 2 editor
            $('#my-editor, #my-editor2').summernote({
                dialogsInBody: true,
                minHeight: 150,
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear', 'link', 'picture', 'video',
                        'undo'
                    ]],
                    ['font', ['strikethrough']],
                    ['para', ['paragraph']]
                ]
            });

            // Sembunyikan "Select from files" sekali saja (global)
            $(document).on('shown.bs.modal', '.note-image-dialog', function() {
                $(this).find('.note-group-select-from-files').remove();
            });
        });
    </script>

    {{-- ===== Floating Upload (AJAX) ===== --}}
    <!-- Input file disimpan di luar FAB agar tidak trigger bubbling -->
    <input type="file" id="fabInput" accept="image/*" hidden>

    <!-- FAB pakai label-for: no JS click, no bubbling -->
    <label class="fab-upload" for="fabInput" title="Upload image (AJAX)">
        <i class="fas fa-cloud-upload-alt"></i>
    </label>

    <div class="upload-result shadow" id="uploadResult" style="display:none;">
        <button type="button" id="uploadClose" class="upload-close" aria-label="Close">&times;</button>
        <div class="d-flex gap-2 align-items-center">
            <input type="text" id="lastUploadUrl" class="form-control form-control-sm" readonly
                placeholder="URL hasil upload">
            <button type="button" id="copyUrlBtn" class="btn btn-secondary btn-sm">Copy</button>
            <button type="button" id="insertToEditor1" class="btn btn-outline-primary btn-sm">Insert ke
                Deskripsi</button>
            <button type="button" id="insertToEditor2" class="btn btn-outline-primary btn-sm">Insert ke Deskripsi
                2</button>
        </div>
        <small id="uploadMsg" class="text-muted d-block mt-1"></small>
    </div>


    <script>
        // Endpoint upload
        const UPLOAD_URL = "{{ route('ajax.image.upload') }}";

        function showResultPanel(show = true) {
            $('#uploadResult').toggle(show);
        }

        function setMsg(msg, isErr = false) {
            $('#uploadMsg').text(msg).toggleClass('text-danger', !!isErr).toggleClass('text-muted', !isErr);
        }
        async function copyToClipboard(text) {
            try {
                await navigator.clipboard.writeText(text);
                return true;
            } catch (e) {
                return false;
            }
        }

        // AJAX CSRF
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Upload handler
        $(document).on('change', '#fabInput', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const fd = new FormData();
            fd.append('image', file);

            setMsg('Uploading...');
            showResultPanel(true);

            $.ajax({
                url: UPLOAD_URL,
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                timeout: 120000,
                success: function(res) {
                    const url = res?.url || res?.data?.url;
                    if (!url) {
                        setMsg('Upload sukses tapi URL tidak ditemukan di response.', true);
                        return;
                    }
                    $('#lastUploadUrl').val(url);
                    setMsg('Upload berhasil. URL siap dipakai.');
                    copyToClipboard(url).then(ok => {
                        if (ok) setMsg('Upload berhasil. URL sudah di-copy ke clipboard.');
                    });
                },
                error: function(xhr) {
                    const msg = xhr?.responseJSON?.message || 'Gagal upload. Coba lagi.';
                    setMsg(msg, true);
                },
                complete: function() {
                    $('#fabInput').val('');
                }
            });
        });

        // Copy
        $(document).on('click', '#copyUrlBtn', async function() {
            const url = $('#lastUploadUrl').val();
            if (!url) return;
            const ok = await copyToClipboard(url);
            setMsg(ok ? 'URL copied to clipboard.' : 'Tidak bisa menyalin otomatis, copy manual ya.');
        });

        // Insert ke editor
        function insertImageToEditor(selector) {
            const url = $('#lastUploadUrl').val();
            if (!url) {
                setMsg('Belum ada URL. Upload dulu.', true);
                return;
            }
            try {
                $(selector).summernote('editor.insertImage', url, img => img.attr('alt', 'uploaded'));
                setMsg('Gambar dimasukkan ke editor.');
            } catch (e) {
                $(selector).summernote('editor.insertText', url);
                setMsg('Editor tidak menerima image, URL disisipkan sebagai teks.');
            }
        }
        $(document).on('click', '#insertToEditor1', () => insertImageToEditor('#my-editor'));
        $(document).on('click', '#insertToEditor2', () => insertImageToEditor('#my-editor2'));
    </script>
    <script>
        // Tombol X
        $(document).on('click', '#uploadClose', function() {
            showResultPanel(false);
        });

        // Klik di luar panel => close
        $(document).on('mousedown', function(e) {
            const $panel = $('#uploadResult');
            if (!$panel.is(':visible')) return;

            const clickedInsidePanel = $panel.is(e.target) || $panel.has(e.target).length > 0;
            const $fab = $('.fab-upload, #fabInput, label[for="fabInput"]');
            const clickedFab = $fab.is(e.target) || $fab.has(e.target).length > 0;

            if (!clickedInsidePanel && !clickedFab) {
                showResultPanel(false);
            }
        });

        // ESC untuk close
        $(document).on('keyup', function(e) {
            if (e.key === 'Escape') {
                showResultPanel(false);
            }
        });

        // Setelah insert ke editor, auto-hide 1.2s
        function insertImageToEditor(selector) {
            const url = $('#lastUploadUrl').val();
            if (!url) {
                setMsg('Belum ada URL. Upload dulu.', true);
                return;
            }
            try {
                $(selector).summernote('editor.insertImage', url, img => img.attr('alt', 'uploaded'));
                setMsg('Gambar dimasukkan ke editor.');
            } catch (e) {
                $(selector).summernote('editor.insertText', url);
                setMsg('Editor tidak menerima image, URL disisipkan sebagai teks.');
            }
            setTimeout(() => showResultPanel(false), 1200);
        }
    </script>

@endsection

@push('top')
    <style>
        .fab-upload {
            position: fixed;
            right: 20px;
            bottom: 24px;
            width: 54px;
            height: 54px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #00537a;
            color: #fff;
            cursor: pointer;
            z-index: 9999;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .18);
        }

        .fab-upload:hover {
            filter: brightness(1.05);
        }

        .fab-upload i {
            font-size: 20px;
        }

        .upload-result {
            position: fixed;
            right: 20px;
            bottom: 90px;
            width: min(520px, calc(100% - 40px));
            background: #fff;
            border-radius: 10px;
            padding: 10px 12px;
            z-index: 9999;
            border: 1px solid rgba(0, 0, 0, .08);
        }

        @media(max-width:480px) {
            .upload-result .btn {
                padding: .25rem .5rem;
            }
        }

        .upload-result {
            position: fixed;
            right: 20px;
            bottom: 90px;
            /* sisanya sudah ada */
        }

        .upload-close {
            position: absolute;
            top: 6px;
            right: 8px;
            border: 0;
            background: transparent;
            font-size: 22px;
            line-height: 1;
            color: #889;
            cursor: pointer;
        }

        .upload-close:hover {
            color: #333;
        }
    </style>
@endpush

@push('bottom')
@endpush
