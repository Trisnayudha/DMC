{{-- Tahap proses renewal (Request Form → Finalize Form → Invoice Sent → Paid).
     Param: $step (['label', 'color', 'icon', 'detail'?] dari attachRenewalStep, atau null). --}}
@if(!empty($step))
    <div style="margin-top:4px;">
        <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:700;color:{{ $step['color'] }};background:#fff;border:1px solid {{ $step['color'] }};">
            <i class="fas {{ $step['icon'] }}" style="font-size:9px;"></i> {{ $step['label'] }}
        </span>
        @if(!empty($step['detail']))
            <span style="font-size:10px;color:#888;">{{ $step['detail'] }}</span>
        @endif
    </div>
@endif
