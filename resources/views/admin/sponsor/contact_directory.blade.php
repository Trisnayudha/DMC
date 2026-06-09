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

            <!-- Summary stats -->
            <div class="row mb-3">
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-primary"><i class="fas fa-building"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Active Sponsors</h4></div>
                            <div class="card-body">{{ $sponsors->count() }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-secondary"><i class="fas fa-user-tie"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Total PICs</h4></div>
                            <div class="card-body">{{ $totalPics }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-info"><i class="fas fa-user-friends"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Representatives</h4></div>
                            <div class="card-body">{{ $totalReps }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-success"><i class="fas fa-users"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Members</h4></div>
                            <div class="card-body">{{ $totalMembers }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search & Filter -->
            <div class="card mb-3">
                <div class="card-body py-2">
                    <form method="GET" action="{{ route('sponsors.contact-directory') }}" class="form-inline flex-wrap" style="gap:8px">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" name="search" class="form-control form-control-sm"
                                placeholder="Search sponsor name..." value="{{ $search }}" style="min-width:220px">
                        </div>
                        <select name="package" class="form-control form-control-sm" style="min-width:150px">
                            <option value="">All Packages</option>
                            <option value="platinum" {{ $package === 'platinum' ? 'selected' : '' }}>Platinum</option>
                            <option value="gold"     {{ $package === 'gold'     ? 'selected' : '' }}>Gold</option>
                            <option value="silver"   {{ $package === 'silver'   ? 'selected' : '' }}>Silver</option>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
                        @if($search || $package)
                            <a href="{{ route('sponsors.contact-directory') }}" class="btn btn-light btn-sm">Reset</a>
                        @endif
                        <span class="text-muted ml-2" style="font-size:13px">{{ $sponsors->count() }} sponsor{{ $sponsors->count() === 1 ? '' : 's' }} found</span>
                    </form>
                </div>
            </div>

            <!-- Directory Cards -->
            @forelse ($sponsors as $sponsor)
            <div class="card mb-2 sponsor-card"
                 data-name="{{ strtolower($sponsor->name) }}"
                 data-package="{{ $sponsor->package }}">
                <div class="card-header py-2 d-flex align-items-center justify-content-between"
                     style="cursor:pointer; border-left: 4px solid
                        @if($sponsor->package === 'platinum') #4e73df
                        @elseif($sponsor->package === 'gold') #f6c23e
                        @else #858796 @endif"
                     data-toggle="collapse"
                     data-target="#sponsor-{{ $sponsor->id }}">
                    <div class="d-flex align-items-center" style="gap:10px">
                        <div style="width:36px;height:36px;border-radius:50%;
                            background:@if($sponsor->package === 'platinum') #4e73df @elseif($sponsor->package === 'gold') #f6c23e @else #858796 @endif;
                            color:#fff;font-size:14px;font-weight:700;
                            display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            {{ strtoupper(substr($sponsor->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="font-weight-bold" style="font-size:14px">{{ $sponsor->name }}</div>
                            <div style="font-size:11px;margin-top:1px">
                                <span class="badge badge-sm
                                    @if($sponsor->package === 'platinum') badge-primary
                                    @elseif($sponsor->package === 'gold') badge-warning
                                    @else badge-secondary @endif">
                                    {{ ucfirst($sponsor->package) }}
                                </span>
                                <span class="text-muted ml-1">
                                    {{ $sponsor->pics->count() }} PIC &middot;
                                    {{ $sponsor->representatives->count() }} Rep &middot;
                                    {{ $sponsor->members->count() }} Member
                                </span>
                            </div>
                        </div>
                    </div>
                    <i class="fas fa-chevron-down text-muted" style="font-size:12px"></i>
                </div>

                <div class="collapse" id="sponsor-{{ $sponsor->id }}">
                    <div class="card-body p-0">
                        <div class="row no-gutters" style="font-size:13px">

                            {{-- PIC Column --}}
                            <div class="col-lg-4 col-md-12" style="border-right:1px solid #f0f0f0;padding:12px 16px">
                                <div class="text-uppercase text-muted font-weight-bold mb-2" style="font-size:10px;letter-spacing:.5px">
                                    <i class="fas fa-user-tie mr-1"></i> PIC
                                </div>
                                @if($sponsor->pics->isEmpty())
                                    <span class="text-muted" style="font-size:12px"><i class="fas fa-user-slash"></i> No PIC assigned</span>
                                @else
                                    @foreach($sponsor->pics as $pic)
                                        <div class="d-flex align-items-start mb-2" style="gap:8px">
                                            <div style="width:28px;height:28px;border-radius:50%;background:#6c757d;color:#fff;font-size:11px;font-weight:600;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                                {{ strtoupper(substr($pic->name, 0, 1)) }}
                                            </div>
                                            <div style="line-height:1.4">
                                                <div class="font-weight-bold">{{ $pic->name }}</div>
                                                @if($pic->title)
                                                    <div class="text-muted" style="font-size:11px">{{ $pic->title }}</div>
                                                @endif
                                                <div class="d-flex flex-wrap mt-1" style="gap:6px">
                                                    @if($pic->email)
                                                        <a href="mailto:{{ $pic->email }}" class="text-primary" style="font-size:11px">
                                                            <i class="fas fa-envelope"></i> {{ $pic->email }}
                                                        </a>
                                                    @endif
                                                    @if($pic->phone)
                                                        <a href="tel:{{ $pic->phone }}" class="text-success" style="font-size:11px">
                                                            <i class="fas fa-phone"></i> {{ $pic->phone }}
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            {{-- Representatives Column --}}
                            <div class="col-lg-4 col-md-12" style="border-right:1px solid #f0f0f0;padding:12px 16px">
                                <div class="text-uppercase text-muted font-weight-bold mb-2" style="font-size:10px;letter-spacing:.5px">
                                    <i class="fas fa-user-friends mr-1"></i> Representatives
                                </div>
                                @if($sponsor->representatives->isEmpty())
                                    <span class="text-muted" style="font-size:12px"><i class="fas fa-user-slash"></i> No representatives</span>
                                @else
                                    @foreach($sponsor->representatives as $rep)
                                        <div class="d-flex align-items-start mb-2" style="gap:8px">
                                            <div style="width:28px;height:28px;border-radius:50%;background:#007bff;color:#fff;font-size:11px;font-weight:600;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                                {{ strtoupper(substr($rep->name, 0, 1)) }}
                                            </div>
                                            <div style="line-height:1.4">
                                                <div class="font-weight-bold">{{ $rep->name }}</div>
                                                @if($rep->job_title)
                                                    <div class="text-muted" style="font-size:11px">{{ $rep->job_title }}</div>
                                                @endif
                                                <div class="d-flex flex-wrap mt-1" style="gap:6px">
                                                    @if($rep->email)
                                                        <a href="mailto:{{ $rep->email }}" class="text-primary" style="font-size:11px">
                                                            <i class="fas fa-envelope"></i> {{ $rep->email }}
                                                        </a>
                                                    @endif
                                                    @if($rep->instagram)
                                                        <a href="https://instagram.com/{{ ltrim($rep->instagram, '@') }}" target="_blank" class="text-danger" style="font-size:11px">
                                                            <i class="fab fa-instagram"></i> {{ $rep->instagram }}
                                                        </a>
                                                    @endif
                                                    @if($rep->linkedin)
                                                        <a href="{{ $rep->linkedin }}" target="_blank" class="text-info" style="font-size:11px">
                                                            <i class="fab fa-linkedin"></i> LinkedIn
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            {{-- Members Column --}}
                            <div class="col-lg-4 col-md-12" style="padding:12px 16px">
                                <div class="text-uppercase text-muted font-weight-bold mb-2" style="font-size:10px;letter-spacing:.5px">
                                    <i class="fas fa-users mr-1"></i> Members
                                </div>
                                @if($sponsor->members->isEmpty())
                                    <span class="text-muted" style="font-size:12px"><i class="fas fa-user-slash"></i> No members</span>
                                @else
                                    @foreach($sponsor->members as $member)
                                        <div class="d-flex align-items-start mb-2" style="gap:8px">
                                            <div style="width:28px;height:28px;border-radius:50%;background:#28a745;color:#fff;font-size:11px;font-weight:600;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                                {{ strtoupper(substr($member->name, 0, 1)) }}
                                            </div>
                                            <div style="line-height:1.4">
                                                <div class="font-weight-bold">{{ $member->name }}</div>
                                                @if($member->status_member)
                                                    <div class="text-muted" style="font-size:11px">{{ $member->status_member }}</div>
                                                @endif
                                                <div class="d-flex flex-wrap mt-1" style="gap:6px">
                                                    @if($member->email)
                                                        <a href="mailto:{{ $member->email }}" class="text-primary" style="font-size:11px">
                                                            <i class="fas fa-envelope"></i> {{ $member->email }}
                                                        </a>
                                                    @endif
                                                    @if($member->fullphone)
                                                        <a href="tel:{{ $member->fullphone }}" class="text-success" style="font-size:11px">
                                                            <i class="fas fa-phone"></i> {{ $member->fullphone }}
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            </div>
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
