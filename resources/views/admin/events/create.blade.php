@extends('layouts.inspire.master')
@section('content-title', 'Add event')
@section('content')
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ Route('events') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Add event</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ Route('events') }}">Event Management</a></div>
                <div class="breadcrumb-item active"><a href="">Add event</a></div>
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
                                'route' => 'events.store',
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
                            <div class="form-group{{ $errors->has('location') ? ' has-error' : '' }}">
                                {!! Form::label('Location *') !!}
                                {!! Form::text('location', old('title'), ['class' => 'form-control', 'placeholder' => 'Tempat Kegiatan']) !!}
                                @if ($errors->has('location'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('location') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="form-group{{ $errors->has('maps') ? ' has-error' : '' }}">
                                {!! Form::label('Google Maps (Link) *') !!}
                                {!! Form::text('maps', old('title'), ['class' => 'form-control', 'placeholder' => 'Tempat Kegiatan']) !!}
                                @if ($errors->has('maps'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('maps') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">
                                {!! Form::label('Deskripsi *') !!}
                                {!! Form::textarea('description', old('description'), [
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
                                {!! Form::label('Tipe') !!}
                                {!! Form::select(
                                    'type',
                                    [
                                        null => 'Type Select',
                                        'Live' => 'Live Event',
                                        'Virtual' => 'Virtual/Online',
                                        'Hybrid' => 'Hybrid',
                                        'Networking Dinner' => 'Networking Dinner',
                                    ],
                                    null,
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
                                    null,
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
                                {!! Form::text('link', old('title'), ['class' => 'form-control', 'placeholder' => 'Link Registration']) !!}
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
                                    null,
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
                                {!! Form::label('Image Kotak *') !!}
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="thumbnail" name="image">
                                        <label class="custom-file-label" for="exampleInputFile">Choose
                                            file</label>
                                    </div>
                                </div>
                                @if ($errors->has('image'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('image') }}</strong>
                                    </span>
                                @endif
                                <img id="holder" style="margin-top:15px;max-height:100px;">
                            </div>
                            <div class="form-group{{ $errors->has('image') ? ' has-error' : '' }}">
                                {!! Form::label('Image Banner *') !!}
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="" name="image_banner">
                                        <label class="custom-file-label" for="exampleInputFile">Choose
                                            file</label>
                                    </div>
                                </div>
                                @if ($errors->has('image_banner'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('image_banner') }}</strong>
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
                                    <div class="form-group{{ $errors->has('end_date') ? ' has-error' : '' }}">
                                        {!! Form::label('Tanggal Selesai *') !!}
                                        <div class="input-group date">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fas fa-calendar"></i>
                                                </div>
                                            </div>
                                            {!! Form::text('end_date', date('Y-m-d'), [
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
                                        {!! Form::select('status', ['draft' => 'Draft', 'publish' => 'Publish'], null, ['class' => 'form-control']) !!}
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
                                        <button type="submit" class="btn btn-primary">Simpan</button>
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
                                    <div class="form-group{{ $errors->has('category_id') ? ' has-error' : '' }}">
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

@endsection
