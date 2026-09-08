{{-- Main card: chance budget summary + add button + prize list (responsive table + mobile cards) --}}

@php
    $fmtUsed = rtrim(rtrim(number_format($usedChance, 2, '.', ''), '0'), '.');
    $remainingChance = max(0, round(100 - $usedChance, 2));
    $fmtRemaining = rtrim(rtrim(number_format($remainingChance, 2, '.', ''), '0'), '.');
@endphp

<div class="card mb-4" id="lucky-draw-budget-card" data-quick-batch-url="{{ route('admin.lucky_draw.items.quick_batch') }}">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:10px;">
        <div>
            <h4 class="mb-0">
                <i class="fas fa-gift mr-1 text-primary"></i>Katalog Hadiah Lucky Draw
                <i class="fas fa-info-circle text-muted ml-1" style="font-size:12px;"
                    title="Peluang % diatur manual per hadiah. Hadiah 0% atau nonaktif tidak akan pernah keluar. Sisanya adalah peluang pengunjung tidak mendapat hadiah (ZONK)."
                    data-toggle="tooltip"></i>
            </h4>
            <small class="text-muted d-block mt-1">
                Atur hadiah yang siap keluar dan sesuaikan persentasenya secara instan.
            </small>
        </div>
        <div class="d-flex align-items-center flex-wrap" style="gap:8px;">
            <button type="button" class="btn btn-sm btn-outline-info js-btn-balance" title="Bagi rata kuota 100% ke semua hadiah aktif">
                <i class="fas fa-balance-scale mr-1"></i>Bagi Rata (100%)
            </button>
            <button type="button" class="btn btn-sm btn-primary" id="btn-add-item">
                <i class="fas fa-plus mr-1"></i>Tambah Hadiah
            </button>
        </div>
    </div>

    {{-- Interactive Live Budget Progress Bar --}}
    <div class="px-4 pt-3 pb-2 bg-light border-bottom">
        <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap" style="gap: 6px;">
            <div class="d-flex align-items-center" style="gap: 6px;">
                <span class="badge {{ $usedChance >= 100 ? 'badge-success' : 'badge-primary' }} px-2 py-1" id="badge-used-chance">
                    Peluang Hadiah: <strong id="val-used-chance">{{ $fmtUsed }}%</strong>
                </span>
                <span class="badge badge-secondary px-2 py-1" id="badge-remaining-chance">
                    Peluang Zonk: <strong id="val-remaining-chance">{{ $fmtRemaining }}%</strong>
                </span>
            </div>
            <div class="small text-muted" id="budget-status-note">
                @if ($usedChance >= 100)
                    <span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i>Pasti keluar hadiah (ZONK 0%)</span>
                @else
                    <span><i class="fas fa-info-circle mr-1"></i>Sisa {{ $fmtRemaining }}% menjadi peluang tanpa hadiah (Zonk)</span>
                @endif
            </div>
        </div>

        <div class="progress" style="height: 10px; border-radius: 6px; overflow: hidden; background-color: #e9ecef;">
            <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" id="progress-bar-used"
                role="progressbar" style="width: {{ min(100, $usedChance) }}%;"
                aria-valuenow="{{ min(100, $usedChance) }}" aria-valuemin="0" aria-valuemax="100">
            </div>
            <div class="progress-bar bg-light text-muted" id="progress-bar-remaining"
                role="progressbar" style="width: {{ max(0, 100 - $usedChance) }}%;"
                aria-valuenow="{{ max(0, 100 - $usedChance) }}" aria-valuemin="0" aria-valuemax="100">
            </div>
        </div>
    </div>

    <div class="card-body p-0 p-md-3">

        {{-- ======================================================== --}}
        {{-- DESKTOP VIEW (>= 768px): Tabel Kompak & Rapi            --}}
        {{-- ======================================================== --}}
        <div class="table-responsive lucky-draw-table-wrap d-none d-md-block">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="10px">No</th>
                        <th width="65px">Foto</th>
                        <th>Nama Hadiah</th>
                        <th class="text-nowrap" width="130px">Peluang (%)</th>
                        <th width="70px">Urutan</th>
                        <th width="100px">Status</th>
                        <th width="120px">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    @forelse ($items as $item)
                        @php
                            $itemChanceFmt = rtrim(rtrim(number_format($item->chance_percent, 2, '.', ''), '0'), '.');
                        @endphp
                        <tr id="desktop-row-{{ $item->id }}" class="prize-row {{ $item->is_active ? '' : 'table-light text-muted' }}">
                            <td class="align-middle">{{ $no++ }}</td>
                            <td class="align-middle">
                                @if ($item->image)
                                    <img src="{{ asset($item->image) }}" alt="{{ $item->name }}" width="42" height="42"
                                        style="object-fit:cover;border-radius:6px;border:1px solid #e9ecef;">
                                @else
                                    <div class="prize-fallback-icon-sm">
                                        <i class="fas fa-gift text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="align-middle">
                                <strong>{{ $item->name }}</strong>
                            </td>
                            <td class="align-middle text-nowrap">
                                <span class="font-weight-bold text-primary desktop-chance-text" id="desktop-chance-val-{{ $item->id }}">
                                    {{ $itemChanceFmt }}%
                                </span>
                            </td>
                            <td class="align-middle">{{ $item->sort_order }}</td>
                            <td class="align-middle">
                                @if ($item->is_active)
                                    <span class="badge badge-success mini-badge desktop-status-badge" id="desktop-badge-{{ $item->id }}">Aktif</span>
                                @else
                                    <span class="badge badge-secondary mini-badge desktop-status-badge" id="desktop-badge-{{ $item->id }}">Nonaktif</span>
                                @endif
                            </td>
                            <td class="align-middle">
                                <div class="btn-icon-group">
                                    <button type="button" class="btn btn-icon btn-outline-warning js-force-win-btn"
                                        data-id="{{ $item->id }}" data-name="{{ $item->name }}"
                                        title="Jadikan 100% (Pasti Keluar)" data-toggle="tooltip">
                                        <i class="fas fa-star"></i>
                                    </button>
                                    <button type="button" class="btn btn-icon btn-outline-secondary btn-edit-item"
                                        data-id="{{ $item->id }}" data-name="{{ $item->name }}"
                                        data-chance="{{ $item->chance_percent }}" data-sort="{{ $item->sort_order }}"
                                        data-active="{{ $item->is_active ? 1 : 0 }}"
                                        data-image="{{ $item->image ? asset($item->image) : '' }}"
                                        data-update-url="{{ route('admin.lucky_draw.items.update', $item->id) }}"
                                        title="Edit Detail" data-toggle="tooltip">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.lucky_draw.items.destroy', $item->id) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Hapus hadiah &quot;{{ $item->name }}&quot;?');">
                                        @csrf
                                        <button type="submit" class="btn btn-icon btn-outline-danger" title="Hapus"
                                            data-toggle="tooltip">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada hadiah. Klik "Tambah Hadiah" untuk membuat baru.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ======================================================== --}}
        {{-- MOBILE VIEW (< 768px): Card-List Ramah Sentuhan (Touch)   --}}
        {{-- ======================================================== --}}
        <div class="lucky-draw-mobile-list d-block d-md-none p-2">
            @forelse ($items as $item)
                @php
                    $itemChanceFmt = rtrim(rtrim(number_format($item->chance_percent, 2, '.', ''), '0'), '.');
                @endphp
                <div class="card mb-3 shadow-sm mobile-prize-card {{ $item->is_active ? '' : 'is-inactive' }}"
                    id="mobile-card-{{ $item->id }}"
                    data-id="{{ $item->id }}"
                    data-quick-url="{{ route('admin.lucky_draw.items.quick_update', $item->id) }}"
                    data-name="{{ $item->name }}">
                    <div class="card-body p-3">

                        {{-- Baris Atas: Foto, Nama, Toggle ON/OFF, dan Menu Edit --}}
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center flex-grow-1 mr-2" style="min-width: 0;">
                                @if ($item->image)
                                    <img src="{{ asset($item->image) }}" alt="{{ $item->name }}"
                                        class="mr-2 mobile-thumb-img">
                                @else
                                    <div class="mr-2 mobile-fallback-icon">
                                        <i class="fas fa-gift text-primary"></i>
                                    </div>
                                @endif
                                <div class="text-truncate">
                                    <h6 class="mb-0 text-truncate font-weight-bold mobile-prize-name" title="{{ $item->name }}">
                                        {{ $item->name }}
                                    </h6>
                                    <div class="d-flex align-items-center mt-1" style="gap:4px;">
                                        <span class="badge badge-light border text-muted px-1 py-0" style="font-size:10px;">
                                            Urutan: {{ $item->sort_order }}
                                        </span>
                                        <span class="badge {{ $item->is_active ? 'badge-success' : 'badge-secondary' }} px-1 py-0 mobile-status-pill"
                                            id="mobile-status-pill-{{ $item->id }}" style="font-size:10px;">
                                            {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Toggle Switch iOS Style + Menu Aksi --}}
                            <div class="d-flex align-items-center" style="gap: 8px;">
                                <div class="custom-control custom-switch mobile-switch-wrap" title="Nyalakan/Matikan Hadiah">
                                    <input type="checkbox" class="custom-control-input js-toggle-active"
                                        id="switch-active-{{ $item->id }}"
                                        data-id="{{ $item->id }}"
                                        {{ $item->is_active ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="switch-active-{{ $item->id }}"></label>
                                </div>

                                {{-- Dropdown Aksi Detail --}}
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border py-1 px-2" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v text-muted"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                        <button type="button" class="dropdown-item btn-edit-item"
                                            data-id="{{ $item->id }}" data-name="{{ $item->name }}"
                                            data-chance="{{ $item->chance_percent }}" data-sort="{{ $item->sort_order }}"
                                            data-active="{{ $item->is_active ? 1 : 0 }}"
                                            data-image="{{ $item->image ? asset($item->image) : '' }}"
                                            data-update-url="{{ route('admin.lucky_draw.items.update', $item->id) }}">
                                            <i class="fas fa-edit mr-2 text-primary"></i>Edit Detail (Foto/Nama)
                                        </button>
                                        <div class="dropdown-divider"></div>
                                        <form action="{{ route('admin.lucky_draw.items.destroy', $item->id) }}" method="POST"
                                            onsubmit="return confirm('Hapus hadiah &quot;{{ $item->name }}&quot;?');">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-trash mr-2"></i>Hapus Hadiah
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Baris Bawah: Kotak Kontrol Peluang Cepat --}}
                        <div class="mobile-chance-control-box p-2 rounded bg-light border">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="small font-weight-bold text-muted">
                                    <i class="fas fa-chart-pie mr-1 text-info"></i>PELUANG KELUAR:
                                </span>
                                <span class="badge badge-primary px-2 py-1 js-mobile-chance-display"
                                    id="mobile-chance-badge-{{ $item->id }}" style="font-size: 13px;">
                                    {{ $itemChanceFmt }}%
                                </span>
                            </div>

                            {{-- Stepper Kontrol: Tombol [-] | Input | Tombol [+] --}}
                            <div class="d-flex align-items-center justify-content-between mb-2" style="gap: 6px;">
                                <button type="button" class="btn btn-light border font-weight-bold px-3 js-btn-step"
                                    data-id="{{ $item->id }}" data-step="-5" style="height: 40px; min-width: 52px;">
                                    -5%
                                </button>
                                <div class="input-group" style="max-width: 140px;">
                                    <input type="number" step="0.5" min="0" max="100"
                                        class="form-control text-center font-weight-bold js-chance-input"
                                        id="input-chance-{{ $item->id }}"
                                        data-id="{{ $item->id }}"
                                        value="{{ $itemChanceFmt }}"
                                        style="height: 40px; font-size: 16px;">
                                    <div class="input-group-append">
                                        <span class="input-group-text font-weight-bold bg-white text-muted px-2">%</span>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-light border font-weight-bold px-3 js-btn-step"
                                    data-id="{{ $item->id }}" data-step="5" style="height: 40px; min-width: 52px;">
                                    +5%
                                </button>
                            </div>

                            {{-- Preset Pills & Tombol Pasti Keluar (100%) --}}
                            <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 4px;">
                                <div class="d-flex flex-wrap" style="gap: 4px;">
                                    <button type="button" class="btn btn-xs btn-outline-secondary js-btn-preset" data-id="{{ $item->id }}" data-val="0">0%</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary js-btn-preset" data-id="{{ $item->id }}" data-val="10">10%</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary js-btn-preset" data-id="{{ $item->id }}" data-val="25">25%</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary js-btn-preset" data-id="{{ $item->id }}" data-val="50">50%</button>
                                </div>
                                <button type="button" class="btn btn-xs btn-warning font-weight-bold js-force-win-btn shadow-sm"
                                    data-id="{{ $item->id }}" data-name="{{ $item->name }}"
                                    title="Jadikan hadiah ini 100% dan nolkan yang lain">
                                    <i class="fas fa-star mr-1"></i>100% Pasti
                                </button>
                            </div>
                        </div>{{-- /mobile-chance-control-box --}}

                    </div>{{-- /card-body --}}
                </div>{{-- /mobile-prize-card --}}
            @empty
                <div class="text-center text-muted py-5">
                    <i class="fas fa-gift fa-3x mb-2 text-light"></i>
                    <p class="mb-0">Belum ada hadiah. Klik tombol "Tambah Hadiah" di atas.</p>
                </div>
            @endforelse
        </div>

    </div>{{-- /card-body --}}
