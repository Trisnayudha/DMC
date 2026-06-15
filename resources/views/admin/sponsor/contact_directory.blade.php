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
</script>
@endpush
