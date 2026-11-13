<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.finance_reports.index') }}" class="form-inline flex-wrap" style="gap:8px">
            <label class="mr-1 text-muted" style="font-size:13px">Year</label>
            {{-- Ganti tahun → langsung submit, kembali ke "All months" & pembanding default. --}}
            <select name="year" class="form-control form-control-sm" style="min-width:100px"
                onchange="this.form.month.value=''; this.form.compare.value=''; this.form.submit()">
                @foreach ($years as $y)
                    <option value="{{ $y }}" {{ $filters['year'] === $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>

            <label class="ml-1 mr-1 text-muted" style="font-size:13px">vs</label>
            {{-- Tahun pembanding untuk grafik (% = tahun terpilih ÷ tahun pembanding). --}}
            <select name="compare" class="form-control form-control-sm" style="min-width:100px" onchange="this.form.submit()">
                @foreach ($compareYears as $y)
                    <option value="{{ $y }}" {{ $filters['compare'] === $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
            <input type="hidden" name="month" value="{{ $filters['month'] }}">

            <div class="input-group input-group-sm">
                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-search"></i></span></div>
                <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Invoice no or sponsor..."
                    value="{{ $filters['keyword'] }}" style="min-width:220px">
            </div>

            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('admin.finance_reports.index') }}" class="btn btn-light btn-sm">Reset</a>
        </form>
    </div>
</div>
