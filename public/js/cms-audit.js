(() => {
    'use strict';

    const { endpoint, page, csrf } = document.currentScript.dataset;
    const identifier = /^[a-z][a-z0-9_-]{0,79}$/i;
    const sensitive = /password|passwd|secret|token|credential|authorization|api_?key|signature|cvv|card_?number/i;
    const verbs = /\b(add|create|tambah|edit|ubah|delete|hapus|remove|save|simpan|submit|update|cancel|close|filter|clear|reset|search|export|download|import|upload|print|preview|view|detail|copy|share|approve|reject|send|open|next|previous)\b/i;

    function report(action, control) {
        fetch(endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            keepalive: true,
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ page, action, control })
        }).catch(() => {}); // A failed background request must not interrupt an admin's action.
    }

    document.addEventListener('click', event => {
        if (!event.isTrusted || !(event.target instanceof Element)) return;
        const button = event.target.closest('button, a, [role="button"], [onclick], [data-toggle], [data-bs-toggle], input[type="submit"], input[type="button"]');
        if (!button || button.disabled) return;

        // A regular internal link produces its own server-side page-access event.
        const href = button.getAttribute('href');
        const internalLink = button.tagName === 'A' && href && !href.startsWith('#')
            && button.origin === location.origin && /^https?:$/.test(button.protocol);

        const target = button.getAttribute('data-bs-target') || button.getAttribute('data-target') || '';
        const names = [target.replace(/^#/, ''), button.id];
        // Button text or titles may contain a person's name. Use only a known action word.
        const label = [button.title, button.getAttribute('aria-label'), button.textContent, button.className].join(' ');
        const verb = label.slice(0, 300).match(verbs);
        const toggle = button.getAttribute('data-bs-toggle') || button.getAttribute('data-toggle');
        const control = names.find(name => name && identifier.test(name)) || (verb && verb[1].toLowerCase())
            || (toggle && identifier.test(toggle) && toggle)
            || (button.tagName === 'A' ? 'link' : 'button');
        if (internalLink) {
            // Delegated CMS handlers can open a modal and cancel normal navigation.
            setTimeout(() => { if (event.defaultPrevented) report('click', control); }, 0);
        } else {
            report('click', control);
        }
    }, true);

    function reportField(field) {
        if (field.matches('[type="password"], [type="hidden"], [autocomplete^="cc-"]')) return;
        const name = (field.getAttribute('name') || field.id || 'field').split('[')[0];
        if (/^[a-z][a-z0-9_]{0,63}$/i.test(name) && !sensitive.test(name)) report('field', name);
    }

    document.addEventListener('change', event => {
        if (event.isTrusted && event.target.matches('input, select, textarea')) reportField(event.target);
    }, true);
    // Select2 commits selections via jQuery without a native change event.
    if (window.jQuery) {
        window.jQuery(document).on('select2:select select2:unselect', 'select', event => reportField(event.target));
    }

    const edited = new WeakSet();
    document.addEventListener('input', event => {
        if (event.isTrusted && event.target.isContentEditable) edited.add(event.target);
    }, true);
    document.addEventListener('focusout', event => {
        if (edited.delete(event.target)) reportField(event.target);
    }, true);
})();
