@extends('layouts.inspire.master')
@section('content-title', 'Edit Sponsor')
@section('content')
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('sponsors.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Edit Sponsor</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('sponsors.index') }}">Sponsor Management</a></div>
                <div class="breadcrumb-item active"><a href="">Edit Sponsor</a></div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">Edit Sponsor</h2>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4>Basic Information</h4>
                            <!-- Quick Action: Jika diinginkan bisa ditambahkan tombol update contract di sini -->
                        </div>
                        <div class="card-body">
                            {!! Form::model($sponsor, [
                                'method' => 'PATCH',
                                'url' => 'admin/sponsors/' . $sponsor->id,
                                'enctype' => 'multipart/form-data',
                            ]) !!}
                            <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                {!! Form::label('Name') !!}
                                {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Name']) !!}
                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong style="color:red">{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                        <label>Email</label>
                                        {!! Form::text('email', null, [
                                            'class' => 'form-control',
                                            'placeholder' => 'email@gmail.com',
                                        ]) !!}
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
                                                <div class="input-group-text"><i class="fas fa-calendar"></i></div>
                                            </div>
                                            {!! Form::text('founded', null, [
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
                                        {!! Form::number('location_office', null, [
                                            'class' => 'form-control',
                                            'placeholder' => 'Jumlah lokasi office (1/2/3/4/5 dst)',
                                            'min' => 1,
                                            'max' => 5,
                                            'step' => 1,
                                        ]) !!}
                                        @if ($errors->has('location_office'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('location_office') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group{{ $errors->has('website') ? ' has-error' : '' }}">
                                        <label>Website</label>
                                        {!! Form::text('website', null, [
                                            'class' => 'form-control',
                                            'placeholder' => 'www.google.com',
                                        ]) !!}
                                        @if ($errors->has('website'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('website') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group{{ $errors->has('video') ? ' has-error' : '' }}">
                                        <label>Video Youtube</label>
                                        {!! Form::text('video', null, [
                                            'class' => 'form-control',
                                            'placeholder' => 'https://youtube.com/',
                                        ]) !!}
                                        @if ($errors->has('video'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('video') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group{{ $errors->has('employees') ? ' has-error' : '' }}">
                                        {!! Form::label('Employees') !!}
                                        {!! Form::number('employees', null, [
                                            'class' => 'form-control',
                                            'placeholder' => 'Jumlah karyawan (100/5000/70000)',
                                        ]) !!}
                                        @if ($errors->has('employees'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('employees') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group{{ $errors->has('company_category') ? ' has-error' : '' }}">
                                        {!! Form::label('Company Category') !!}
                                        {!! Form::text('company_category', null, [
                                            'class' => 'form-control',
                                            'placeholder' => 'Industri apa?',
                                        ]) !!}
                                        @if ($errors->has('company_category'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('company_category') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group{{ $errors->has('instagram') ? ' has-error' : '' }}">
                                        {!! Form::label('Instagram') !!}
                                        {!! Form::text('instagram', null, [
                                            'class' => 'form-control',
                                            'placeholder' => 'Instagram',
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
                                        {!! Form::label('Facebook') !!}
                                        {!! Form::text('facebook', null, [
                                            'class' => 'form-control',
                                            'placeholder' => 'Facebook',
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
                                        {!! Form::label('LinkedIn') !!}
                                        {!! Form::text('linkedin', null, [
                                            'class' => 'form-control',
                                            'placeholder' => 'LinkedIn',
                                        ]) !!}
                                        @if ($errors->has('linkedin'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('linkedin') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <!-- Input Contract Start & End -->
                                <div class="col-6">
                                    <div class="form-group{{ $errors->has('contract_start') ? ' has-error' : '' }}">
                                        {!! Form::label('Contract Start') !!}
                                        <input type="month" name="contract_start" class="form-control"
                                            value="{{ old('contract_start', $sponsor->contract_start) }}">
                                        @if ($errors->has('contract_start'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('contract_start') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group{{ $errors->has('contract_end') ? ' has-error' : '' }}">
                                        {!! Form::label('Contract End') !!}
                                        <input type="month" name="contract_end" class="form-control"
                                            value="{{ old('contract_end', $sponsor->contract_end) }}">
                                        @if ($errors->has('contract_end'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('contract_end') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="form-group{{ $errors->has('address') ? ' has-error' : '' }}">
                                <label>Address</label>
                                {!! Form::textarea('address', null, [
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
                                {!! Form::textarea('description', null, [
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

                            <!-- Section Sponsor PIC -->
                            <div class="card mt-4">
                                <div class="card-header">
                                    <h4>Sponsor PIC Information</h4>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered" id="picTable">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Title</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if ($sponsor->pics && $sponsor->pics->count() > 0)
                                                @foreach ($sponsor->pics as $pic)
                                                    <tr>
                                                        <td>
                                                            <input type="text" name="pic[name][]"
                                                                value="{{ $pic->name }}" class="form-control">
                                                            <input type="hidden" name="pic[id][]"
                                                                value="{{ $pic->id }}">
                                                        </td>
                                                        <td><input type="text" name="pic[title][]"
                                                                value="{{ $pic->title }}" class="form-control"></td>
                                                        <td><input type="email" name="pic[email][]"
                                                                value="{{ $pic->email }}" class="form-control"></td>
                                                        <td><input type="text" name="pic[phone][]"
                                                                value="{{ $pic->phone }}" class="form-control"></td>
                                                        <td><button type="button"
                                                                class="btn btn-danger remove-pic">Remove</button></td>
                                                    </tr>
                                                @endforeach
                                            @elseif(old('pic'))
                                                @foreach (old('pic.name') as $index => $picName)
                                                    <tr>
                                                        <td>
                                                            <input type="text" name="pic[name][]"
                                                                value="{{ old('pic.name')[$index] }}"
                                                                class="form-control">
                                                        </td>
                                                        <td>
                                                            <input type="text" name="pic[title][]"
                                                                value="{{ old('pic.title')[$index] }}"
                                                                class="form-control">
                                                        </td>
                                                        <td>
                                                            <input type="email" name="pic[email][]"
                                                                value="{{ old('pic.email')[$index] }}"
                                                                class="form-control">
                                                        </td>
                                                        <td>
                                                            <input type="text" name="pic[phone][]"
                                                                value="{{ old('pic.phone')[$index] }}"
                                                                class="form-control">
                                                        </td>
                                                        <td><button type="button"
                                                                class="btn btn-danger remove-pic">Remove</button></td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                    <button type="button" class="btn btn-primary" id="addPic">Add PIC</button>
                                </div>
                            </div>
                            <!-- End Section Sponsor PIC -->

                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Featured Image</h4>
                                </div>
                                <div class="card-body">
                                    <div class="form-group{{ $errors->has('image') ? ' has-error' : '' }}">
                                        {!! Form::label('Thumbnails') !!}
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="image"
                                                    name="image">
                                                <label class="custom-file-label" for="exampleInputFile">Choose
                                                    file</label>
                                            </div>
                                        </div>
                                        @if ($errors->has('image'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('image') }}</strong>
                                            </span>
                                        @endif
                                        <img id="holder" style="margin-top:15px;max-height:100px;"
                                            src="{{ asset($sponsor->image) }}">
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
                                    <div class="form-group{{ $errors->has('package') ? ' has-error' : '' }}">
                                        {!! Form::label('Package') !!}
                                        {!! Form::select('package', ['silver' => 'Silver', 'gold' => 'Gold', 'platinum' => 'Platinum'], null, [
                                            'class' => 'form-control',
                                        ]) !!}
                                        @if ($errors->has('package'))
                                            <span class="help-block">
                                                <strong style="color:red">{{ $errors->first('package') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group{{ $errors->has('status') ? ' has-error' : '' }}">
                                        {!! Form::label('Status') !!}
                                        {!! Form::select('status', ['draft' => 'Draft', 'publish' => 'Publish'], null, ['class' => 'form-control']) !!}
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

    @push('bottom')
        <script>
            $(document).ready(function() {
                $('#addPic').click(function() {
                    var row = `<tr>
                        <td><input type="text" name="pic[name][]" class="form-control"></td>
                        <td><input type="text" name="pic[title][]" class="form-control"></td>
                        <td><input type="email" name="pic[email][]" class="form-control"></td>
                        <td><input type="text" name="pic[phone][]" class="form-control"></td>
                        <td><button type="button" class="btn btn-danger remove-pic">Remove</button></td>
                    </tr>`;
                    $('#picTable tbody').append(row);
                });
                $(document).on('click', '.remove-pic', function() {
                    $(this).closest('tr').remove();
                });
            });
        </script>
    @endpush

    <script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
    <script src="{{ asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
    <script>
        $(function() {
            $('#summernote').summernote();
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
            });
        });
    </script>
@endsection
