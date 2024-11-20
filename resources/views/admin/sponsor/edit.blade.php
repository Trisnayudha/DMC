@extends('layouts.inspire.master')
@section('content-title', 'Tambah Program')
@section('content')
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ Route('news') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Create Sponsor</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ Route('news') }}">Sponsor Management</a></div>
                <div class="breadcrumb-item active"><a href="">Create Sponsor</a></div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">
                Create Sponsor
            </h2>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4>
                                Basic Information
                            </h4>
                        </div>
                        <div class="card-body">
                            {!! Form::open([
                                'method' => 'PUT',
                                'route' => ['sponsors.update', $sponsor->id], // Sesuaikan dengan rute yang sesuai
                                'enctype' => 'multipart/form-data',
                            ]) !!}
                            <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                {!! Form::label('Name') !!}
                                {!! Form::text('name', $sponsor->name, ['class' => 'form-control', 'placeholder' => 'Name']) !!}
                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-6">

                                    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                        {!! Form::label('Email') !!}
                                        {!! Form::text('email', $sponsor->email, ['class' => 'form-control', 'placeholder' => 'Email']) !!}
                                        @if ($errors->has('email'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('email') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group{{ $errors->has('founded') ? ' has-error' : '' }}">
                                        {!! Form::label('Founded') !!}
                                        <div class="input-group date">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fas fa-calendar"></i>
                                                </div>
                                            </div>
                                            {!! Form::text('founded', $sponsor->founded, [
                                                'class' => 'form-control datepicker',
                                                'placeholder' => 'Tanggal lahir company',
                                            ]) !!}

                                        </div>
                                        @if ($errors->has('founded'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('founded') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group{{ $errors->has('location_office') ? ' has-error' : '' }}">
                                        {!! Form::label('Location Office') !!}
                                        {!! Form::text('location_office', $sponsor->location_office, [
                                            'class' => 'form-control',
                                            'placeholder' => 'jumlah lokasi office (1/2/3/4/5 dst)',
                                        ]) !!}
                                        @if ($errors->has('location_office'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('location_office') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-6">
                                    <!-- Website -->
                                    <div class="form-group{{ $errors->has('website') ? ' has-error' : '' }}">
                                        {!! Form::label('Website') !!}
                                        {!! Form::text('website', $sponsor->company_website, ['class' => 'form-control', 'placeholder' => 'Website']) !!}
                                        @if ($errors->has('website'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('website') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group{{ $errors->has('video') ? ' has-error' : '' }}">
                                        <label>Video Youtube</label>
                                        {!! Form::text('video', $sponsor->video, [
                                            'class' => 'form-control',
                                            'placeholder' => 'https://youtube.com',
                                        ]) !!}
                                        @if ($errors->has('video'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('video') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <!-- founded -->
                                    <div class="form-group{{ $errors->has('employees') ? ' has-error' : '' }}">
                                        {!! Form::label('employees') !!}
                                        {!! Form::text('employees', $sponsor->employees, [
                                            'class' => 'form-control',
                                            'placeholder' => 'jumlah karyawan(100/5000/70000)',
                                        ]) !!}
                                        @if ($errors->has('employees'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('employees') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group{{ $errors->has('Company Category') ? ' has-error' : '' }}">
                                        {!! Form::label('company_category') !!}
                                        {!! Form::text('company_category', $sponsor->company_category, [
                                            'class' => 'form-control',
                                            'placeholder' => 'Industri apa ?',
                                        ]) !!}
                                        @if ($errors->has('company_category'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('company_category') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group{{ $errors->has('Instagram') ? ' has-error' : '' }}">
                                        {!! Form::label('instagram') !!}
                                        {!! Form::text('instagram', $sponsor->instagram, [
                                            'class' => 'form-control',
                                            'placeholder' => 'instagram',
                                        ]) !!}
                                        @if ($errors->has('instagram'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('instagram') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group{{ $errors->has('facebook') ? ' has-error' : '' }}">
                                        {!! Form::label('facebook') !!}
                                        {!! Form::text('facebook', $sponsor->facebook, [
                                            'class' => 'form-control',
                                            'placeholder' => 'facebook',
                                        ]) !!}
                                        @if ($errors->has('facebook'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('facebook') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group{{ $errors->has('linkedin') ? ' has-error' : '' }}">
                                        {!! Form::label('linkedin') !!}
                                        {!! Form::text('linkedin', $sponsor->linkedin, [
                                            'class' => 'form-control',
                                            'placeholder' => 'linkedin',
                                        ]) !!}
                                        @if ($errors->has('linkedin'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('linkedin') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="form-group{{ $errors->has('address') ? ' has-error' : '' }}">
                                {!! Form::label('Address') !!}
                                {!! Form::textarea('address', $sponsor->address, [
                                    'cols' => '30',
                                    'rows' => '5',
                                    'class' => 'form-control',
                                    'placeholder' => 'Address',
                                ]) !!}
                                @if ($errors->has('address'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('address') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">
                                {!! Form::label('Deskripsi') !!}
                                {!! Form::textarea('description', $sponsor->description, [
                                    'id' => 'my-editor',
                                    'class' => 'form-control my-editor',
                                    'placeholder' => 'Description',
                                ]) !!}
                                @if ($errors->has('description'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('description') }}</strong>
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
                                {!! Form::label('Featured Image') !!}
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="image" name="image">
                                        <label class="custom-file-label" for="image">Choose file</label>
                                    </div>
                                </div>
                                @if ($errors->has('image'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('image') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="form-group">
                                <label>Image Preview</label>
                                <div>
                                    @if (!empty($sponsor->image))
                                        <img src="{{ $sponsor->image }}" id="image-preview" style="max-height:100px;">
                                    @else
                                        <p>No image available</p>
                                    @endif
                                </div>
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
                                    <div class="form-group{{ $errors->has('package') ? ' has-error' : '' }}">
                                        {!! Form::label('Package') !!}
                                        {!! Form::select(
                                            'package',
                                            ['default' => 'Select Package', 'silver' => 'Silver', 'gold' => 'Gold', 'platinum' => 'Platinum'],
                                            $sponsor->package ?? 'default', // Nilai default diambil dari objek sponsor jika ada, jika tidak, maka 'default' dipilih.
                                            [
                                                'class' => 'form-control',
                                            ],
                                        ) !!}
                                        @if ($errors->has('package'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('package') }}</strong>
                                            </span>
                                        @endif
                                    </div>

                                    <div class="form-group{{ $errors->has('status') ? ' has-error' : '' }}">
                                        {!! Form::label('Status') !!}
                                        {!! Form::select('status', ['draft' => 'Draft', 'publish' => 'Publish'], $sponsor->status ?? 'draft', [
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
                                        <a href="{{ url('admin/sponsors') }}" class="btn btn-warning">Close</a>
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
