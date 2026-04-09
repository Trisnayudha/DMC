@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Participants</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active">Event Participants</div>
                </div>
            </div>

            <div class="section-body">

                {{-- ── STAT CARDS ── --}}
                <div class="stat-grid">
                    <div class="stat-card stat-blue">
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-info">
                            <div class="stat-number">{{ $checkin }}</div>
                            <div class="stat-label">Check-in</div>
                        </div>
                    </div>
                    <div class="stat-card stat-orange">
                        <div class="stat-icon"><i class="fas fa-clock"></i></div>
                        <div class="stat-info">
                            <div class="stat-number">{{ $absent }}</div>
                            <div class="stat-label">Belum Hadir</div>
                        </div>
                    </div>
                    <div class="stat-card stat-green">
                        <div class="stat-icon"><i class="fas fa-id-badge"></i></div>
                        <div class="stat-info">
                            <div class="stat-number">{{ $memberCount }}</div>
                            <div class="stat-label">Member</div>
                        </div>
                    </div>
                    <div class="stat-card stat-amber">
                        <div class="stat-icon"><i class="fas fa-user-tag"></i></div>
                        <div class="stat-info">
                            <div class="stat-number">{{ $nonMemberCount }}</div>
                            <div class="stat-label">Non Member</div>
                        </div>
                    </div>
                    <div class="stat-card stat-purple">
                        <div class="stat-icon"><i class="fas fa-star"></i></div>
                        <div class="stat-info">
                            <div class="stat-number">{{ $sponsorCount }}</div>
                            <div class="stat-label">Sponsor</div>
                        </div>
                    </div>
                    <div class="stat-card stat-yellow">
                        <div class="stat-icon"><i class="fas fa-star"></i></div>
                        <div class="stat-info">
                            <div class="stat-number">{{ $freeCount }}</div>
                            <div class="stat-label">Free/Invitation</div>
                        </div>
                    </div>
                </div>

                {{-- ── MAIN CARD ── --}}
                <div class="card shadow-sm border-0">
                    <div class="card-header border-0 d-flex align-items-center justify-content-between flex-wrap gap-2"
                        style="background:#fff; padding:1.25rem 1.5rem;">
                        <div>
                            <h5 class="mb-0 fw-bold" style="color:#1a202c;">
                                <i class="fas fa-users text-primary mr-2"></i>Daftar Peserta
                            </h5>
                            <small class="text-muted">Total {{ $list->count() }} peserta terdaftar</small>
                        </div>

                        {{-- Filter Pills --}}
                        <div class="filter-pills">
                            <button class="pill pill-all active" data-filter="all">
                                <i class="fas fa-th-large mr-1"></i>Semua
                                <span class="pill-count">{{ $list->count() }}</span>
                            </button>
                            <button class="pill pill-member" data-filter="member">
                                <i class="fas fa-id-badge mr-1"></i>Member
                                <span class="pill-count">{{ $memberCount }}</span>
                            </button>
                            <button class="pill pill-nonmember" data-filter="non-member">
                                <i class="fas fa-user-tag mr-1"></i>Non Member
                                <span class="pill-count">{{ $nonMemberCount }}</span>
                            </button>
                            <button class="pill pill-sponsor" data-filter="sponsor">
                                <i class="fas fa-star mr-1"></i>Sponsor
                                <span class="pill-count">{{ $sponsorCount }}</span>
                            </button>
                            <button class="pill pill-free" data-filter="free">
                                <i class="fas fa-star mr-1"></i>Invitation/Free
                                <span class="pill-count">{{ $freeCount }}</span>
                            </button>
                        </div>
                    </div>

                    <div class="card-body" style="padding:0 1.5rem 1.5rem;">

                        @if ($errors->any())
                            <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>Whoops!</strong> @lang('general.validation_error_message')
                                <ul class="mb-0 mt-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                                <i class="fas fa-times-circle mr-2"></i>{{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                            </div>
                        @endif

                        <div class="table-responsive mt-3">
                            <table id="laravel_crud" class="table table-hover participant-table">
                                <thead>
                                    <tr>
                                        <th width="40">#</th>
                                        <th>Tanggal Daftar</th>
                                        <th>Kode</th>
                                        <th>Package</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Jabatan</th>
                                        <th>Perusahaan</th>
                                        <th>No. Telepon</th>
                                        <th>Alamat</th>
                                        @if ($list[0]['end_date'] <= date('Y-m-d'))
                                            <th>Kategori</th>
                                            <th>Negara</th>
                                            <th>No. Kantor</th>
                                        @endif
                                        @if ($list[0]['end_date'] >= date('Y-m-d'))
                                            <th>Email Konfirmasi</th>
                                            <th>WA Konfirmasi</th>
                                            <th>WA Manual</th>
                                        @endif
                                        <th>Kehadiran</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; ?>
                                    @foreach ($list as $post)
                                        @php
                                            $pkg = strtolower($post->package ?? '');
                                            if (str_contains($pkg, 'non')) {
                                                $pkgClass = 'badge-nonmember';
                                                $pkgIcon = 'fa-user-tag';
                                            } elseif (str_contains($pkg, 'sponsor')) {
                                                $pkgClass = 'badge-sponsor';
                                                $pkgIcon = 'fa-star';
                                            } elseif (in_array($pkg, ['member', 'premium'])) {
                                                $pkgClass = 'badge-member';
                                                $pkgIcon = 'fa-id-badge';
                                            } else {
                                                $pkgClass = 'badge-member';
                                                $pkgIcon = 'fa-id-badge';
                                            }
                                        @endphp
                                        <tr id="row_{{ $post->id }}">
                                            <td class="text-muted" style="font-size:.8rem;">{{ $no++ }}</td>
                                            <td>
                                                <span style="font-size:.82rem;">
                                                    {{ date('d M Y', strtotime($post->payment_updated)) }}
                                                </span><br>
                                                <small
                                                    class="text-muted">{{ date('H:i', strtotime($post->payment_updated)) }}</small>
                                            </td>
                                            <td>
                                                <code
                                                    style="font-size:.78rem; color:#6366f1;">{{ $post->code_payment }}</code>
                                            </td>
                                            <td data-pkg="{{ $pkg }}">
                                                <span class="pkg-badge {{ $pkgClass }}">
                                                    <i class="fas {{ $pkgIcon }} mr-1"></i>{{ $post->package }}
                                                </span>
                                            </td>
                                            <td class="fw-semibold">{{ $post->name }}</td>
                                            <td>
                                                <a href="mailto:{{ $post->email }}"
                                                    style="color:#6366f1; font-size:.83rem;">{{ $post->email }}</a>
                                            </td>
                                            <td style="font-size:.83rem;">{{ $post->job_title }}</td>
                                            <td style="font-size:.83rem;">
                                                {{ $post->company_name . ($post->prefix ? ', ' . $post->prefix : '') }}
                                            </td>
                                            <td style="font-size:.83rem;">
                                                {{ $post->prefix_phone != null ? $post->fullphone : $post->phone }}
                                            </td>
                                            <td style="font-size:.83rem; max-width:160px; white-space:normal;">
                                                {{ $post->address }}</td>

                                            @if ($post->end_date <= date('Y-m-d'))
                                                <td style="font-size:.83rem;">
                                                    {{ $post->company_category != 'other' ? $post->company_category : $post->company_other }}
                                                </td>
                                                <td style="font-size:.83rem;">{{ $post->country }}</td>
                                                <td style="font-size:.83rem;">{{ $post->office_number }}</td>
                                            @endif

                                            @if ($post->end_date >= date('Y-m-d'))
                                                {{-- Email Konfirmasi --}}
                                                <td>
                                                    @if ($post->reminder == null)
                                                        <form action="{{ Route('events-send-participant') }}"
                                                            method="post">
                                                            @csrf
                                                            <input type="hidden" name="users_id"
                                                                value="{{ $post->users_id }}">
                                                            <input type="hidden" name="events_id"
                                                                value="{{ $post->events_id }}">
                                                            <input type="hidden" name="payment_id"
                                                                value="{{ $post->payment_id }}">
                                                            <input type="hidden" name="method" value="confirmation">
                                                            <button class="action-btn btn-send send"
                                                                title="Kirim Email Konfirmasi">
                                                                <i class="fas fa-envelope"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <div class="sent-info">
                                                            <i class="fas fa-check-circle text-success mr-1"></i>
                                                            <span>{{ date('d M H:i', strtotime($post->reminder)) }}</span><br>
                                                            <small class="text-muted">{{ $post->name_reminder }}</small>
                                                        </div>
                                                    @endif
                                                </td>

                                                {{-- WA Konfirmasi --}}
                                                <td>
                                                    @if ($post->reminder_wa == null)
                                                        <button type="button" class="action-btn btn-wa open-modal"
                                                            title="Kirim WA Konfirmasi"
                                                            data-users-id="{{ $post->users_id }}"
                                                            data-events-id="{{ $post->events_id }}"
                                                            data-payment-id="{{ $post->payment_id }}"
                                                            data-phone="{{ $post->prefix_phone != null ? $post->fullphone : $post->phone }}">
                                                            <i class="fab fa-whatsapp"></i>
                                                        </button>
                                                    @else
                                                        <div class="sent-info">
                                                            <i class="fas fa-check-circle text-success mr-1"></i>
                                                            <span>{{ date('d M H:i', strtotime($post->reminder_wa)) }}</span><br>
                                                            <small
                                                                class="text-muted">{{ $post->name_reminder_wa }}</small>
                                                        </div>
                                                    @endif
                                                </td>

                                                {{-- WA Manual --}}
                                                <td>
                                                    <button type="button" class="action-btn btn-wa-manual open-wa-direct"
                                                        title="WA Manual" data-users-id="{{ $post->users_id }}"
                                                        data-events-id="{{ $post->events_id }}"
                                                        data-payment-id="{{ $post->payment_id }}"
                                                        data-user-name="{{ $post->name }}"
                                                        data-event-name="{{ $post->event_name ?? ($event->name ?? 'Our Event') }}"
                                                        data-location="{{ $post->location ?? ($event->location ?? 'Jakarta, Indonesia') }}"
                                                        data-start-time="{{ isset($post->start_time) ? date('h.i a', strtotime($post->start_time)) : (isset($event->start_time) ? date('h.i a', strtotime($event->start_time)) : '01.30 pm') }}"
                                                        data-end-time="{{ isset($post->end_time) ? date('h.i a', strtotime($post->end_time)) : (isset($event->end_time) ? date('h.i a', strtotime($event->end_time)) : '06.00 pm') }}"
                                                        data-event-date="{{ isset($post->start_date)
                                                            ? \Carbon\Carbon::parse($post->start_date)->isoFormat('dddd, D MMMM YYYY')
                                                            : (isset($event->start_date)
                                                                ? \Carbon\Carbon::parse($event->start_date)->isoFormat('dddd, D MMMM YYYY')
                                                                : \Carbon\Carbon::parse($post->end_date)->isoFormat('dddd, D MMMM YYYY')) }}"
                                                        data-ticket-url="{{ $post->ticket_url ?? '' }}"
                                                        data-dest-phone="{{ $post->prefix_phone != null ? $post->fullphone : $post->phone }}">
                                                        <i class="fab fa-whatsapp"></i>&nbsp;<i class="fas fa-pen"
                                                            style="font-size:.65rem;"></i>
                                                    </button>
                                                </td>
                                            @endif

                                            {{-- Kehadiran --}}
                                            <td>
                                                @if ($post->present == null)
                                                    <form action="{{ Route('events-send-participant') }}" method="post">
                                                        @csrf
                                                        <input type="hidden" name="users_id"
                                                            value="{{ $post->users_id }}">
                                                        <input type="hidden" name="events_id"
                                                            value="{{ $post->events_id }}">
                                                        <input type="hidden" name="payment_id"
                                                            value="{{ $post->payment_id }}">
                                                        <input type="hidden" name="method" value="present">
                                                        <button class="action-btn btn-present present"
                                                            title="Tandai Hadir">
                                                            <i class="fas fa-user-check"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <div class="present-info">
                                                        <span class="present-badge">
                                                            <i class="fas fa-check mr-1"></i>Hadir
                                                        </span>
                                                        <div style="font-size:.75rem; color:#555; margin-top:3px;">
                                                            {{ date('d M H:i', strtotime($post->present)) }}
                                                        </div>
                                                        <small class="text-muted">{{ $post->name_present }}</small>
                                                        @if ($post->photo)
                                                            <button class="action-btn btn-photo btn-view-photo mt-1"
                                                                data-photo="{{ $post->photo }}" title="Lihat Foto">
                                                                <i class="fas fa-image"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>

    {{-- ── LOADER ── --}}
    <div id="loader" style="display:none">
        <div class="loader-box">
            <div class="loader-ring"></div>
            <div class="loader-text">Memproses...</div>
        </div>
    </div>

    {{-- ── MODAL: WA Konfirmasi ── --}}
    <div class="modal fade" id="phoneModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow">
                <form id="phoneForm" method="post" action="{{ route('events-send-participant') }}">
                    @csrf
                    <div class="modal-header border-0" style="background:#f0fdf4; padding:1.25rem 1.5rem;">
                        <div class="d-flex align-items-center">
                            <div class="modal-icon-wrap bg-success mr-3">
                                <i class="fab fa-whatsapp text-white"></i>
                            </div>
                            <div>
                                <h5 class="modal-title mb-0 fw-bold">Kirim WA Konfirmasi</h5>
                                <small class="text-muted">Periksa nomor sebelum mengirim</small>
                            </div>
                        </div>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body" style="padding:1.5rem;">
                        <div class="form-group mb-0">
                            <label class="form-label fw-semibold">Nomor WhatsApp</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                </div>
                                <input type="text" class="form-control" id="phoneNumber" name="phone"
                                    placeholder="08xxxxxxxxxx" required>
                            </div>
                        </div>
                        <input type="hidden" name="users_id" id="usersId">
                        <input type="hidden" name="events_id" id="eventsId">
                        <input type="hidden" name="payment_id" id="paymentId">
                        <input type="hidden" name="method" value="confirmation_wa">
                    </div>
                    <div class="modal-footer border-0" style="padding:.75rem 1.5rem 1.25rem;">
                        <button type="button" class="btn btn-light px-4" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success px-4">
                            <i class="fab fa-whatsapp mr-1"></i>Kirim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── MODAL: WA Manual ── --}}
    <div class="modal fade" id="waDirectModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0" style="background:#f0fdf4; padding:1.25rem 1.5rem;">
                    <div class="d-flex align-items-center">
                        <div class="modal-icon-wrap bg-success mr-3">
                            <i class="fab fa-whatsapp text-white" style="font-size:1.3rem;"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0 fw-bold">WA Manual — Generate & Copy</h5>
                            <small class="text-muted">Generate template lalu copy ke WhatsApp</small>
                        </div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body" style="padding:1.5rem;">
                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label class="form-label fw-semibold">Nomor Tujuan</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                </div>
                                <input type="text" class="form-control" id="waDestPhone" placeholder="0811xxxxxxx">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="btnCopyPhone">
                                        <i class="fas fa-copy mr-1"></i>Copy
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted">Akan diformat jadi 62xxxxxxxxx untuk wa.me</small>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label fw-semibold">Format wa.me</label>
                            <input type="text" class="form-control bg-light" id="waNormPhone" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label fw-semibold">Link wa.me</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-link"></i></span>
                            </div>
                            <input type="text" class="form-control bg-light" id="waLink" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="btnCopyLink">
                                    <i class="fas fa-copy mr-1"></i>Copy
                                </button>
                            </div>
                        </div>
                        <small class="text-muted">Link tanpa pesan — buka chat kosong ke nomor ini.</small>
                    </div>

                    <div class="form-group mb-1">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0">Template Pesan</label>
                            <div>
                                <button class="btn btn-outline-primary btn-sm mr-1" type="button" id="btnGenerate">
                                    <i class="fas fa-magic mr-1"></i>Generate
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" type="button" id="btnCopyText">
                                    <i class="fas fa-copy mr-1"></i>Copy Pesan
                                </button>
                            </div>
                        </div>
                        <textarea class="form-control" id="waDirectText" rows="9"
                            placeholder="Klik 'Generate' untuk membuat template pesan..."></textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted"><span id="waTextCount">0</span> karakter</small>
                            <small class="text-muted">Pengirim: <strong>08111937399</strong></small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0" style="padding:.75rem 1.5rem 1.25rem;">
                    <button class="btn btn-light px-4" data-dismiss="modal">Tutup</button>
                    <button class="btn btn-success px-4" id="waDirectOpen">
                        <i class="fab fa-whatsapp mr-1"></i>Buka WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── MODAL: Foto ── --}}
    <div class="modal fade" id="photoModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0">
                    <h6 class="modal-title fw-bold"><i class="fas fa-image mr-2 text-info"></i>Foto Peserta</h6>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body text-center p-3">
                    <img id="previewPhoto" src="" class="img-fluid rounded shadow-sm">
                </div>
            </div>
        </div>
    </div>

