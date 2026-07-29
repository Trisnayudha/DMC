{{-- Row cell: Password status + Edit/Log/Follow-up buttons. Expects $post, $followUpMap. --}}
@php $openFollowUp = $followUpMap[$post->user_id] ?? null; @endphp
<div class="btn-icon-group">
    <i class="fas {{ $post->password ? 'fa-lock text-success' : 'fa-lock-open text-danger' }}"
        title="Password {{ $post->password ? 'has been set' : 'not set yet' }}"
        data-toggle="tooltip"></i>
    <button type="button"
        class="btn btn-icon btn-outline-primary btn-edit-user"
        data-user-id="{{ $post->user_id }}"
        data-name="{{ $post->name }}"
        data-email="{{ $post->email }}"
        data-job-title="{{ $post->job_title }}"
        data-phone="{{ $post->fullphone ?? $post->phone }}"
        data-prefix="{{ $post->prefix }}"
        data-company-name="{{ $post->company_name }}"
        data-company-website="{{ $post->company_website }}"
        data-company-category="{{ $post->company_category }}"
        data-company-other="{{ $post->company_other }}"
        data-address="{{ $post->address }}"
        data-city="{{ $post->city }}"
        data-portal-code="{{ $post->portal_code }}"
        data-country="{{ $post->country }}"
        data-prefix-office-number="{{ $post->prefix_office_number }}"
        data-office-number="{{ $post->office_number }}"
        data-full-office-number="{{ $post->full_office_number }}"
        data-status-member="{{ $post->status_member }}"
        data-tier="{{ strtolower((string) ($post->tier ?? 'reguler')) }}"
        data-update-url="{{ route('users.update', $post->user_id) }}"
        title="Edit data user" data-toggle="tooltip">
        <i class="fas fa-edit"></i>
    </button>
    <button type="button"
        class="btn btn-icon btn-outline-secondary btn-view-logs"
        data-user-id="{{ $post->user_id }}"
        data-name="{{ $post->name }}"
        data-logs-url="{{ route('users.logs', $post->user_id) }}"
        title="Lihat riwayat perubahan" data-toggle="tooltip">
        <i class="fas fa-history"></i>
    </button>
    <button type="button"
        class="btn btn-icon {{ $openFollowUp ? 'btn-warning' : 'btn-outline-warning' }} btn-open-follow-up-modal"
        data-user-id="{{ $post->user_id }}"
        data-member-name="{{ $post->name }}"
        data-current-company="{{ $post->company_name }}"
        data-new-company="{{ $openFollowUp->new_company_name ?? '' }}"
        data-notes="{{ $openFollowUp->notes ?? '' }}"
        title="{{ $openFollowUp ? 'Follow-up pending — klik untuk edit' : 'Tandai member ini pindah company' }}"
        data-toggle="tooltip">
        <i class="fas fa-exchange-alt"></i>
    </button>
</div>
