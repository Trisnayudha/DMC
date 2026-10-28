@extends('layouts.inspire.master')
@section('content')
<div class="content-wrapper">
    <section class="section">
        <div class="section-header"><h1>Dashboard</h1></div>
        <div class="section-body">
            <div class="card"><div class="card-body">
                <h5>Hi, {{ auth()->user()->name }}</h5>
                <p class="mb-0">Level: <strong>{{ auth()->user()->roleLabel() }}</strong>. Pilih menu yang tersedia untuk tim Anda.</p>
            </div></div>
            @php $hasMenu = false; @endphp
            @foreach ($navigation as $group => $items)
                @if ($group !== 'Main')
                    @php $hasMenu = true; @endphp
                    <div class="card"><div class="card-header"><h4>{{ $group }}</h4></div><div class="card-body d-flex flex-wrap" style="gap:10px;">
                        @foreach ($items as $item)
                            @foreach ($item['children'] ?? [$item] as $link)
                                <a class="btn btn-outline-primary" href="{{ url('admin/' . $link['path']) }}"><i class="fas fa-{{ $link['icon'] }} mr-1"></i>{{ $link['label'] }}</a>
                            @endforeach
                        @endforeach
                    </div></div>
                @endif
            @endforeach
            @unless ($hasMenu)
                <div class="alert alert-info">Akses menu tim belum diatur. Hubungi Superadmin untuk mengaktifkan menu yang diperlukan.</div>
            @endunless
        </div>
    </section>
</div>
@endsection
