<tr>
    <td style="padding-left:16px;white-space:nowrap">
        <div class="d-flex align-items-center" style="gap:8px">
            <div style="width:28px;height:28px;border-radius:50%;
                background:@if($contact['role'] === 'pic') #4e73df @elseif($contact['role'] === 'billing') #f6c23e @else #858796 @endif;
                color:#fff;font-size:11px;font-weight:600;
                display:flex;align-items:center;justify-content:center;flex-shrink:0">
                {{ strtoupper(substr($contact['name'] ?? '?', 0, 1)) }}
            </div>
            <span class="font-weight-bold">{{ $contact['name'] ?? '-' }}</span>
        </div>
    </td>
    <td class="align-middle" style="white-space:nowrap">
        @if($contact['role'] === 'pic')
            <span class="badge badge-primary">Primary Contact (PIC)</span>
        @elseif($contact['role'] === 'billing')
            <span class="badge badge-warning">Billing</span>
        @else
            <span class="badge badge-light border">Representative Profile</span>
        @endif
    </td>
    <td class="align-middle text-muted" style="font-size:12px">{{ $contact['title'] ?: '-' }}</td>
    <td class="align-middle">
        @if($contact['email'])
            <a href="mailto:{{ $contact['email'] }}" class="text-primary" style="font-size:12px">
                <i class="fas fa-envelope"></i> {{ $contact['email'] }}
            </a>
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
    <td class="align-middle" style="white-space:nowrap">
        @if($contact['phone'])
            <a href="tel:{{ $contact['phone'] }}" class="text-success" style="font-size:12px">
                <i class="fas fa-phone"></i> {{ $contact['phone'] }}
            </a>
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
    <td class="align-middle" style="white-space:nowrap">
        @if($contact['instagram'])
            <a href="https://instagram.com/{{ ltrim($contact['instagram'], '@') }}" target="_blank" class="text-danger mr-2" style="font-size:12px">
                <i class="fab fa-instagram"></i> {{ $contact['instagram'] }}
            </a>
        @endif
        @if($contact['linkedin'])
            <a href="{{ $contact['linkedin'] }}" target="_blank" class="text-info" style="font-size:12px">
                <i class="fab fa-linkedin"></i> LinkedIn
            </a>
        @endif
        @if(!$contact['instagram'] && !$contact['linkedin'])
            <span class="text-muted">-</span>
        @endif
    </td>
</tr>
