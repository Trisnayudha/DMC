{{-- Year-over-year delta badge: current year's count vs last year's FINAL count.
     Params: $current (int), $previous (int|null), $year (int, current year — used to
     label the tooltip as "$year - 1"), $label (string, optional, what's being counted),
     $invert (bool, optional — set true for "bad when higher" metrics like Lost, so a
     rise shows red and a drop shows green). --}}
@php
    $label = $label ?? 'confirmed';
    $invert = $invert ?? false;
@endphp
@if($previous !== null && $previous > 0)
    @php
        $delta = $current - $previous;
        $isGood = $invert ? $delta < 0 : $delta > 0;
        $isBad  = $invert ? $delta > 0 : $delta < 0;
        $deltaColor = $isBad ? '#fc544b' : ($isGood ? '#47c363' : '#888');
        $deltaText = ($delta > 0 ? '+' : '') . $delta;
    @endphp
    <div style="font-size:11px;font-weight:700;margin-top:2px;color:{{ $deltaColor }};display:inline-flex;align-items:center;gap:3px;">
        {{ $deltaText }}
        <i class="fas fa-info-circle" style="font-size:10px;color:#aaa;font-weight:400;cursor:help;" data-toggle="tooltip"
            title="vs {{ $year - 1 }} final: {{ $previous }} {{ $label }}"></i>
    </div>
@endif
