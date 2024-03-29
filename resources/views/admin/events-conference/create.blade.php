@extends('layouts.inspire.master')
@section('content-title', 'Add event')
@section('content')
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ Route('events') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Add Event Conference</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ Route('events.conference') }}">Event Conference Management</a></div>
                <div class="breadcrumb-item active"><a href="">Add Event Conference</a></div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">
                Add event
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
                                'url' => '/admin/events-conference/addcategory',
                                'enctype' => 'multipart/form-data',
                            ]) !!}
                            <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                {!! Form::label('Name Event *') !!}
                                {!! Form::text('name', old('title'), ['class' => 'form-control', 'placeholder' => 'Nama Program']) !!}
                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="form-group{{ $errors->has('events_id') ? ' has-error' : '' }}">
                                {!! Form::label('Events') !!}
                                {!! Form::select('events_id', $events->pluck('name', 'id'), null, ['class' => 'form-control']) !!}
                                @if ($errors->has('events_id'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('events_id') }}</strong>
                                    </span>
                                @endif

                            </div>
                            <div class="form-group{{ $errors->has('link') ? ' has-error' : '' }}">
                                {!! Form::label('Link Youtube') !!}
                                {!! Form::text('link', old('link'), ['class' => 'form-control', 'placeholder' => 'LINK']) !!}
                                @if ($errors->has('link'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('link') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4>Featured Upload</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group{{ $errors->has('image') ? ' has-error' : '' }}">
                                {!! Form::label('Thumbnails *') !!}
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="thumbnail" name="image">
                                        <label class="custom-file-label" for="exampleInputFile">Choose
                                            file</label>
                                    </div>
                                </div>
                                <small>
                                    <i>Recommend Image MAX 1MB</i>
                                </small>
                                @if ($errors->has('image'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('image') }}</strong>
                                    </span>
                                @endif
                                <img id="holder" style="margin-top:15px;max-height:100px;">
                            </div>
                            <div class="form-group{{ $errors->has('file') ? ' has-error' : '' }}">
                                {!! Form::label('File Presentation *') !!}
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="file" name="file">
                                        <label class="custom-file-label" for="exampleInputFile">Choose
                                            file</label>
                                    </div>
                                </div>
                                <small>
                                    <i>Recommend file MAX 2MB</i>
                                </small>
                                @if ($errors->has('file'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('file') }}</strong>
                                    </span>
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
                                    <h4>Publish</h4>
                                </div>
                                <div class="card-body">
                                    <div class="form-group{{ $errors->has('start_date') ? ' has-error' : '' }}">
                                        {!! Form::label('Tanggal Mulai *') !!}
                                        <div class="input-group date">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fas fa-calendar"></i>
                                                </div>
                                            </div>
                                            {!! Form::text('start_date', date('Y-m-d'), [
                                                'class' => 'form-control
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    datepicker',
                                                'placeholder' => 'Tanggal Mulai',
                                            ]) !!}
                                        </div>
                                        @if ($errors->has('start_date'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('start_date') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group{{ $errors->has('start_time') ? ' has-error' : '' }}">
                                        {!! Form::label('Waktu Mulai *') !!}
                                        <div class="input-group clockpicker" data-autoclose="true">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                            </div>
                                            {!! Form::text('start_time', date('H:i'), ['class' => 'form-control ', 'placeholder' => 'Waktu Mulai']) !!}
                                            <span class="input-group-addon">
                                                <span class="fa fa-clock-o"></span>
                                            </span>
                                        </div>
                                        @if ($errors->has('start_time'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('start_time') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group{{ $errors->has('start_time') ? ' has-error' : '' }}">
                                        {!! Form::label('Waktu Selesai *') !!}
                                        <div class="input-group clockpicker" data-autoclose="true">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                            </div>
                                            {!! Form::text('end_time', date('H:i'), [
                                                'class' => 'form-control ',
                                                'placeholder' => 'Waktu
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    Selesai',
                                            ]) !!}
                                            <span class="input-group-addon">
                                                <span class="fa fa-clock-o"></span>
                                            </span>
                                        </div>
                                        @if ($errors->has('start_time'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('start_time') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group{{ $errors->has('status') ? ' has-error' : '' }}">
                                        {!! Form::label('Status') !!}
                                        {!! Form::select('status', ['draft' => 'Draft', 'record' => 'Record'], null, ['class' => 'form-control']) !!}
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


    <script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
    <script src="{{ asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
    <script>
        $(function() {
            $('#summernote').summernote()
            bsCustomFileInput.init();
        });
    </script>
    <script>
        $(function() {
            $('.my-editor').summernote({
                dialogsInBody: true,
                minHeight: 150,
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear', 'link', 'picture', 'video',
                        'undo'
                    ]],
                    ['font', ['strikethrough']],
                    ['para', ['paragraph']]
                ]
            })
        });
    </script>
    <script>
        // Fungsi untuk memvalidasi input file Thumbnail
        document.getElementById("thumbnail").addEventListener("change", function() {
            var fileInput = this;
            var selectedFile = fileInput.files[0];
            if (selectedFile) {
                if (!isImageFile(selectedFile)) {
                    alert("Please select an image file (e.g., JPG, PNG).");
                    fileInput.value = ""; // Mengosongkan input jika file tidak sesuai
                } else if (selectedFile.size > 1048576) { // Ukuran maksimum 1MB (1MB = 1048576 bytes)
                    alert("Image size should be less than 1MB.");
                    fileInput.value = ""; // Mengosongkan input jika ukuran melebihi batas
                }
            }
        });

        // Fungsi untuk memvalidasi input file File Presentation
        document.getElementById("file").addEventListener("change", function() {
            var fileInput = this;
            var selectedFile = fileInput.files[0];
            if (selectedFile) {
                if (!isPresentationFile(selectedFile)) {
                    alert("Please select a PDF or PPT file.");
                    fileInput.value = ""; // Mengosongkan input jika file tidak sesuai
                } else if (selectedFile.size > 2097152) { // Ukuran maksimum 2MB (2MB = 2097152 bytes)
                    alert("File size should be less than 2MB.");
                    fileInput.value = ""; // Mengosongkan input jika ukuran melebihi batas
                }
            }
        });

        // Fungsi untuk memeriksa apakah file adalah gambar
        function isImageFile(file) {
            return file.type.startsWith("image/");
        }

        // Fungsi untuk memeriksa apakah file adalah PDF atau PPT
        function isPresentationFile(file) {
            return file.type === "application/pdf" || file.type === "application/vnd.ms-powerpoint" || file.type ===
                "application/vnd.openxmlformats-officedocument.presentationml.presentation";
        }
    </script>
    <script>
        // Fungsi untuk memvalidasi input file Thumbnail
        document.getElementById("thumbnail").addEventListener("change", function() {
            var fileInput = this;
            var selectedFile = fileInput.files[0];
            if (selectedFile) {
                if (!isImageFile(selectedFile)) {
                    alert("Please select an image file (e.g., JPG, PNG).");
                    fileInput.value = ""; // Mengosongkan input jika file tidak sesuai
                } else if (selectedFile.size > 1048576) { // Ukuran maksimum 1MB (1MB = 1048576 bytes)
                    alert("Image size should be less than 1MB.");
                    fileInput.value = ""; // Mengosongkan input jika ukuran melebihi batas
                }
            }
        });

        // Fungsi untuk memvalidasi input file File Presentation
        document.getElementById("file").addEventListener("change", function() {
            var fileInput = this;
            var selectedFile = fileInput.files[0];
            if (selectedFile) {
                if (!isPresentationFile(selectedFile)) {
                    alert("Please select a PDF or PPT file.");
                    fileInput.value = ""; // Mengosongkan input jika file tidak sesuai
                } else if (selectedFile.size > 2097152) { // Ukuran maksimum 2MB (2MB = 2097152 bytes)
                    alert("File size should be less than 2MB.");
                    fileInput.value = ""; // Mengosongkan input jika ukuran melebihi batas
                }
            }
        });

        // Fungsi untuk memeriksa apakah file adalah gambar
        function isImageFile(file) {
            return file.type.startsWith("image/");
        }

        // Fungsi untuk memeriksa apakah file adalah PDF atau PPT
        function isPresentationFile(file) {
            return file.type === "application/pdf" || file.type === "application/vnd.ms-powerpoint" || file.type ===
                "application/vnd.openxmlformats-officedocument.presentationml.presentation";
        }
    </script>



@endsection
