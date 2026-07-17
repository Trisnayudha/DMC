@extends('layouts.inspire.master')
@section('content-title', 'Edit event')
@section('content')
    {{-- CSRF token untuk Ajax (pastikan juga ada di master layout) --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ Route('events') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Edit event</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ Route('events') }}">Event Management</a></div>
                <div class="breadcrumb-item active"><a href="">Edit event</a></div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">
                Edit event
            </h2>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4>
                                Keterangan event
                            </h4>
                        </div>
                        <div class="card-body">
                            {!! Form::open([
                                'method' => 'POST',
                                'route' => ['events.update'],
                                'enctype' => 'multipart/form-data',
                            ]) !!}
                            <input type="hidden" name="id" id="id" value="{{ $data->id }}">
                            <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                {!! Form::label('Name Event *') !!}
                                {!! Form::text('name', $data->name, ['class' => 'form-control', 'placeholder' => 'Nama Program']) !!}
                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="form-group{{ $errors->has('topic') ? ' has-error' : '' }}">
                                {!! Form::label('Topic (untuk share link)') !!}
                                {!! Form::text('topic', $data->topic, ['class' => 'form-control', 'placeholder' => 'Contoh: Ambition to Action']) !!}
                                <small class="form-text text-muted">Dipakai pada share link, contoh: /events/2026/2/ambition-to-action</small>
                                @if ($errors->has('topic'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('topic') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="form-group{{ $errors->has('location') ? ' has-error' : '' }}">
                                {!! Form::label('Location *') !!}
                                {!! Form::text('location', $data->location, ['class' => 'form-control', 'placeholder' => 'Tempat Kegiatan']) !!}
                                @if ($errors->has('location'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('location') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="form-group{{ $errors->has('maps') ? ' has-error' : '' }}">
                                {!! Form::label('Google Maps (Link) *') !!}
                                {!! Form::text('maps', $data->maps, ['class' => 'form-control', 'placeholder' => 'Tempat Kegiatan']) !!}
                                @if ($errors->has('maps'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('maps') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">
                                {!! Form::label('Deskripsi *') !!}
                                {!! Form::textarea('description', $data->description, [
                                    'id' => 'my-editor',
                                    'class' => 'form-control my-editor',
                                    'placeholder' => 'Berita',
                                ]) !!}
                                @if ($errors->has('description'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('description') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="form-group{{ $errors->has('type') ? ' has-error' : '' }}">
                                {!! Form::label('Tipe event') !!}
                                {!! Form::select(
                                    'type',
                                    [
                                        null => 'Type Select',
                                        'Live' => 'Live Event',
                                        'Virtual' => 'Virtual/Online',
                                        'Hybrid' => 'Hybrid',
                                        'Networking Dinner' => 'Networking Dinner',
                                    ],
                                    $data->type,
                                    ['class' => 'form-control'],
                                ) !!}
                                @if ($errors->has('type'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('type') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="form-group{{ $errors->has('event_type') ? ' has-error' : '' }}">
                                {!! Form::label('Tipe event') !!}
                                {!! Form::select(
                                    'event_type',
                                    [
                                        null => 'Type Select',
                                        'DMC Event' => 'DMC Event',
                                        'DMC Partnership Event' => 'DMC Partnership Event',
                                        'Partnership Event' => 'Partnership Event',
                                    ],
                                    $data->event_type,
                                    ['class' => 'form-control'],
                                ) !!}
                                @if ($errors->has('event_type'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('event_type') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="form-group{{ $errors->has('link') ? ' has-error' : '' }}">
                                {!! Form::label('link diinput kalau tipe event (partnership event)') !!}
                                {!! Form::text('link', $data->link, ['class' => 'form-control', 'placeholder' => 'Link Registration']) !!}
                                @if ($errors->has('link'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('link') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="form-group{{ $errors->has('status_event') ? ' has-error' : '' }}">
                                {!! Form::label('Status Event') !!}
                                {!! Form::select(
                                    'status_event',
                                    [
                                        null => 'Status Select',
                                        'Free' => 'Free',
                                        'Paid' => 'Paid',
                                    ],
                                    $data->status_event,
                                    ['class' => 'form-control'],
                                ) !!}
                                @if ($errors->has('status_event'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('status_event') }}</strong>
                                    </span>
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
                                {!! Form::label('Thumbnails *') !!}
                                <img src="{{ asset($data->image) }}" class="img-preview img-fluid mb-3 col-sm-5 d-block">
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="thumbnail" name="image">
                                        <label class="custom-file-label" for="exampleInputFile">{{ $data->image }}</label>
                                    </div>

                                </div>
                                @if ($errors->has('image'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('image') }}</strong>
                                    </span>
                                @endif
                                <img id="holder" style="margin-top:15px;max-height:100px;">
                            </div>
                            <div class="form-group{{ $errors->has('image') ? ' has-error' : '' }}">
                                {!! Form::label('Image Banner *') !!}
                                <img src="{{ asset($data->image_banner) }}"
                                    class="img-preview img-fluid mb-3 col-sm-5 d-block">
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="" name="image_banner">
                                        <label class="custom-file-label"
                                            for="exampleInputFile">{{ $data->image_banner }}</label>
                                    </div>
                                </div>
                                @if ($errors->has('image_banner'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('image_banner') }}</strong>
                                    </span>
                                @endif
                                <img id="holder_2" style="margin-top:15px;max-height:100px;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Publish</h4>
                                </div>
                                <div class="card-body">
                                    <div class="form-group{{ $errors->has('start_time') ? ' has-error' : '' }}">
                                        {!! Form::label('Waktu Mulai *') !!}
                                        <div class="input-group clockpicker" data-autoclose="true">
                                            {!! Form::text('start_time', $data->start_time, ['class' => 'form-control ', 'placeholder' => 'Waktu Mulai']) !!}
                                            <span class="input-group-addon">
                                                <span class="fa fa-clock-o"></span>
                                            </span>
                                        </div>
                                        @if ($errors->has('start_time'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('start_time') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group{{ $errors->has('start_time') ? ' has-error' : '' }}">
                                        {!! Form::label('Waktu Selesai *') !!}
                                        <div class="input-group clockpicker" data-autoclose="true">
                                            {!! Form::text('end_time', $data->end_time, ['class' => 'form-control ', 'placeholder' => 'Waktu Selesai']) !!}
                                            <span class="input-group-addon">
                                                <span class="fa fa-clock-o"></span>
                                            </span>
                                        </div>
                                        @if ($errors->has('start_time'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('start_time') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group{{ $errors->has('start_date') ? ' has-error' : '' }}">
                                        {!! Form::label('Tanggal Mulai *') !!}
                                        <div class="input-group date">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fas fa-calendar"></i>
                                                </div>
                                            </div>
                                            {!! Form::text('start_date', date('Y/m/d', strtotime($data->start_date)), [
                                                'class' => 'form-control datepicker',
                                                'placeholder' => 'Tanggal Selesai',
                                            ]) !!}
                                        </div>
                                        @if ($errors->has('end_date'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('end_date') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group{{ $errors->has('end_date') ? ' has-error' : '' }}">
                                        {!! Form::label('Tanggal Selesai *') !!}
                                        <div class="input-group date">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fas fa-calendar"></i>
                                                </div>
                                            </div>
                                            {!! Form::text('end_date', date('Y/m/d', strtotime($data->end_date)), [
                                                'class' => 'form-control datepicker',
                                                'placeholder' => 'Tanggal Selesai',
                                            ]) !!}
                                        </div>
                                        @if ($errors->has('end_date'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('end_date') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group{{ $errors->has('status') ? ' has-error' : '' }}">
                                        {!! Form::label('Status') !!}
                                        {!! Form::select('status', ['draft' => 'Draft', 'publish' => 'Publish'], $data->status, [
                                            'class' => 'form-control',
                                        ]) !!}
                                        @if ($errors->has('status'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('status') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-footer text-right">
                                    <div class="pull-right">
                                        <a href="{{ route('events') }}" class="btn btn-warning">Close</a>
                                        <!-- <button type="button" id="previewProgram" class="btn btn-success">preview</button> -->
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Categories</h4>
                                </div>
                                <div class="card-body">
                                    {{-- <div class="form-group{{ $errors->has('category_id') ? ' has-error' : '' }}">
                                        {!! Form::label('Kategori *') !!}
                                        {!! Form::select('category_id[]', $categories->pluck('category_name', 'id'), null, [
                                            'multiple' => 'multiple',
                                            'class' => 'form-control select2',
                                        ]) !!}
                                        @if ($errors->has('category_id'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('category_id') }}</strong>
                                            </span>
                                        @endif
                                    </div> --}}
                                </div>
                            </div>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </section>


    {{-- Summernote & bsCustomFileInput --}}
    <script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
    <script src="{{ asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>

    <script>
        $(function() {
            bsCustomFileInput.init();

            // Custom button: spasi atas/bawah paragraf (margin)
            function makeSpacingButton(context) {
                const ui = $.summernote.ui;
                const opts = [
                    { label: 'None', val: '0' },
                    { label: 'Kecil (6px)', val: '6px' },
                    { label: 'Sedang (12px)', val: '12px' },
                    { label: 'Besar (24px)', val: '24px' }
                ];

                function applySpacing(val) {
                    context.invoke('editor.focus');
                    const rng = context.invoke('editor.createRange');
                    if (!rng) return;
                    let node = rng.sc;
                    if (node && node.nodeType === 3) node = node.parentNode; // text -> parent
                    let $block = $(node).closest('p,div,h1,h2,h3,h4,h5,h6,li,blockquote', context.layoutInfo.editable[0]);
                    if (!$block.length) {
                        document.execCommand('formatBlock', false, 'p');
                        const rng2 = context.invoke('editor.createRange');
                        let n2 = rng2 ? rng2.sc : null;
                        if (n2 && n2.nodeType === 3) n2 = n2.parentNode;
                        $block = $(n2).closest('p,div,h1,h2,h3,h4,h5,h6,li,blockquote', context.layoutInfo.editable[0]);
                    }
                    if (!$block.length) return;
                    $block.css({ marginTop: val, marginBottom: val });
                    context.invoke('editor.afterCommand');
                }

                const items = opts.map(function(o) {
                    return '<a class="dropdown-item" href="#" data-val="' + o.val + '">Spasi: ' + o.label + '</a>';
                }).join('');

                const button = ui.buttonGroup([
                    ui.button({
                        className: 'dropdown-toggle',
                        contents: '<i class="fas fa-arrows-alt-v"></i>',
                        tooltip: 'Spasi atas/bawah paragraf',
                        data: { toggle: 'dropdown' }
                    }),
                    ui.dropdown({
                        className: 'dropdown-menu',
                        contents: items,
                        callback: function($dropdown) {
                            $dropdown.find('a.dropdown-item').on('click', function(e) {
                                e.preventDefault();
                                applySpacing($(this).data('val'));
                            });
                        }
                    })
                ]);

                return button.render();
            }

            // Custom button: jadikan gambar terpilih sebagai link (popover gambar)
            function makeImageLinkButton(context) {
                const ui = $.summernote.ui;
                const $editable = context.layoutInfo.editable;

                // simpan gambar terakhir yang diklik di editor ini
                $editable.off('mousedown.imglink').on('mousedown.imglink', 'img', function() {
                    $editable.data('imglink-target', this);
                });

                const button = ui.button({
                    contents: '<i class="fas fa-link"></i>',
                    tooltip: 'Tambah / edit link pada gambar',
                    click: function() {
                        const img = $editable.data('imglink-target');
                        if (!img) return;
                        const $img = $(img);
                        const $parentA = $img.parent('a');
                        const current = $parentA.length ? $parentA.attr('href') : '';
                        const url = window.prompt(
                            'URL link untuk gambar (kosongkan untuk hapus link):',
                            current || 'https://'
                        );
                        if (url === null) return; // batal

                        context.invoke('editor.beforeCommand');
                        if (url.trim() === '') {
                            if ($parentA.length) $img.unwrap(); // hapus link
                        } else {
                            if ($parentA.length) {
                                $parentA.attr('href', url).attr('target', '_blank').attr('rel', 'noopener');
                            } else {
                                const a = document.createElement('a');
                                a.setAttribute('href', url);
                                a.setAttribute('target', '_blank');
                                a.setAttribute('rel', 'noopener');
                                $img.wrap(a);
                            }
                        }
                        context.invoke('editor.afterCommand');
                    }
                });
                return button.render();
            }

            $('#my-editor').summernote({
                dialogsInBody: true,
                minHeight: 250,
                buttons: {
                    spacing: makeSpacingButton,
                    imageLink: makeImageLinkButton
                },
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
                    ['fontname', ['fontname']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height', 'spacing']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video', 'hr']],
                    ['view', ['fullscreen', 'codeview', 'undo', 'redo', 'help']]
                ],
                popover: {
                    image: [
                        ['imagesize', ['imageSize100', 'imageSize50', 'imageSize25']],
                        ['float', ['floatLeft', 'floatRight', 'floatNone']],
                        ['link', ['imageLink']],
                        ['remove', ['removeMedia']]
                    ]
                }
            });

            $(document).on('shown.bs.modal', '.note-image-dialog', function() {
                $(this).find('.note-group-select-from-files').remove();
            });

            // Sinkronkan isi codeview ke textarea sebelum submit
            // (tanpa ini, save saat codeview masih terbuka mengirim isi lama)
            $('#my-editor').closest('form').on('submit', function() {
                var $ed = $('#my-editor');
                if ($ed.summernote('codeview.isActivated')) {
                    $ed.summernote('codeview.deactivate');
                }
                $ed.val($ed.summernote('code'));
            });
        });
    </script>

    {{-- =========== Floating Upload (AJAX) =========== --}}
    <!-- Input file dipisah dari FAB agar tidak terjadi bubbling loop -->
    <input type="file" id="fabInput" accept="image/*" hidden>

    <!-- FAB pakai label for -->
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
        </div>
        <small id="uploadMsg" class="text-muted d-block mt-1"></small>
    </div>


    <script>
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

        // Ajax CSRF
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

        $(document).on('click', '#insertToEditor1', () => insertImageToEditor('#my-editor'));
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
