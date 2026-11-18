@if ($auditPage = request()->attributes->get('cms_audit_page'))
    @php request()->attributes->set('cms_audit_rendered', true); @endphp
    <script defer src="{{ asset('js/cms-audit.js') }}"
        data-page="{{ $auditPage }}" data-endpoint="{{ route('cms_activity.interaction', [], false) }}"
        data-csrf="{{ csrf_token() }}"></script>
@endif
