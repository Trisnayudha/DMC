@extends('layouts.inspire.master')

@section('content')
<div class="content-wrapper">
    <section class="section">
        <div class="section-header">
            <h1>Sponsor Contact Directory</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('sponsors.index') }}">Sponsors Management</a></div>
                <div class="breadcrumb-item active">Contact Directory</div>
            </div>
        </div>

        <div class="section-body">

            @foreach (['success' => 'success', 'error' => 'danger'] as $flash => $alert)
                @if (session($flash))
                    <div class="alert alert-{{ $alert }} alert-dismissible show fade">
                        <div class="alert-body">
                            <button class="close" data-dismiss="alert"><span>×</span></button>
                            {{ session($flash) }}
                        </div>
                    </div>
                @endif
            @endforeach
            @if ($errors->any())
                <div class="alert alert-warning">{{ $errors->first() }}</div>
            @endif

            @include('admin.sponsor.contact_directory._summary_cards')
            @include('admin.sponsor.contact_directory._filters')

            @forelse ($sponsors as $sponsor)
                @include('admin.sponsor.contact_directory._sponsor_card')
            @empty
                <div class="card">
                    <div class="card-body text-center text-muted py-5">
                        <i class="fas fa-search fa-2x mb-3 d-block"></i>
                        No sponsors found matching your filters.
                    </div>
                </div>
            @endforelse

        </div>
    </section>
</div>
@include('admin.sponsor.contact_directory._edit_modal')
@endsection

@push('bottom')
<script>
    // Rotate chevron on collapse toggle
    $(document).on('click', '[data-toggle="collapse"]', function() {
        var icon = $(this).find('.fa-chevron-down, .fa-chevron-up');
        var target = $(this).data('target');
        if ($(target).hasClass('show')) {
            icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
        } else {
            icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
        }
    });

    // Edit kontak: isi modal dari data-* tombol; field tampil sesuai sumber
    // (PIC & member: phone, representative: social). Email member = login, tidak diubah.
    $(document).on('click', '.btn-edit-contact', function() {
        var d = $(this).data();
        var picIds = d.picIds || [], repIds = d.repIds || [], memberIds = d.memberIds || [];
        var $ids = $('#ec-source-ids').empty();
        picIds.forEach(function(id) { $ids.append($('<input type="hidden" name="pic_ids[]">').val(id)); });
        repIds.forEach(function(id) { $ids.append($('<input type="hidden" name="rep_ids[]">').val(id)); });
        memberIds.forEach(function(id) { $ids.append($('<input type="hidden" name="member_ids[]">').val(id)); });

        $('#ec-sponsor-id').val(d.sponsorId);
        $('#ec-sponsor-name').text(d.sponsorName);
        ['name', 'title', 'email', 'phone', 'instagram', 'linkedin'].forEach(function(f) {
            $('#ec-' + f).val(d[f] == null ? '' : d[f]);
        });
        var memberOnly = memberIds.length > 0 && !picIds.length && !repIds.length;
        $('#ec-email').prop('readonly', memberOnly);
        $('.ec-pic-only').toggle(picIds.length > 0 || memberIds.length > 0);
        $('.ec-rep-only').toggle(repIds.length > 0);

        var scope = [];
        if (picIds.length) scope.push('PIC');
        if (repIds.length) scope.push('Representative');
        if (memberIds.length) scope.push('member profile');
        $('#ec-scope').text('This will update the ' + scope.join(' & ') + ' record for this sponsor.');

        var notes = [];
        if (memberIds.length) notes.push('Member profile: name, job title and phone will be updated as well; the member email stays unchanged (used for login).');
        if (d.hasBilling) notes.push('This contact is also the billing contact — billing details are edited from the Edit Sponsor form.');
        $('#ec-note').text(notes.join(' ')).toggle(notes.length > 0);

        $('#editContactModal').modal('show');
    });
</script>
@endpush
