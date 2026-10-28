{{-- Daftar member source + link pendaftaran publik --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:10px;">
        <div>
            <h4 class="mb-0"><i class="fas fa-link mr-1 text-primary"></i>Registration Links</h4>
            <small class="text-muted d-block mt-1">
                Member yang daftar lewat link ini tercatat dengan source <code>CATEGORY/CODE</code>.
                Source yang nonaktif membuat link-nya tidak bisa dipakai.
            </small>
        </div>
        <button type="button" class="btn btn-sm btn-primary" id="btn-add-source">
            <i class="fas fa-plus mr-1"></i>Tambah Source
        </button>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive member-source-table-wrap">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="10px">No</th>
                        <th>Category / Code</th>
                        <th>Nama</th>
                        <th>Form</th>
                        <th>Link Pendaftaran</th>
                        <th class="text-center">Pendaftar</th>
                        <th>Status</th>
                        <th width="120px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sources as $source)
                        @php
                            $sourceKey = $source->category . '/' . $source->code;
                            $link = $frontendUrl . '/member/' . $sourceKey;
                            $registered = $registrations[strtolower($sourceKey)] ?? 0;
                        @endphp
                        <tr class="{{ $source->is_active ? '' : 'table-light text-muted' }}">
                            <td>{{ $loop->iteration }}</td>
                            <td class="text-nowrap">
                                <strong>{{ $sourceKey }}</strong>
                                @if (isset($knownCategories[strtoupper($source->category)]))
                                    <div class="small text-muted">{{ $knownCategories[strtoupper($source->category)] }}</div>
                                @endif
                            </td>
                            <td>{{ $source->name }}</td>
                            <td>{{ $formTypes[$source->form_type] ?? $source->form_type }}</td>
                            <td>
                                <div class="d-flex align-items-center" style="gap:4px;">
                                    <a href="{{ $link }}" target="_blank" rel="noopener" class="text-truncate"
                                        style="max-width:260px;">{{ $link }}</a>
                                    <button type="button" class="btn btn-icon btn-outline-secondary js-copy-link"
                                        data-link="{{ $link }}" title="Copy link" data-toggle="tooltip">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="text-center">{{ number_format($registered) }}</td>
                            <td>
                                @if ($source->is_active)
                                    <span class="badge badge-success mini-badge">Aktif</span>
                                @else
                                    <span class="badge badge-secondary mini-badge">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-icon-group">
                                    <button type="button" class="btn btn-icon btn-outline-secondary btn-edit-source"
                                        data-category="{{ $source->category }}" data-code="{{ $source->code }}"
                                        data-name="{{ $source->name }}" data-form-type="{{ $source->form_type }}"
                                        data-active="{{ $source->is_active ? 1 : 0 }}"
                                        data-locked="{{ $registered > 0 ? 1 : 0 }}"
                                        data-update-url="{{ route('admin.member_sources.update', $source->id) }}"
                                        title="Edit" data-toggle="tooltip">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.member_sources.toggle', $source->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        <button type="submit"
                                            class="btn btn-icon {{ $source->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                            title="{{ $source->is_active ? 'Nonaktifkan' : 'Aktifkan' }}" data-toggle="tooltip">
                                            <i class="fas {{ $source->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                        </button>
                                    </form>
                                    @if ($registered === 0)
                                        <form action="{{ route('admin.member_sources.destroy', $source->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Hapus source &quot;{{ $sourceKey }}&quot;?');">
                                            @csrf
                                            <button type="submit" class="btn btn-icon btn-outline-danger" title="Hapus"
                                                data-toggle="tooltip">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Belum ada source. Klik "Tambah Source" untuk membuat baru.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .member-source-table-wrap table thead th {
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
    .member-source-table-wrap table tbody td {
        border-bottom: 1px solid #eef0f4 !important;
        vertical-align: middle;
        font-size: 12px;
        padding: .5rem .6rem;
    }
    .member-source-table-wrap .btn-icon {
        width: 26px;
        height: 26px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        border-radius: 6px;
        flex-shrink: 0;
    }
    .member-source-table-wrap .btn-icon-group {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .member-source-table-wrap .mini-badge {
        font-size: 10px;
        padding: .25em .5em;
        font-weight: 600;
    }
</style>
