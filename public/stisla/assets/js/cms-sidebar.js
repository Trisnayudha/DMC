(function () {
    var input = document.getElementById('cms-menu-search-input');
    var menu = document.getElementById('cms-sidebar-menu');
    var empty = document.getElementById('cms-menu-empty');
    var status = document.getElementById('cms-menu-search-status');
    if (!input || !menu) return;

    var items = Array.prototype.slice.call(menu.querySelectorAll('.cms-menu-item'));
    var headers = Array.prototype.slice.call(menu.querySelectorAll('.menu-header'));

    function filterMenu() {
        var query = input.value.trim().toLocaleLowerCase();
        var count = 0;
        menu.classList.toggle('is-searching', query !== '');

        items.forEach(function (item) {
            var parentMatches = item.dataset.menuLabel.toLocaleLowerCase().indexOf(query) !== -1;
            var children = Array.prototype.slice.call(item.querySelectorAll('.cms-submenu-item'));
            var visibleChildren = 0;
            children.forEach(function (child) {
                var match = !query || parentMatches || child.dataset.menuLabel.toLocaleLowerCase().indexOf(query) !== -1;
                child.hidden = !match;
                if (match) visibleChildren++;
            });
            var visible = !query || parentMatches || visibleChildren > 0;
            item.hidden = !visible;
            if (visible) count += children.length ? visibleChildren : 1;
        });

        headers.forEach(function (header) {
            header.hidden = !items.some(function (item) {
                return item.dataset.menuGroup === header.dataset.menuGroup && !item.hidden;
            });
        });
        empty.hidden = !query || count > 0;
        status.textContent = query ? (count ? count + ' menu ditemukan.' : 'Menu tidak ditemukan.') : '';

        // Stisla uses NiceScroll for the sidebar; update its scroll bounds after filtering.
        if (window.jQuery && window.jQuery.fn.getNiceScroll) {
            var scrollbar = window.jQuery('.main-sidebar').getNiceScroll();
            if (scrollbar && scrollbar.length) scrollbar.resize();
        }
    }

    // A hidden search field should never leave a filtered menu in mini mode.
    new MutationObserver(function () {
        if (document.body.classList.contains('sidebar-mini') && input.value) {
            input.value = '';
            filterMenu();
        }
    }).observe(document.body, { attributes: true, attributeFilter: ['class'] });

    input.addEventListener('input', filterMenu);
    input.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && input.value) {
            input.value = '';
            filterMenu();
        }
    });
})();
