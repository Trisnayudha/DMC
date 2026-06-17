{{-- Floating Quick Search Panel --}}
<style>
    #qs-toggle {
        position: fixed;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        z-index: 1050;
        background: #6777ef;
        color: #fff;
        border: none;
        border-radius: 8px 0 0 8px;
        padding: 14px 10px;
        writing-mode: vertical-rl;
        text-orientation: mixed;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1px;
        cursor: pointer;
        box-shadow: -2px 0 8px rgba(0,0,0,0.15);
        transition: background 0.2s;
    }
    #qs-toggle:hover { background: #4e5ed3; }

    #qs-panel {
        position: fixed;
        right: -440px;
        top: 0;
        height: 100vh;
        width: 420px;
        background: #fff;
        box-shadow: -4px 0 24px rgba(0,0,0,0.13);
        z-index: 1049;
        display: flex;
        flex-direction: column;
        transition: right 0.3s cubic-bezier(.4,0,.2,1);
        border-left: 3px solid #6777ef;
    }
    #qs-panel.open { right: 0; }
    #qs-toggle.hidden { display: none; }

    #qs-panel .qs-header {
        background: #6777ef;
        color: #fff;
        padding: 16px 18px;
        font-weight: 700;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }
    #qs-panel .qs-close { cursor: pointer; font-size: 20px; opacity: .85; }
    #qs-panel .qs-close:hover { opacity: 1; }

    #qs-panel .qs-search-wrap {
        padding: 14px 16px;
        border-bottom: 1px solid #f0f0f0;
        flex-shrink: 0;
    }
    #qs-panel .qs-search-wrap input {
        width: 100%;
        border: 1.5px solid #e0e0e0;
        border-radius: 8px;
        padding: 9px 14px;
        font-size: 13px;
        outline: none;
        transition: border-color 0.2s;
        box-sizing: border-box;
    }
    #qs-panel .qs-search-wrap input:focus { border-color: #6777ef; }

    #qs-body { flex: 1; overflow-y: auto; padding: 10px 16px; }

    .qs-empty { text-align: center; color: #aaa; margin-top: 40px; font-size: 13px; }

    .qs-card {
        border: 1px solid #eee;
        border-radius: 10px;
        margin-bottom: 14px;
        overflow: hidden;
    }
    .qs-card-head {
        background: #fafbfc;
        padding: 12px 14px;
        border-bottom: 1px solid #f0f0f0;
    }
    .qs-card-head .qs-name { font-weight: 700; font-size: 13px; color: #222; }
    .qs-card-head .qs-meta { font-size: 11px; color: #777; margin-top: 1px; }

    .qs-fields { padding: 10px 14px; }
    .qs-field { display: flex; font-size: 11.5px; padding: 3px 0; }
    .qs-field-label { width: 90px; color: #999; flex-shrink: 0; }
    .qs-field-value { color: #333; flex: 1; word-break: break-word; }

    .qs-badge-status { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: 600; text-transform: capitalize; }
    .qs-st-active      { background: #d4edda; color: #155724; }
    .qs-st-pending     { background: #fff3cd; color: #856404; }
    .qs-st-declined    { background: #f8d7da; color: #721c24; }
    .qs-st-deactivated { background: #e2e3e5; color: #383d41; }

    .qs-events-toggle {
        width: 100%;
        border: none;
        background: #f5f6fa;
        padding: 8px 14px;
        font-size: 12px;
        font-weight: 600;
        color: #555;
        cursor: pointer;
        text-align: left;
        border-top: 1px solid #f0f0f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .qs-events-toggle:hover { background: #eef0f8; }
    .qs-events-toggle .arrow { transition: transform 0.2s; }
    .qs-events-toggle.open .arrow { transform: rotate(180deg); }

    .qs-events-list { display: none; padding: 6px 14px 10px; }
    .qs-events-list.open { display: block; }

    .qs-event-item {
        padding: 6px 0;
        border-bottom: 1px dashed #f0f0f0;
        font-size: 11.5px;
    }
    .qs-event-item:last-child { border-bottom: none; }
    .qs-event-name { font-weight: 600; color: #333; }
    .qs-event-detail { color: #888; font-size: 10.5px; margin-top: 2px; }

    .qs-status { display: inline-block; padding: 1px 7px; border-radius: 10px; font-size: 9px; font-weight: 600; text-transform: uppercase; }
    .qs-status-paid    { background: #d4edda; color: #155724; }
    .qs-status-waiting { background: #fff3cd; color: #856404; }
    .qs-status-free    { background: #cce5ff; color: #004085; }
    .qs-status-other   { background: #e2e3e5; color: #383d41; }

    #qs-spinner { display: none; text-align: center; padding: 30px 0; color: #aaa; font-size: 13px; }
</style>

<button id="qs-toggle" title="Quick Search">&#128269; Quick Search</button>

<div id="qs-panel">
    <div class="qs-header">
        <span>&#128269; Quick Search</span>
        <span class="qs-close" id="qs-close">&times;</span>
    </div>
    <div class="qs-search-wrap">
        <input type="text" id="qs-input" placeholder="Search by name, email, or company..." autocomplete="off">
        <select id="qs-category" style="width:100%;margin-top:8px;border:1.5px solid #e0e0e0;border-radius:8px;padding:7px 10px;font-size:12px;outline:none;">
            <option value="">All Categories</option>
            @foreach(\App\Models\Company\CompanyCategory::where('is_active', true)->orderBy('sort_order')->get() as $cat)
                <option value="{{ $cat->name }}">{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
    <div id="qs-body">
        <div class="qs-empty" id="qs-hint">Type at least 2 characters to search.</div>
        <div id="qs-spinner">Searching...</div>
        <div id="qs-results"></div>
    </div>
</div>

<script>
(function() {
    var toggle  = document.getElementById('qs-toggle');
    var panel   = document.getElementById('qs-panel');
    var closeBtn= document.getElementById('qs-close');
    var input   = document.getElementById('qs-input');
    var results = document.getElementById('qs-results');
    var spinner = document.getElementById('qs-spinner');
    var hint    = document.getElementById('qs-hint');
    var catSel  = document.getElementById('qs-category');
    var timer   = null;
    var currentOffset = 0;
    var pageSize = 10;

    toggle.addEventListener('click', function() {
        panel.classList.add('open');
        toggle.classList.add('hidden');
        input.focus();
    });
    closeBtn.addEventListener('click', function() {
        panel.classList.remove('open');
        toggle.classList.remove('hidden');
    });

    function doSearch(append) {
        clearTimeout(timer);
        var q = input.value.trim();
        var cat = catSel.value;
        if (q.length < 2 && !cat) {
            results.innerHTML = '';
            spinner.style.display = 'none';
            hint.style.display = 'block';
            currentOffset = 0;
            removeShowMore();
            return;
        }
        if (!append) {
            currentOffset = 0;
            results.innerHTML = '';
            removeShowMore();
        }
        hint.style.display = 'none';
        spinner.style.display = 'block';

        timer = setTimeout(function() {
            var url = '{{ route("admin.quick_search") }}?q=' + encodeURIComponent(q)
                + '&limit=' + pageSize + '&offset=' + currentOffset;
            if (cat) url += '&category=' + encodeURIComponent(cat);
            var xhr = new XMLHttpRequest();
            xhr.open('GET', url);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.onload = function() {
                spinner.style.display = 'none';
                if (xhr.status === 200) {
                    var data = JSON.parse(xhr.responseText);
                    var items = data.results || [];
                    renderResults(items, append);
                    if (items.length >= pageSize) {
                        currentOffset += pageSize;
                        addShowMore();
                    } else {
                        removeShowMore();
                    }
                } else {
                    results.innerHTML += '<div class="qs-empty">Failed to load results.</div>';
                }
            };
            xhr.onerror = function() {
                spinner.style.display = 'none';
                results.innerHTML += '<div class="qs-empty">Network error.</div>';
            };
            xhr.send();
        }, append ? 0 : 400);
    }

    function addShowMore() {
        removeShowMore();
        var btn = document.createElement('button');
        btn.id = 'qs-show-more';
        btn.style.cssText = 'width:100%;padding:10px;border:1px dashed #ccc;background:#fafbfc;border-radius:8px;cursor:pointer;font-size:12px;font-weight:600;color:#6777ef;margin-top:8px;';
        btn.textContent = 'Show More...';
        btn.addEventListener('click', function() { doSearch(true); });
        results.parentNode.appendChild(btn);
    }

    function removeShowMore() {
        var old = document.getElementById('qs-show-more');
        if (old) old.remove();
    }

    input.addEventListener('input', function() { doSearch(false); });
    catSel.addEventListener('change', function() { doSearch(false); });

    function statusClass(s) {
        s = (s || '').toLowerCase();
        if (s === 'paid off') return 'qs-status-paid';
        if (s === 'waiting')  return 'qs-status-waiting';
        if (s === 'free' || s.indexOf('free') >= 0) return 'qs-status-free';
        return 'qs-status-other';
    }

    function renderResults(data, append) {
        if (!data.length && !append) {
            results.innerHTML = '<div class="qs-empty">No results found.</div>';
            return;
        }

        var html = '';
        for (var i = 0; i < data.length; i++) {
            var u = data[i];
            var st = (u.status_member || 'pending').toLowerCase();
            var stMap = {
                'active':      { cls: 'qs-st-active',      icon: '&#10003;', label: 'Active' },
                'pending':     { cls: 'qs-st-pending',      icon: '&#9679;',  label: 'Pending' },
                'declined':    { cls: 'qs-st-declined',     icon: '&#10007;', label: 'Declined' },
                'deactivated': { cls: 'qs-st-deactivated',  icon: '&#9888;',  label: 'Deactivated' }
            };
            var stInfo = stMap[st] || stMap['pending'];
            var memberBadge = '<span class="qs-badge-status ' + stInfo.cls + '">' + stInfo.icon + ' ' + stInfo.label + '</span>';

            var eventsHtml = '';
            if (u.history && u.history.length) {
                for (var j = 0; j < u.history.length; j++) {
                    var h = u.history[j];
                    eventsHtml += '<div class="qs-event-item">'
                        + '<div class="qs-event-name">' + esc(h.event_name) + '</div>'
                        + '<div class="qs-event-detail">' + esc(h.ticket_title) + ' &bull; ' + esc(h.event_date)
                        + ' <span class="qs-status ' + statusClass(h.status) + '">' + esc(h.status) + '</span>'
                        + '</div></div>';
                }
            } else {
                eventsHtml = '<div style="color:#aaa;font-size:11px;padding:6px 0;">No event history.</div>';
            }

            var evtCount = u.history ? u.history.length : 0;

            var companyVal = esc(u.company_name || '-');
            if (u.company_verified) {
                companyVal += ' <span style="color:#28a745;font-size:11px;" title="Verified Company">&#10003;</span>';
            }

            html += '<div class="qs-card">'
                + '<div class="qs-card-head">'
                + '<div class="qs-name">' + esc(u.name) + '</div>'
                + '<div class="qs-meta">' + esc(u.email) + '</div>'
                + '</div>'
                + '<div class="qs-fields">'
                + field('Phone', u.phone)
                + field('Job Title', u.job_title)
                + fieldRaw('Company', companyVal)
                + fieldRaw('Member', memberBadge)
                + '</div>'
                + '<button class="qs-events-toggle" onclick="this.classList.toggle(\'open\');this.nextElementSibling.classList.toggle(\'open\');">'
                + 'Events Attended (' + evtCount + ') <span class="arrow">&#9660;</span>'
                + '</button>'
                + '<div class="qs-events-list">' + eventsHtml + '</div>'
                + '</div>';
        }
        if (append) {
            results.innerHTML += html;
        } else {
            results.innerHTML = html;
        }
    }

    function field(label, val) {
        return '<div class="qs-field"><div class="qs-field-label">' + label + '</div><div class="qs-field-value">' + esc(val || '-') + '</div></div>';
    }

    function fieldRaw(label, html) {
        return '<div class="qs-field"><div class="qs-field-label">' + label + '</div><div class="qs-field-value">' + html + '</div></div>';
    }

    function esc(s) {
        if (!s) return '';
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(s));
        return d.innerHTML;
    }
})();
</script>
