@extends('layouts.inspire.master')
@section('content-title', 'Edit News')
@section('content')
    {{-- CSRF token untuk Ajax (pastikan juga ada di master layout) --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ Route('news') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Edit News</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ Route('news') }}">Berita Management</a></div>
                <div class="breadcrumb-item active"><a href="">Edit News</a></div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">Edit News</h2>
            <div class="row">
                <div class="col-lg-8">

                    {{-- Card Keterangan Berita --}}
                    <div class="card">
                        <div class="card-header">
                            <h4>Keterangan Berita</h4>
                        </div>
                        <div class="card-body">
                            {!! Form::model($news, [
                                'route' => ['news.update', $news->id],
                                'method' => 'PATCH',
                                'enctype' => 'multipart/form-data',
                            ]) !!}

                            {{-- Judul Berita --}}
                            <div class="form-group{{ $errors->has('title') ? ' has-error' : '' }}">
                                {!! Form::label('Judul Berita') !!}
                                {!! Form::text('title', null, ['class' => 'form-control', 'placeholder' => 'Judul Berita']) !!}
                                @if ($errors->has('title'))
                                    <span class="help-block"><strong
                                            style="color:red">{{ $errors->first('title') }}</strong></span>
                                @endif
                            </div>

                            {{-- Deskripsi 1 (desc) --}}
                            <div class="form-group{{ $errors->has('desc') ? ' has-error' : '' }}">
                                {!! Form::label('Deskripsi') !!}
                                {!! Form::textarea('desc', null, [
                                    'id' => 'my-editor',
                                    'class' => 'form-control',
                                    'placeholder' => 'Berita',
                                ]) !!}
                                @if ($errors->has('desc'))
                                    <span class="help-block"><strong
                                            style="color:red">{{ $errors->first('desc') }}</strong></span>
                                @endif
                            </div>

                            {{-- Deskripsi 2 (desc2) --}}
                            <div class="form-group{{ $errors->has('desc2') ? ' has-error' : '' }}">
                                {!! Form::label('Deskripsi 2') !!}
                                {!! Form::textarea('desc2', null, [
                                    'id' => 'my-editor2',
                                    'class' => 'form-control',
                                    'placeholder' => 'Berita Bagian 2',
                                ]) !!}
                                @if ($errors->has('desc2'))
                                    <span class="help-block"><strong
                                            style="color:red">{{ $errors->first('desc2') }}</strong></span>
                                @endif
                            </div>

                            {{-- Reference Image --}}
                            <div class="form-group{{ $errors->has('reference_image') ? ' has-error' : '' }}">
                                <label>Reference Image</label>
                                {!! Form::textarea('reference_image', null, [
                                    'cols' => '30',
                                    'rows' => '3',
                                    'class' => 'form-control',
                                    'placeholder' => 'Image Reference',
                                ]) !!}
                                @if ($errors->has('reference_image'))
                                    <span class="help-block"><strong
                                            style="color:red">{{ $errors->first('reference_image') }}</strong></span>
                                @endif
                            </div>

                            {{-- Reference Link --}}
                            <div class="form-group{{ $errors->has('reference_link') ? ' has-error' : '' }}">
                                <label>Reference Link</label>
                                {!! Form::textarea('reference_link', null, [
                                    'cols' => '30',
                                    'rows' => '3',
                                    'class' => 'form-control',
                                    'placeholder' => 'Link Reference',
                                ]) !!}
                                @if ($errors->has('reference_link'))
                                    <span class="help-block"><strong
                                            style="color:red">{{ $errors->first('reference_link') }}</strong></span>
                                @endif
                            </div>

                        </div>{{-- card-body --}}
                    </div>{{-- card --}}

                    {{-- Card Featured Image --}}
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
                                        <label class="custom-file-label">Choose file</label>
                                    </div>
                                </div>
                                @if ($errors->has('image'))
                                    <span class="help-block"><strong
                                            style="color:red">{{ $errors->first('image') }}</strong></span>
                                @endif

                                @if (!empty($news->image))
                                    <img src="{{ asset($news->image) }}" alt="Current Image"
                                        style="margin-top:15px;max-height:100px;">
                                @endif

                                <img id="holder" style="margin-top:15px;max-height:100px;">
                            </div>
                        </div>
                    </div>

                </div>{{-- col-lg-8 --}}

                <div class="col-lg-4">
                    {{-- Card Publish --}}
                    <div class="card">
                        <div class="card-header">
                            <h4>Publish</h4>
                        </div>
                        <div class="card-body">

                            {{-- ✅ NEW: Type --}}
                            <div class="form-group{{ $errors->has('type') ? ' has-error' : '' }}">
                                {!! Form::label('Type *') !!}
                                {!! Form::select(
                                    'type',
                                    [
                                        'default' => 'Default News',
                                        'partnership' => 'Partnership',
                                        'sponsor' => 'Sponsor (Brochure)',
                                    ],
                                    old('type', $news->type ?? 'default'),
                                    [
                                        'class' => 'form-control',
                                        'id' => 'newsType',
                                    ],
                                ) !!}
                                @if ($errors->has('type'))
                                    <span class="help-block"><strong
                                            style="color:red">{{ $errors->first('type') }}</strong></span>
                                @endif
                            </div>

                            {{-- ✅ NEW: Partner dropdown (muncul kalau type=partnership) --}}
                            <div id="partnerWrap" style="display:none;">
                                <div class="form-group{{ $errors->has('news_partners_id') ? ' has-error' : '' }}">
                                    {!! Form::label('Partner *') !!}
                                    <div class="d-flex" style="gap:8px;">
                                        <select name="news_partners_id" id="partnerSelect" class="form-control select2"
                                            style="width:100%;">
                                            <option value="">-- Select Partner --</option>
                                            @if (isset($partners))
                                                @foreach ($partners as $p)
                                                    @php
                                                        $pName = $p->partner_name ?? ($p->name ?? '-');
                                                        $pCompany = $p->partner_company ?? ($p->company ?? '');
                                                    @endphp
                                                    <option value="{{ $p->id }}"
                                                        {{ old('news_partners_id', $news->news_partners_id ?? '') == $p->id ? 'selected' : '' }}>
                                                        {{ $pName }}{{ !empty($pCompany) ? ' - ' . $pCompany : '' }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>

                                        <button type="button" class="btn btn-outline-primary" id="btnCreatePartner">
                                            + Partner
                                        </button>
                                    </div>

                                    @if ($errors->has('news_partners_id'))
                                        <span class="help-block"><strong
                                                style="color:red">{{ $errors->first('news_partners_id') }}</strong></span>
                                    @endif
                                </div>
                                <small class="text-muted d-block mt-1">
                                    Jika partner belum ada, klik <b>+ Partner</b> untuk buat baru.
                                </small>
                            </div>

                            {{-- ✅ NEW: Sponsor dropdown (muncul kalau type=sponsor) --}}
                            <div id="sponsorWrap" style="display:none;">
                                <div class="form-group{{ $errors->has('sponsors_id') ? ' has-error' : '' }}">
                                    {!! Form::label('Sponsor *') !!}
                                    <select name="sponsors_id" id="sponsorSelect" class="form-control select2"
                                        style="width:100%;">
                                        <option value="">-- Select Sponsor --</option>
                                        @if (isset($sponsors))
                                            @foreach ($sponsors as $s)
                                                <option value="{{ $s->id }}"
                                                    {{ old('sponsors_id', $news->sponsors_id ?? '') == $s->id ? 'selected' : '' }}>
                                                    {{ $s->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>

                                    @if ($errors->has('sponsors_id'))
                                        <span class="help-block"><strong
                                                style="color:red">{{ $errors->first('sponsors_id') }}</strong></span>
                                    @endif
                                </div>
                                <small class="text-muted d-block mt-1">
                                    Pilih sponsor untuk tipe <b>Sponsor (Brochure)</b>.
                                </small>
                            </div>

                            {{-- Date News --}}
                            <div class="form-group{{ $errors->has('date_news') ? ' has-error' : '' }}">
                                {!! Form::label('Date News *') !!}
                                <div class="input-group date">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text"><i class="fas fa-calendar"></i></div>
                                    </div>
                                    {!! Form::text('date_news', null, ['class' => 'form-control datepicker', 'placeholder' => 'Tanggal Mulai']) !!}
                                </div>
                                @if ($errors->has('date_news'))
                                    <span class="help-block"><strong
                                            style="color:red">{{ $errors->first('date_news') }}</strong></span>
                                @endif
                            </div>

                            {{-- Status --}}
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

                    {!! Form::close() !!}
                </div>{{-- col-lg-4 --}}
            </div>{{-- row --}}
        </div>{{-- section-body --}}
    </section>

    {{-- ===========================
        ✅ NEW: Modal Create Partner
    ============================ --}}
    <div class="modal fade" id="partnerModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Partner</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Name *</label>
                        <input type="text" class="form-control" id="p_name">
                    </div>
                    <div class="form-group">
                        <label>Position</label>
                        <input type="text" class="form-control" id="p_position">
                    </div>
                    <div class="form-group">
                        <label>Company</label>
                        <input type="text" class="form-control" id="p_company">
                    </div>
                    <div class="form-group">
                        <label>Website</label>
                        <input type="text" class="form-control" id="p_website">
                    </div>

                    <div class="form-group">
                        <label>Image (upload → URL)</label>
                        <div class="d-flex" style="gap:8px;">
                            <input type="text" class="form-control" id="p_image"
                                placeholder="Auto filled after upload" readonly>
                            <input type="file" id="p_image_file" hidden accept="image/*">
                            <button class="btn btn-outline-primary" type="button"
                                id="btnUploadPartnerImage">Upload</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Quote</label>
                        <input type="text" class="form-control" id="p_quote">
                    </div>

                    <small class="text-danger d-none" id="partnerErr"></small>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btnSavePartner">Save Partner</button>
                </div>
            </div>
        </div>
    </div>


    {{-- Summernote & bsCustomFileInput --}}
    <script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
    <script src="{{ asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>

    <script>
        $(function() {
            bsCustomFileInput.init();

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

            $(document).on('shown.bs.modal', '.note-image-dialog', function() {
                $(this).find('.note-group-select-from-files').remove();
            });

            // ✅ NEW: Toggle partner & sponsor dropdown based on type
            function toggleTypeWrap() {
                const type = $('#newsType').val();

                if (type === 'partnership') {
                    $('#partnerWrap').slideDown(120);
                } else {
                    $('#partnerWrap').slideUp(120);
                    // optional clear:
                    // $('#partnerSelect').val('').trigger('change');
                }

                if (type === 'sponsor') {
                    $('#sponsorWrap').slideDown(120);
                } else {
                    $('#sponsorWrap').slideUp(120);
                    // optional clear:
                    // $('#sponsorSelect').val('').trigger('change');
                }
            }

            toggleTypeWrap();
            $(document).on('change', '#newsType', toggleTypeWrap);
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
            <button type="button" id="insertToEditor2" class="btn btn-outline-primary btn-sm">Insert ke Deskripsi
                2</button>
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

    {{-- ===========================
        ✅ NEW: Partner Modal JS (AJAX create + upload)
        (tidak mengganggu FAB upload yang sudah ada)
    ============================ --}}
    <script>
        const PARTNER_STORE_URL = "{{ route('news.partners.ajaxStore') }}";
        const PARTNER_UPLOAD_URL = "{{ route('ajax.image.upload') }}";

        // open modal
        $(document).on('click', '#btnCreatePartner', function() {
            $('#partnerErr').addClass('d-none').text('');
            $('#p_name,#p_position,#p_company,#p_website,#p_image,#p_quote').val('');
            $('#partnerModal').modal('show');
        });

        // upload image partner
        $(document).on('click', '#btnUploadPartnerImage', function() {
            $('#p_image_file').click();
        });

        $(document).on('change', '#p_image_file', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const fd = new FormData();
            fd.append('image', file);

            $.ajax({
                url: PARTNER_UPLOAD_URL,
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                timeout: 120000,
                success: function(res) {
                    const url = res?.url || res?.data?.url;
                    if (url) $('#p_image').val(url);
                },
                error: function(xhr) {
                    const msg = xhr?.responseJSON?.message || 'Gagal upload image partner.';
                    $('#partnerErr').removeClass('d-none').text(msg);
                },
                complete: function() {
                    $('#p_image_file').val('');
                }
            });
        });

        // save partner ajax => append option => select
        $(document).on('click', '#btnSavePartner', function() {
            const payload = {
                partner_name: $('#p_name').val(),
                partner_position: $('#p_position').val(),
                partner_company: $('#p_company').val(),
                partner_website: $('#p_website').val(),
                partner_image: $('#p_image').val(),
                partner_quote: $('#p_quote').val(),
            };

            $.ajax({
                url: PARTNER_STORE_URL,
                method: 'POST',
                data: payload,
                success: function(res) {
                    if (res?.success && res?.partner?.id) {
                        const id = res.partner.id;
                        const text = res.partner.text || ('Partner #' + id);

                        if ($('#partnerSelect option[value="' + id + '"]').length === 0) {
                            $('#partnerSelect').append(new Option(text, id, true, true));
                        }
                        $('#partnerSelect').val(id).trigger('change');

                        $('#partnerModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    let msg = xhr?.responseJSON?.message || 'Failed to create partner.';
                    if (xhr?.responseJSON?.errors) {
                        const errors = xhr.responseJSON.errors;
                        const firstKey = Object.keys(errors)[0];
                        if (firstKey && errors[firstKey]?.[0]) msg = errors[firstKey][0];
                    }
                    $('#partnerErr').removeClass('d-none').text(msg);
                }
            });
        });
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
