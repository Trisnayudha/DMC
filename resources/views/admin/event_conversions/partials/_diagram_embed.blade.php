{{-- Respons AJAX untuk halaman DMC Main Event: style + diagram + pilihan tahun --}}
@include('admin.event_conversions.partials._diagram_styles')

<div class="d-flex justify-content-end mb-2">
    <select class="form-control form-control-sm w-auto font-weight-bold" id="conversion-diagram-year">
        <option value="all" {{ $selectedYear === 'all' ? 'selected' : '' }}>All Years</option>
        @foreach ($years as $y)
            <option value="{{ $y }}" {{ (string) $selectedYear === (string) $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
        @endforeach
    </select>
</div>

@include('admin.event_conversions.partials._diagram')
