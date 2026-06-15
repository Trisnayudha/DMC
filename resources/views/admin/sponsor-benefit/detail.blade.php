@extends('layouts.inspire.master')

@section('content')
<div class="content-wrapper">
    <section class="section">
        <div class="section-header">
            <h1>Benefit Management — {{ $sponsor->name }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('sponsors.index') }}">Sponsors Management</a></div>
                <div class="breadcrumb-item active">Benefit Details</div>
            </div>
        </div>

        <div class="section-body">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            {{-- Summary cards --}}
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-info"><i class="fas fa-boxes"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Total Benefits</h4></div>
                            <div class="card-body">{{ $totalCount }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-success"><i class="fas fa-check-circle"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Fully Used</h4></div>
                            <div class="card-body">{{ $usedCount }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Not Fully Used</h4></div>
                            <div class="card-body">{{ $totalCount - $usedCount }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body py-2">
                    <div class="d-flex align-items-center" style="gap:10px">
                        <span class="text-muted" style="white-space:nowrap;font-size:13px">Overall usage</span>
                        <div class="progress flex-fill" style="height:16px">
                            <div class="progress-bar bg-primary" style="width:{{ $benefitUsageRate }}%">
                                {{ $benefitUsageRate }}%
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Benefit cards grouped by category --}}
            @php $grouped = $benefitDetails->groupBy(function($d) { return $d->benefit->category; }); @endphp

            @foreach($grouped as $category => $details)
                <h5 class="text-uppercase text-muted mb-2" style="font-size:11px;letter-spacing:.6px">
                    <i class="fas fa-tag mr-1"></i> {{ $category ?: 'Uncategorized' }}
                </h5>

                @foreach($details as $detail)
                    @php
                        $qty        = $quantityMap[$detail->benefit_id] ?? 1;
                        $marksCount = $detail->marks->count();
                        $pct        = $qty > 0 ? min(100, round(($marksCount / $qty) * 100)) : 0;
                        $isFull     = $marksCount >= $qty;
                        $barClass   = $isFull ? 'bg-success' : ($marksCount > 0 ? 'bg-warning' : 'bg-secondary');
                    @endphp

                    <div class="card mb-3">
                        <div class="card-header d-flex align-items-center justify-content-between py-2">
                            <div>
                                <span class="font-weight-bold">{{ $detail->benefit->name }}</span>
                                <span class="text-muted ml-2" style="font-size:12px">Period: {{ $activePeriod }}</span>
                            </div>
                            <div class="d-flex align-items-center" style="gap:10px">
                                <div style="width:140px">
                                    <div class="progress" style="height:10px">
                                        <div class="progress-bar {{ $barClass }}" style="width:{{ $pct }}%"></div>
                                    </div>
                                    <div class="text-right text-muted" style="font-size:11px;margin-top:2px">
                                        {{ $marksCount }} / {{ $qty }} used
                                    </div>
                                </div>
                                @if($isFull)
                                    <span class="badge badge-success">Fully Used</span>
                                @elseif($marksCount > 0)
                                    <span class="badge badge-warning">Partial</span>
                                @else
                                    <span class="badge badge-secondary">Unused</span>
                                @endif
                                @if(!$isFull)
                                    <button class="btn btn-sm btn-primary"
                                        data-toggle="modal"
                                        data-target="#markModal{{ $detail->id }}">
                                        <i class="fas fa-plus"></i> Add Mark
                                    </button>
                                @endif
                            </div>
                        </div>

                        @if($detail->marks->isNotEmpty())
                            <div class="card-body p-0">
                                <table class="table table-sm mb-0" style="font-size:13px">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width:130px;padding-left:16px">Date</th>
                                            <th>Note</th>
                                            <th style="width:100px">Proof</th>
                                            <th style="width:100px">By</th>
                                            <th style="width:70px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($detail->marks as $mark)
                                            <tr>
                                                <td style="padding-left:16px;white-space:nowrap">
                                                    {{ $mark->marked_at->format('d M Y') }}
                                                </td>
                                                <td class="text-muted">{{ $mark->note ?: '-' }}</td>
                                                <td>
                                                    @if($mark->proof_image)
                                                        <a href="{{ $mark->proof_image }}" target="_blank">
                                                            <img src="{{ $mark->proof_image }}"
                                                                style="height:36px;width:54px;object-fit:cover;border-radius:3px;border:1px solid #dee2e6"
                                                                alt="proof">
                                                        </a>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-muted" style="font-size:11px">
                                                    {{ $mark->createdBy ? $mark->createdBy->name : '-' }}
                                                </td>
                                                <td>
                                                    <form action="{{ route('sponsors.benefit.removeMark', $mark->id) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Hapus mark ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="card-body py-2 text-muted" style="font-size:13px">
                                <i class="fas fa-info-circle mr-1"></i> Belum ada mark untuk benefit ini.
                            </div>
                        @endif
                    </div>
                @endforeach

                <div class="mb-3"></div>
            @endforeach

            <div class="text-right">
                <a href="{{ route('sponsors.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Sponsors
                </a>
            </div>

        </div>
    </section>
</div>

{{-- ============================================================
     Semua modal di luar section content agar tidak nested di
     dalam card loop — mencegah z-index & backdrop stacking.
     ============================================================ --}}
@foreach($benefitDetails as $detail)
    @if(($quantityMap[$detail->benefit_id] ?? 1) > $detail->marks->count())
        <div class="modal fade" id="markModal{{ $detail->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('sponsors.benefit.addMark', $detail->id) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-check-circle text-primary mr-1"></i>
                                Add Mark — {{ $detail->benefit->name }}
                            </h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="marked_at" class="form-control"
                                    value="{{ now()->format('Y-m-d') }}" required>
                                <small class="text-muted">Bisa diisi tanggal yang sudah lewat.</small>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    Note <span class="text-muted font-weight-normal">(optional)</span>
                                </label>
                                <input type="text" name="note" class="form-control"
                                    placeholder="Misal: DMC Conference 2025">
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    Bukti Screenshot
                                    <span class="text-muted font-weight-normal">(optional, max 5MB)</span>
                                </label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input proof-input"
                                        name="proof_image" accept="image/*"
                                        id="proofInput{{ $detail->id }}">
                                    <label class="custom-file-label" for="proofInput{{ $detail->id }}">
                                        Choose image...
                                    </label>
                                </div>
                                <div class="mt-2" id="previewBox{{ $detail->id }}" style="display:none">
                                    <img id="previewImg{{ $detail->id }}"
                                        style="max-height:120px;border-radius:4px;border:1px solid #dee2e6">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> Save Mark
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach

@endsection

@push('bottom')
<script>
    document.querySelectorAll('.proof-input').forEach(function(input) {
        input.addEventListener('change', function() {
            var id      = this.id.replace('proofInput', '');
            var preview = document.getElementById('previewImg' + id);
            var box     = document.getElementById('previewBox' + id);
            var label   = this.nextElementSibling;
            if (this.files && this.files[0]) {
                label.textContent = this.files[0].name;
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    box.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            } else {
                box.style.display = 'none';
                label.textContent = 'Choose image...';
            }
        });
    });
</script>
@endpush
