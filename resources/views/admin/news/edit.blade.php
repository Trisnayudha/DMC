@extends('layouts.inspire.master')
@section('content-title', 'Edit News')
@section('content')
    {{-- CSRF token untuk Ajax --}}
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
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('title') }}</strong>
                                    </span>
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
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('desc') }}</strong>
                                    </span>
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
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('desc2') }}</strong>
                                    </span>
                                @endif
                            </div>

                            {{-- Reference Image --}}
                            <div class="form-group{{ $errors->has('reference_image') ? ' has-error' : '' }}">
                                <label>Reference image</label>
                                {!! Form::textarea('reference_image', null, [
                                    'cols' => '30',
                                    'rows' => '3',
                                    'class' => 'form-control',
                                    'placeholder' => 'Image Reference',
                                ]) !!}
                                @if ($errors->has('reference_image'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('reference_image') }}</strong>
                                    </span>
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
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('reference_link') }}</strong>
                                    </span>
                                @endif
                            </div>

                        </div> {{-- card-body --}}
                    </div> {{-- card --}}

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
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('image') }}</strong>
                                    </span>
                                @endif

                                @if (!empty($news->image))
                                    <img src="{{ asset($news->image) }}" alt="Current Image"
                                        style="margin-top:15px;max-height:100px;">
                                @endif

                                <img id="holder" style="margin-top:15px;max-height:100px;">
                            </div>
                        </div>
                    </div>

                </div> {{-- col-lg-8 --}}

                <div class="col-lg-4">
                    {{-- Card Publish --}}
                    <div class="card">
                        <div class="card-header">
                            <h4>Publish</h4>
                        </div>
                        <div class="card-body">
                            {{-- Date News --}}
                            <div class="form-group{{ $errors->has('date_news') ? ' has-error' : '' }}">
                                {!! Form::label('Date News *') !!}
                                <div class="input-group date">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <i class="fas fa-calendar"></i>
                                        </div>
                                    </div>
                                    {!! Form::text('date_news', null, [
                                        'class' => 'form-control datepicker',
                                        'placeholder' => 'Tanggal Mulai',
                                    ]) !!}
                                </div>
                                @if ($errors->has('date_news'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('date_news') }}</strong>
                                    </span>
                                @endif
                            </div>

                            {{-- Status --}}
                            <div class="form-group{{ $errors->has('status') ? ' has-error' : '' }}">
                                {!! Form::label('Status') !!}
                                {!! Form::select('status', ['draft' => 'Draft', 'publish' => 'Publish'], null, [
                                    'class' => 'form-control',
                                ]) !!}
                                @if ($errors->has('status'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('status') }}</strong>
                                    </span>
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
                </div> {{-- col-lg-4 --}}
            </div> {{-- row --}}
        </div> {{-- section-body --}}
    </section>

    {{-- Summernote & bsCustomFileInput Scripts --}}
    <script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
    <script src="{{ asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>

    <script>
        $(function() {
            bsCustomFileInput.init();

            // Reusable init untuk dua editor
            function initEditor(selector) {
                $(selector).summernote({
                    dialogsInBody: true,
                    minHeight: 180,
                    toolbar: [
                        ['style', ['bold', 'italic', 'underline', 'clear']],
                        ['font', ['strikethrough']],
                        ['insert', ['link', 'picture', 'video']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['view', ['undo', 'redo', 'codeview']]
                    ],
                    callbacks: {
                        // Blokir paste image base64 dari clipboard (biar tidak bengkak)
                        onPaste: function(e) {
                            const cb = (e.originalEvent || e).clipboardData;
                            if (cb && [...cb.items].some(it => it.type && it.type.indexOf('image') ===
                                    0)) {
                                e.preventDefault();
                                alert(
                                    'Jangan paste gambar langsung. Gunakan tombol "Insert Picture" untuk upload.');
                            }
                        },
                        // Ketika user pilih gambar lewat dialog Summernote
                        onImageUpload: function(files) {
                            for (let i = 0; i < files.length; i++) {
                                uploadImage(files[i], selector);
                            }
                        },
                        // (Opsional) hapus file di server saat img dihapus dari editor
                        onMediaDelete: function($target) {
                            const src = $target.attr('src') || '';
                            if (!src) return;
                            $.ajax({
                                url: '{{ route('editor.delete') }}', // opsional, buat route ini jika mau
                                method: 'POST',
                                data: {
                                    src: src,
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                }
                            });
                        }
                    }
                });
            }

            // Init untuk desc & desc2
            initEditor('#my-editor');
            initEditor('#my-editor2');

            // Fungsi upload ke server lalu sisipkan URL ke editor
            function uploadImage(file, editorSelector) {
                const data = new FormData();
                data.append('file', file);

                $.ajax({
                    url: '{{ route('editor.upload') }}', // pastikan route ini ada
                    type: 'POST',
                    data: data,
                    contentType: false,
                    processData: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        if (res && res.url) {
                            $(editorSelector).summernote('insertImage', res.url);
                        } else {
                            alert('Upload berhasil tapi respons tidak berisi URL.');
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.message || xhr.statusText || 'Unknown error';
                        alert('Upload gagal: ' + msg);
                    }
                });
            }
        });
    </script>
@endsection
