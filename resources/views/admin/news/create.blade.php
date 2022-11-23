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
            <h2 class="section-title">
                Add News
            </h2>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4>
                                Keterangan Berita
                            </h4>
                        </div>
                        <div class="card-body">
                            {!! Form::open([
                                'method' => 'POST',
                                'route' => 'news.store',
                                'enctype' => 'multipart/form-data',
                            ]) !!}
                            <div class="form-group{{ $errors->has('title') ? ' has-error' : '' }}">
                                {!! Form::label('Judul Berita') !!}
                                {!! Form::text('title', old('title'), ['class' => 'form-control', 'placeholder' => 'Judul Berita']) !!}
                                @if ($errors->has('title'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('title') }}</strong>
                                    </span>
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
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('description') }}</strong>
                                    </span>
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
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('reference_link') }}</strong>
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
                                {!! Form::label('Thumbnails') !!}
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="image" name="image">
                                        <label class="custom-file-label" for="exampleInputFile">Choose
                                            file</label>
                                    </div>
                                </div>
                                @if ($errors->has('image'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('thumb') }}</strong>
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
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('category_id') }}</strong>
                                            </span>
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
                                                <div class="input-group-text">
                                                    <i class="fas fa-calendar"></i>
                                                </div>
                                            </div>
                                            {!! Form::text('date_news', date('Y-m-d H:i'), [
                                                'class' => 'form-control
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            datepicker',
                                                'placeholder' => 'Tanggal Mulai',
                                            ]) !!}
                                        </div>
                                        @if ($errors->has('date_news'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('date_news') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                {{-- <div class="card-body">
                                    <div class="form-group{{ $errors->has('status') ? ' has-error' : '' }}">
                                        {!! Form::label('Status') !!}
                                        {!! Form::select('status', ['draft' => 'Draft', 'publish' => 'Publish'], null, ['class' => 'form-control']) !!}
                                        @if ($errors->has('status'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('status') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div> --}}
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
            $('#my-editor').summernote({
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
