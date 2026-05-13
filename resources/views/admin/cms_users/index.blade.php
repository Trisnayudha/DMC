@extends('layouts.inspire.master')

@section('content')
<div class="content-wrapper">
    <section class="section">
        <div class="section-header">
            <h1>CMS Users</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item active">CMS Users</div>
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
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible show fade">
                    <div class="alert-body">
                        <button class="close" data-dismiss="alert"><span>×</span></button>
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            <div class="row">

                {{-- Left: Admin user list --}}
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">
                                <i class="fas fa-user-shield mr-2"></i>Admin Users
                            </h4>
                            <span class="badge badge-primary" style="font-size:13px;">{{ $admins->count() }} admin</span>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="40">#</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th width="130">Terdaftar</th>
                                        <th width="90">Role</th>
                                        <th width="80" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($admins as $i => $admin)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center" style="gap:8px;">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white"
                                                        style="width:32px; height:32px; font-size:13px; font-weight:600; flex-shrink:0;">
                                                        {{ strtoupper(substr($admin->name ?? '?', 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div style="font-weight:600; font-size:13px;">{{ $admin->name ?: '(no name)' }}</div>
                                                        @if ($admin->id === auth()->id())
                                                            <span class="badge badge-light" style="font-size:10px;">You</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td><small>{{ $admin->email }}</small></td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $admin->created_at ? \Carbon\Carbon::parse($admin->created_at)->format('d M Y') : '-' }}
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary">admin</span>
                                            </td>
                                            <td class="text-center">
                                                @if ($admin->id !== auth()->id())
                                                    <form method="POST" action="{{ route('admin.cms_users.revoke', $admin->id) }}"
                                                        onsubmit="return confirm('Cabut role admin dari {{ addslashes($admin->name ?: $admin->email) }}?')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-xs btn-outline-danger" title="Cabut admin role">
                                                            <i class="fas fa-user-minus"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-muted" style="font-size:11px;">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Right: Add admin --}}
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0"><i class="fas fa-user-plus mr-2"></i>Tambah Admin</h4>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                Masukkan email user yang sudah terdaftar di sistem untuk diberikan akses admin.
                            </p>
                            <form method="POST" action="{{ route('admin.cms_users.assign') }}">
                                @csrf
                                <div class="form-group">
                                    <label class="small font-weight-bold">Email User</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                        placeholder="email@domain.com" value="{{ old('email') }}" required autofocus>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-user-shield mr-1"></i> Jadikan Admin
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Info: Other roles --}}
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0"><i class="fas fa-info-circle mr-2"></i>Roles di Sistem</h4>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Role</th>
                                        <th class="text-right">Jumlah User</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($roleSummary as $role)
                                        <tr>
                                            <td>
                                                <span class="badge badge-{{ $role->name === 'admin' ? 'primary' : ($role->name === 'manager' ? 'info' : 'secondary') }}">
                                                    {{ $role->name }}
                                                </span>
                                            </td>
                                            <td class="text-right">
                                                <strong>{{ $role->users_count }}</strong>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>
@endsection
