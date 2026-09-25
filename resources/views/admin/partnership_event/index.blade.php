@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Partnership Event Visitors</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active">Partnership Event Visitors</div>
                </div>
            </div>

            <div class="section-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible show fade">
                        <div class="alert-body">
                            <button class="close" data-dismiss="alert"><span>×</span></button>
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Pilih Partnership Event</h4>
                    </div>

                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.partnership_events.index') }}" class="mb-3">
                            <div class="form-row align-items-end">
                                <div class="form-group col-md-5 mb-2">
                                    <label class="mb-1">Cari Event</label>
                                    <input type="text" name="search" class="form-control" value="{{ $search }}"
                                        placeholder="contoh: Mining Indonesia 2026">
                                </div>
                                <div class="form-group col-md-3 mb-2" style="display:flex; gap:8px;">
                                    <button type="submit" class="btn btn-primary btn-block">Filter</button>
                                    <a href="{{ route('admin.partnership_events.index') }}" class="btn btn-outline-secondary btn-block">Reset</a>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Event Name</th>
                                        <th>Event Type</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Location</th>
                                        <th>Giveaway</th>
                                        <th>Visitors</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($events as $idx => $event)
                                        <tr>
                                            <td>{{ $events->firstItem() + $idx }}</td>
                                            <td>{{ $event->name }}</td>
                                            <td><span class="badge badge-light">{{ $event->event_type }}</span></td>
                                            <td>{{ $event->start_date ? \Carbon\Carbon::parse($event->start_date)->format('d M Y') : '-' }}</td>
                                            <td>{{ $event->end_date ? \Carbon\Carbon::parse($event->end_date)->format('d M Y') : '-' }}</td>
                                            <td>{{ $event->location ?: '-' }}</td>
                                            <td>
                                                @if ($event->has_giveaway)
                                                    <span class="badge badge-success" title="Giveaway booth aktif untuk event ini"><i class="fas fa-gift"></i> ON</span>
                                                @else
                                                    <span class="badge badge-secondary" title="Giveaway booth nonaktif">OFF</span>
                                                @endif
                                            </td>
                                            <td><span class="badge badge-primary">{{ $event->visitors_count }}</span></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.partnership_events.show', $event->slug) }}" class="btn btn-sm btn-info" title="Kelola Visitor">
                                                        <i class="fas fa-users"></i> Kelola
                                                    </a>
                                                    <a href="{{ url('visit/' . $event->slug) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Buka Form Booth Visitor (Link untuk QR Code)">
                                                        <i class="fas fa-external-link-alt"></i> Form Booth
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">
                                                Belum ada event dengan tipe "Partnership Event". Buat/tandai event tersebut lebih dulu di menu Events.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{ $events->links() }}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
