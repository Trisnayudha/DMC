{{-- Kartu kontak PIC: avatar inisial + nama/jabatan + email & telepon yang bisa diklik.
     Param: $pic (SponsorPic|null), $color (warna avatar, default ungu). --}}
@php $color = $color ?? '#6777ef'; @endphp
@if($pic)
    <div class="d-flex align-items-center" style="gap:8px;">
        <div style="width:30px;height:30px;border-radius:50%;background:{{ $color }};color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            {{ strtoupper(substr($pic->name, 0, 1)) }}
        </div>
        <div>
            <div style="font-size:12px;font-weight:600;color:#333;">{{ $pic->name }}</div>
            @if($pic->title)<div style="font-size:10px;color:#999;">{{ $pic->title }}</div>@endif
            @if($pic->email)
                <a href="mailto:{{ $pic->email }}" style="font-size:10px;color:#6777ef;display:block;">
                    <i class="fas fa-envelope" style="width:12px;"></i> {{ Str::limit($pic->email, 26) }}
                </a>
            @endif
            @if($pic->phone)
                <a href="tel:{{ $pic->phone }}" style="font-size:10px;color:#47c363;display:block;">
                    <i class="fas fa-phone" style="width:12px;"></i> {{ $pic->phone }}
                </a>
            @endif
        </div>
    </div>
@else
    <span style="font-size:11px;color:#bbb;"><i class="fas fa-user-slash mr-1"></i>No PIC</span>
@endif