</div>{{-- /card --}}

<style>
    /* Desktop table styling */
    .lucky-draw-table-wrap table {
        border-collapse: separate !important;
        border: none;
    }
    .lucky-draw-table-wrap table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f8f9fc;
        border-top: none !important;
        border-bottom: 2px solid #e3e6f0 !important;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #6c757d;
        white-space: nowrap;
        vertical-align: middle;
        padding: .45rem .6rem;
    }
    .lucky-draw-table-wrap table tbody td {
        border-bottom: 1px solid #eef0f4 !important;
        vertical-align: middle;
        font-size: 12px;
        padding: .5rem .6rem;
    }
    .lucky-draw-table-wrap table tbody tr:hover {
        background-color: #f8faff;
    }
    .lucky-draw-table-wrap .btn-icon {
        width: 26px;
        height: 26px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        border-radius: 6px;
    }
    .lucky-draw-table-wrap .btn-icon-group {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .lucky-draw-table-wrap .mini-badge {
        font-size: 10px;
        padding: .25em .5em;
        font-weight: 600;
    }

    .prize-fallback-icon-sm {
        width: 42px;
        height: 42px;
        background: #f1f3f9;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    /* Mobile Cards styling (< 768px) */
    .mobile-prize-card {
        border-radius: 10px;
        border: 1px solid #e3e6f0;
        transition: all 0.2s ease;
    }
    .mobile-prize-card.is-inactive {
        opacity: 0.60;
        background: #fdfdfe;
        border-style: dashed;
    }
    .mobile-prize-card.is-inactive .mobile-chance-control-box {
        opacity: 0.75;
    }
    .mobile-thumb-img {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        object-fit: cover;
        border: 1px solid #e3e6f0;
    }
    .mobile-fallback-icon {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        background: #eef2ff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .mobile-prize-name {
        font-size: 14px;
        color: #2c3e50;
    }
    .mobile-switch-wrap .custom-control-label::before {
        height: 24px;
        width: 44px;
        border-radius: 12px;
    }
    .mobile-switch-wrap .custom-control-label::after {
        width: 20px;
        height: 20px;
        border-radius: 10px;
    }
    .mobile-switch-wrap .custom-control-input:checked ~ .custom-control-label::after {
        transform: translateX(20px);
    }
    .btn-xs {
        padding: .2rem .5rem;
        font-size: 11px;
        line-height: 1.3;
        border-radius: .25rem;
    }
</style>
