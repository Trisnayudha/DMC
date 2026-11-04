<tr>
    <td style="padding-left:16px;white-space:nowrap">
        <div class="d-flex align-items-center" style="gap:8px">
            <div style="width:28px;height:28px;border-radius:50%;
                background:@if($contact['role'] === 'pic') #4e73df @elseif($contact['role'] === 'billing') #f6c23e @else #858796 @endif;
                color:#fff;font-size:11px;font-weight:600;
                display:flex;align-items:center;justify-content:center;flex-shrink:0">
                {{ strtoupper(substr($contact['name'] ?? '?', 0, 1)) }}
            </div>
            <span class="font-weight-bold">{{ trim(($contact['salutation'] ?? '') . ' ' . ($contact['name'] ?? '-')) }}</span>
        </div>
    </td>
    <td class="align-middle" style="white-space:nowrap">
        @php
            $rowPicIds = \App\Services\Sponsors\SponsorContactRowBuilder::sourceIds($contact, 'pic');
            $rowCcIds = \App\Services\Sponsors\SponsorContactRowBuilder::sourceIds($contact, 'cc');
            $rowBillingIds = \App\Services\Sponsors\SponsorContactRowBuilder::sourceIds($contact, 'billing');
        @endphp
        @if($rowPicIds)<span class="badge badge-primary">Primary Contact (PIC)</span>@endif
        @if($rowCcIds)<span class="badge badge-info">CC</span>@endif
        @if($rowBillingIds)<span class="badge badge-warning">Billing</span>@endif
        @if(\App\Services\Sponsors\SponsorContactRowBuilder::sourceIds($contact, 'representative'))<span class="badge badge-light border">Representative</span>@endif
        @if(\App\Services\Sponsors\SponsorContactRowBuilder::sourceIds($contact, 'member'))<span class="badge badge-light border">Member</span>@endif
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
    <td class="align-middle text-right" style="padding-right:16px">
        {{-- Edit sumber kontak yang tergabung dalam baris ini. --}}
        @php
            $picIds    = \App\Services\Sponsors\SponsorContactRowBuilder::sourceIds($contact, 'pic');
            $ccIds = \App\Services\Sponsors\SponsorContactRowBuilder::sourceIds($contact, 'cc');
            $billingIds = \App\Services\Sponsors\SponsorContactRowBuilder::sourceIds($contact, 'billing');
            $repIds    = \App\Services\Sponsors\SponsorContactRowBuilder::sourceIds($contact, 'representative');
            $memberIds = \App\Services\Sponsors\SponsorContactRowBuilder::sourceIds($contact, 'member');
        @endphp
        @if(empty($contact['email']) && (!$picIds || !$ccIds || !$billingIds))
            <span title="Add an email to this contact before assigning roles"><button type="button" class="btn btn-sm btn-outline-secondary" disabled><i class="fas fa-plus mr-1"></i>Add Role</button></span>
        @elseif(!empty($contact['email']) && (!$picIds || !$ccIds || !$billingIds))
            <button type="button" class="btn btn-sm btn-outline-success btn-add-role" title="Add PIC, CC or Billing role"
                data-sponsor-id="{{ $sponsor->id }}" data-sponsor-name="{{ $sponsor->branding_name ?: $sponsor->name }}"
                data-name="{{ $contact['name'] }}" data-email="{{ $contact['email'] }}"
                data-existing-roles='@json(array_values(array_filter([ $picIds ? "pic" : null, $ccIds ? "cc" : null, $billingIds ? "billing" : null ])))'>
                <i class="fas fa-plus mr-1"></i>Add Role
            </button>
        @endif
        @if($picIds || $ccIds || $billingIds || $repIds || $memberIds)
            <button type="button" class="btn btn-sm btn-outline-primary btn-edit-contact" title="Edit kontak"
                data-sponsor-id="{{ $sponsor->id }}"
                data-sponsor-name="{{ $sponsor->branding_name ?: $sponsor->name }}"
                data-pic-ids='@json($picIds)'
                data-cc-ids='@json($ccIds)'
                data-billing-ids='@json($billingIds)'
                data-rep-ids='@json($repIds)'
                data-member-ids='@json($memberIds)'
                data-has-billing="{{ \App\Services\Sponsors\SponsorContactRowBuilder::sourceIds($contact, 'billing') ? 1 : 0 }}"
                data-salutation="{{ $contact['salutation'] ?? '' }}"
                data-name="{{ $contact['name'] }}"
                data-title="{{ $contact['title'] }}"
                data-email="{{ $contact['email'] }}"
                data-phone="{{ $contact['phone'] }}"
                data-instagram="{{ $contact['instagram'] }}"
                data-linkedin="{{ $contact['linkedin'] }}">
                <i class="fas fa-pen"></i>
            </button>
        @endif
        @foreach(['pic' => $picIds, 'cc' => $ccIds, 'billing' => $billingIds] as $sourceRole => $sourceIds)
            @foreach($sourceIds as $sourceId)
                <form method="POST" action="{{ route('sponsors.contact-directory.destroy') }}" class="d-inline remove-contact-form" data-role="{{ strtoupper($sourceRole) }}" data-name="{{ $contact['name'] }}">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="sponsor_id" value="{{ $sponsor->id }}">
                    <input type="hidden" name="role" value="{{ $sourceRole }}">
                    <input type="hidden" name="contact_id" value="{{ $sourceId }}">
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove {{ strtoupper($sourceRole) }} contact"><i class="fas fa-trash-alt mr-1"></i>Remove {{ strtoupper($sourceRole) }}</button>
                </form>
            @endforeach
        @endforeach
    </td>
</tr>