@endsection

@push('top')
    <style>
        /* ── STAT GRID ── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 992px) {
            .stat-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 576px) {
            .stat-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .stat-card {
            border-radius: 16px;
            padding: 1.25rem 1.1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #fff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .1);
            transition: transform .2s, box-shadow .2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 28px rgba(0, 0, 0, .15);
        }

        .stat-blue {
            background: linear-gradient(135deg, #4f8ef7 0%, #2563eb 100%);
        }

        .stat-orange {
            background: linear-gradient(135deg, #fb923c 0%, #ea580c 100%);
        }

        .stat-green {
            background: linear-gradient(135deg, #34d399 0%, #059669 100%);
        }

        .stat-amber {
            background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%);
        }

        .stat-purple {
            background: linear-gradient(135deg, #a78bfa 0%, #7c3aed 100%);
        }

        .stat-yellow {
            background: linear-gradient(135deg, #faeb8b 0%, #a2d80d 100%);
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            background: rgba(255, 255, 255, .2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .stat-number {
            font-size: 1.9rem;
            font-weight: 800;
            line-height: 1;
        }

        .stat-label {
            font-size: .78rem;
            opacity: .88;
            margin-top: 3px;
            font-weight: 500;
            letter-spacing: .3px;
        }

        /* ── FILTER PILLS ── */
        .filter-pills {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .pill {
            border: none;
            cursor: pointer;
            border-radius: 50px;
            padding: .38rem 1rem;
            font-size: .8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            transition: all .2s;
            background: #f1f5f9;
            color: #64748b;
        }

        .pill:hover {
            background: #e2e8f0;
            color: #334155;
        }

        .pill-count {
            background: rgba(0, 0, 0, .12);
            border-radius: 20px;
            padding: 1px 7px;
            font-size: .72rem;
            font-weight: 700;
        }

        .pill-all.active {
            background: #1e293b;
            color: #fff;
        }

        .pill-member.active {
            background: #059669;
            color: #fff;
        }

        .pill-nonmember.active {
            background: #d97706;
            color: #fff;
        }

        .pill-sponsor.active {
            background: #7c3aed;
            color: #fff;
        }

        .pill-free.active {
            background: #a2d80d;
            color: #fff;
        }

        /* ── TABLE ── */
        .participant-table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .participant-table thead th {
            background: #f8fafc;
            color: #64748b;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .6px;
            text-transform: uppercase;
            border-top: none;
            border-bottom: 2px solid #e2e8f0;
            padding: .75rem .9rem;
            white-space: nowrap;
        }

        .participant-table tbody td {
            padding: .7rem .9rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-size: .83rem;
            color: #374151;
        }

        .participant-table tbody tr:hover td {
            background: #f8faff;
        }

        .participant-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* ── PACKAGE BADGES ── */
        .pkg-badge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .28rem .75rem;
            border-radius: 50px;
            font-size: .72rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-member {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-nonmember {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-sponsor {
            background: #ede9fe;
            color: #5b21b6;
        }

        /* ── ACTION BUTTONS ── */
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: .9rem;
            transition: all .18s;
        }

        .btn-send {
            background: #dbeafe;
            color: #2563eb;
        }

        .btn-send:hover {
            background: #2563eb;
            color: #fff;
        }

        .btn-wa {
            background: #dcfce7;
            color: #16a34a;
        }

        .btn-wa:hover {
            background: #16a34a;
            color: #fff;
        }

        .btn-wa-manual {
            background: #f0fdf4;
            color: #15803d;
            border: 1.5px dashed #86efac;
            width: auto;
            padding: 0 .7rem;
            font-size: .8rem;
        }

        .btn-wa-manual:hover {
            background: #15803d;
            color: #fff;
            border-color: #15803d;
        }

        .btn-present {
            background: #f0fdf4;
            color: #059669;
        }

        .btn-present:hover {
            background: #059669;
            color: #fff;
        }

        .btn-photo {
            background: #e0f2fe;
            color: #0284c7;
            width: auto;
            padding: 0 .6rem;
            font-size: .75rem;
            border-radius: 6px;
            height: 28px;
        }

        .btn-photo:hover {
            background: #0284c7;
            color: #fff;
        }

        /* ── SENT / PRESENT INFO ── */
        .sent-info {
            font-size: .78rem;
            line-height: 1.4;
            color: #374151;
        }

        .present-info {
            display: flex;
            flex-direction: column;
            gap: 1px;
        }

        .present-badge {
            display: inline-flex;
            align-items: center;
            background: #dcfce7;
            color: #15803d;
            border-radius: 50px;
            padding: .22rem .65rem;
            font-size: .72rem;
            font-weight: 700;
            width: fit-content;
        }

        /* ── LOADER ── */
        #loader {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, .85);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loader-box {
            text-align: center;
        }

        .loader-ring {
            width: 56px;
            height: 56px;
            border: 5px solid #e2e8f0;
            border-top-color: #6366f1;
            border-radius: 50%;
            animation: spin .8s linear infinite;
            margin: 0 auto 12px;
        }

        .loader-text {
            font-size: .85rem;
            color: #64748b;
            font-weight: 600;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ── MODAL ICON ── */
        .modal-icon-wrap {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        /* ── MISC ── */
        .fw-bold {
            font-weight: 700 !important;
        }

        .fw-semibold {
            font-weight: 600 !important;
        }

        .card {
            border-radius: 16px !important;
        }

        code {
            background: #ede9fe;
            border-radius: 5px;
            padding: 2px 6px;
        }
    </style>
@endpush

@push('bottom')
    <script>
        $(document).ready(function() {

            /* ── DataTable ── */
            var table = $('#laravel_crud').DataTable({
                dom: 'Bfrtip',
                pageLength: 25,
                language: {
                    search: '<i class="fas fa-search"></i>',
                    searchPlaceholder: 'Cari peserta...',
                    lengthMenu: 'Tampilkan _MENU_ baris',
                    info: 'Menampilkan _START_–_END_ dari _TOTAL_ peserta',
                    paginate: {
                        previous: '‹',
                        next: '›'
                    }
                },
                buttons: [{
                        extend: 'copyHtml5',
                        className: 'btn btn-sm btn-light border',
                        text: '<i class="fas fa-copy mr-1"></i>Copy'
                    },
                    {
                        extend: 'excelHtml5',
                        className: 'btn btn-sm btn-light border',
                        text: '<i class="fas fa-file-excel mr-1"></i>Excel'
                    },
                    {
                        extend: 'csvHtml5',
                        className: 'btn btn-sm btn-light border',
                        text: '<i class="fas fa-file-csv mr-1"></i>CSV'
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-sm btn-light border',
                        text: '<i class="fas fa-file-pdf mr-1"></i>PDF'
                    }
                ]
            });

            /* ── Filter Pills – pakai data-pkg di <td> ── */
            var activeFilter = 'all';

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (activeFilter === 'all') return true;
                var row = table.row(dataIndex).node();
                var pkg = $(row).find('td[data-pkg]').data('pkg') || '';
                if (activeFilter === 'member') return pkg === 'member' || pkg === 'premium';
                if (activeFilter === 'non-member') return pkg.indexOf('non') !== -1;
                if (activeFilter === 'sponsor') return pkg.indexOf('sponsor') !== -1;
                if (activeFilter === 'free') return pkg.indexOf('free') !== -1;
                return true;
            });

            $(document).on('click', '.pill', function() {
                $('.pill').removeClass('active');
                $(this).addClass('active');
                activeFilter = $(this).data('filter');
                table.draw();
            });

            /* ── Loader: Email / Present ── */
            $(".send, .present").on("click", function() {
                $("#loader").show();
                setTimeout(() => $("#loader").hide(), 120000);
            });

            /* ── Modal: WA Konfirmasi ── */
            $(document).on('click', '.open-modal', function() {
                $('#usersId').val($(this).data('users-id'));
                $('#eventsId').val($(this).data('events-id'));
                $('#paymentId').val($(this).data('payment-id'));
                $('#phoneNumber').val($(this).data('phone'));
                $('#phoneModal').modal('show');
            });
        });

        /* ── WA Manual ── */
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        function normalizePhone(phone) {
            let p = (phone || '').toString().trim().replace(/[^\d+]/g, '');
            if (p.startsWith('+')) p = p.slice(1);
            p = p.replace(/[^\d]/g, '');
            if (p.startsWith('0')) p = '62' + p.slice(1);
            if (!p.startsWith('62') && p.length > 0) p = '62' + p;
            return p;
        }

        function updateLinkOnly() {
            const norm = normalizePhone($('#waDestPhone').val());
            $('#waNormPhone').val(norm);
            $('#waLink').val(norm ? `https://wa.me/${norm}` : '');
        }

        async function copyToClipboard(text) {
            try {
                await navigator.clipboard.writeText(text);
                return true;
            } catch (e) {
                const ta = document.createElement('textarea');
                ta.value = text;
                document.body.appendChild(ta);
                ta.select();
                const ok = document.execCommand('copy');
                document.body.removeChild(ta);
                return ok;
            }
        }

        let ctxUsersId = null,
            ctxEventsId = null,
            ctxPaymentId = null;

        $(document).on('click', '.open-wa-direct', function() {
            ctxUsersId = $(this).data('users-id');
            ctxEventsId = $(this).data('events-id');
            ctxPaymentId = $(this).data('payment-id');
            $('#waDestPhone').val($(this).data('dest-phone') || '');
            $('#waDirectText').val('');
            $('#waTextCount').text('0');
            updateLinkOnly();
            $('#waDirectModal').modal('show');
        });

        $('#waDestPhone').on('input', updateLinkOnly);
        $('#waDirectText').on('input', function() {
            $('#waTextCount').text($(this).val().length);
        });

        $('#btnCopyPhone').on('click', async function() {
            const ok = await copyToClipboard($('#waDestPhone').val() || '');
            $(this).html(ok ? '<i class="fas fa-check mr-1"></i>Tersalin!' :
                '<i class="fas fa-copy mr-1"></i>Copy');
            setTimeout(() => $(this).html('<i class="fas fa-copy mr-1"></i>Copy'), 1800);
        });

        $('#btnCopyLink').on('click', async function() {
            const ok = await copyToClipboard($('#waLink').val() || '');
            $(this).html(ok ? '<i class="fas fa-check mr-1"></i>Tersalin!' :
                '<i class="fas fa-copy mr-1"></i>Copy');
            setTimeout(() => $(this).html('<i class="fas fa-copy mr-1"></i>Copy'), 1800);
        });

        $('#btnGenerate').on('click', async function() {
            if (!(ctxUsersId && ctxEventsId && ctxPaymentId)) return alert('Data peserta tidak lengkap.');
            const norm = normalizePhone($('#waDestPhone').val());
            $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Generating...');
            try {
                const res = await $.post("{{ route('events-generate-wa-template') }}", {
                    users_id: ctxUsersId,
                    events_id: ctxEventsId,
                    payment_id: ctxPaymentId,
                    phone: norm
                });
                if (res && res.ok) {
                    $('#waDirectText').val(res.message);
                    $('#waTextCount').text(res.message.length);
                    $(this).html('<i class="fas fa-check mr-1"></i>Berhasil!').removeClass(
                        'btn-outline-primary').addClass('btn-success');
                    setTimeout(() => {
                        $(this).prop('disabled', false).html(
                                '<i class="fas fa-magic mr-1"></i>Generate')
                            .removeClass('btn-success').addClass('btn-outline-primary');
                    }, 2000);
                } else {
                    alert('Gagal generate template.');
                }
            } catch (e) {
                console.error(e);
                alert('Error generate template.');
            } finally {
                if ($(this).prop('disabled') && !$(this).hasClass('btn-success')) $(this).prop('disabled',
                    false).html('<i class="fas fa-magic mr-1"></i>Generate');
            }
        });

        $('#btnCopyText').on('click', async function() {
            const ok = await copyToClipboard($('#waDirectText').val() || '');
            $(this).html(ok ? '<i class="fas fa-check mr-1"></i>Tersalin!' :
                '<i class="fas fa-copy mr-1"></i>Copy Pesan');
            setTimeout(() => $(this).html('<i class="fas fa-copy mr-1"></i>Copy Pesan'), 1800);
        });

        $('#waDirectOpen').on('click', function() {
            const link = $('#waLink').val();
            if (!link) return alert('Link kosong / nomor tidak valid.');
            window.open(link, '_blank');
        });

        $(document).on('click', '.btn-view-photo', function() {
            $('#previewPhoto').attr('src', $(this).data('photo'));
            $('#photoModal').modal('show');
        });
    </script>
@endpush
