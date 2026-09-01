{{-- Edit Contract icon button — opens the same edit modal as the Contract History
     page (admin.sponsor.contract-history._edit-modal), prefilled from this row's
     own sponsor_renewals record ($r). Lets admins fix a record without leaving
     the Annual Report page.

     $isSuccessor (optional): true when $r isn't the row this button visually sits
     next to, but the newer record it was superseded by (renewed/upgraded/stopped) —
     swaps the tooltip so it's clear which contract is actually being edited. --}}
@php $isSuccessor = $isSuccessor ?? false; @endphp
<button class="btn btn-sm btn-outline-secondary action-icon-btn edit-renewal-btn"
    data-id="{{ $r->id }}"
    data-sponsor-id="{{ $r->sponsor_id }}"
    data-sponsor-name="{{ $r->sponsor ? $r->sponsor->name : '—' }}"
    data-status="{{ $r->renewal_status }}"
    data-contract-start="{{ $r->contract_start }}"
    data-contract-end="{{ $r->contract_end }}"
    data-package="{{ $r->package }}"
    data-renewal-type="{{ $r->renewal_type }}"
    data-amount-usd="{{ $r->amount_usd }}"
    data-amount-idr="{{ $r->amount_idr }}"
    data-quotation-number="{{ $r->quotation_number }}"
    data-quotation-date="{{ $r->quotation_date ? $r->quotation_date->format('Y-m-d') : '' }}"
    data-invoice-date="{{ $r->invoice_date ? $r->invoice_date->format('Y-m-d') : '' }}"
    data-invoice-number="{{ $r->invoice_number }}"
    data-paid-date="{{ $r->paid_date ? $r->paid_date->format('Y-m-d') : '' }}"
    data-notes="{{ $r->notes }}"
    data-toggle="tooltip"
    title="{{ $isSuccessor ? 'Edit the renewed contract (new cycle — this expiring one is superseded)' : 'Edit Contract Record' }}">
    <i class="fas fa-pencil-alt"></i>
    @if($isSuccessor)<i class="fas fa-sync-alt" style="font-size:8px;vertical-align:top;margin-left:1px;color:#6c757d;"></i>@endif
</button>
