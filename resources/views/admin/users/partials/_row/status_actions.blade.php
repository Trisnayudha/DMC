{{-- Row cell: Status action buttons. Expects $post, $isActive, $isDeclined, $isDeactivated. --}}
<div class="btn-icon-group">
    @if ($isDeactivated)
        <button type="button"
            class="btn btn-icon btn-outline-success btn-reactivate-member"
            data-url="{{ route('users.reactivate', $post->user_id) }}"
            data-name="{{ $post->name }}"
            title="Reactivate member ini" data-toggle="tooltip">
            <i class="fas fa-undo"></i>
        </button>
    @elseif ($isActive)
        <button type="button"
            class="btn btn-icon btn-success btn-verify-member"
            data-url="{{ route('users.verify', $post->user_id) }}"
            disabled title="Sudah verified" data-toggle="tooltip">
            <i class="fas fa-check"></i>
        </button>
        <button type="button"
            class="btn btn-icon {{ !empty($post->two_step_verified) ? 'btn-info' : 'btn-outline-info' }} btn-toggle-two-step"
            data-url="{{ route('users.toggle_two_step', $post->user_id) }}"
            data-name="{{ $post->name }}"
            data-verified="{{ !empty($post->two_step_verified) ? '1' : '0' }}"
            title="{{ !empty($post->two_step_verified) ? 'Sudah Verifikasi 2-Langkah oleh ' . ($post->two_step_verified_by ?? 'Staff') . ' — Klik untuk batalkan' : 'Tandai Verifikasi 2-Langkah oleh Staff (LinkedIn/Telfon)' }}"
            data-toggle="tooltip">
            <i class="fas {{ !empty($post->two_step_verified) ? 'fa-check-circle' : 'fa-user-check' }}"></i>
        </button>
        <button type="button"
            class="btn btn-icon btn-outline-secondary btn-deactivate-member"
            data-url="{{ route('users.deactivate', $post->user_id) }}"
            data-name="{{ $post->name }}"
            title="Deactivate member ini" data-toggle="tooltip">
            <i class="fas fa-user-slash"></i>
        </button>
    @elseif ($isDeclined)
        @php
            $declinedCompanyVerified = !empty($post->is_verified) || !empty($post->has_verified_company_name);
            $declinedPayload = [
                'company_name'         => $post->company_name,
                'prefix'               => $post->prefix,
                'company_website'      => $post->company_website,
                'company_category'     => $post->company_category,
                'company_other'        => $post->company_other,
                'address'              => $post->address,
                'city'                 => $post->city,
                'portal_code'          => $post->portal_code,
                'prefix_office_number' => $post->prefix_office_number,
                'office_number'        => $post->office_number,
                'full_office_number'   => $post->full_office_number,
                'country'              => $post->country,
            ];
        @endphp
        <button type="button"
            class="btn btn-icon btn-danger btn-verify-member"
            data-url="{{ route('users.verify', $post->user_id) }}"
            data-company-verified="{{ $declinedCompanyVerified ? '1' : '0' }}"
            data-company-name="{{ $post->company_name }}"
            data-normalized-name="{{ strtolower(trim((string) $post->company_name)) }}"
            data-member-name="{{ $post->name }}"
            data-member-email="{{ $post->email }}"
            data-member-job-title="{{ $post->job_title }}"
            data-member-phone="{{ $post->fullphone ?? $post->phone }}"
            data-payload='@json($declinedPayload)'
            title="Aplikasi ini sudah di-decline — klik untuk re-review" data-toggle="tooltip">
            <i class="fas fa-redo"></i>
        </button>
    @else
        @php
            $companyVerified = !empty($post->is_verified) || !empty($post->has_verified_company_name);
            $companyPayload = [
                'company_name'         => $post->company_name,
                'prefix'               => $post->prefix,
                'company_website'      => $post->company_website,
                'company_category'     => $post->company_category,
                'company_other'        => $post->company_other,
                'address'              => $post->address,
                'city'                 => $post->city,
                'portal_code'          => $post->portal_code,
                'prefix_office_number' => $post->prefix_office_number,
                'office_number'        => $post->office_number,
                'full_office_number'   => $post->full_office_number,
                'country'              => $post->country,
            ];
        @endphp
        <button type="button"
            class="btn btn-icon btn-warning btn-verify-member"
            data-url="{{ route('users.verify', $post->user_id) }}"
            data-company-verified="{{ $companyVerified ? '1' : '0' }}"
            data-company-name="{{ $post->company_name }}"
            data-normalized-name="{{ strtolower(trim((string) $post->company_name)) }}"
            data-member-name="{{ $post->name }}"
            data-member-email="{{ $post->email }}"
            data-member-job-title="{{ $post->job_title }}"
            data-member-phone="{{ $post->fullphone ?? $post->phone }}"
            data-payload='@json($companyPayload)'
            title="{{ $companyVerified ? 'Verifikasi member' : 'Company belum verified — klik untuk selesaikan dulu' }}"
            data-toggle="tooltip">
            @if (!$companyVerified)
                <i class="fas fa-exclamation-triangle"></i>
            @else
                <i class="fas fa-shield-alt"></i>
            @endif
        </button>
    @endif
</div>
