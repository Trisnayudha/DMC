<section class="eg-gallery-section">
    <div class="eg-section-title">
        <span class="eg-line"></span>
        <h2>Event Highlight</h2>
        <span class="eg-line"></span>
    </div>

    @if ($photos->isEmpty())
        <p class="eg-empty">No photos available yet for this event.</p>
    @else
        <div class="fj-gallery">
            @foreach ($photos as $photo)
                <a href="{{ $photo->url }}" class="fj-gallery-item eg-gallery-item" data-index="{{ $loop->index }}">
                    <img
                        src="{{ $photo->url }}"
                        width="{{ $photo->width }}"
                        height="{{ $photo->height }}"
                        alt="{{ $event->name }} photo {{ $loop->iteration }}"
                        loading="lazy"
                    >
                </a>
            @endforeach
        </div>
    @endif
</section>

@if ($photos->isNotEmpty())
    <div class="eg-lightbox" id="egLightbox">
        <button class="eg-lightbox-close" id="egLightboxClose" aria-label="Close">&times;</button>
        <button class="eg-lightbox-prev" id="egLightboxPrev" aria-label="Previous">&#8249;</button>
        <img id="egLightboxImg" src="" alt="{{ $event->name }}">
        <button class="eg-lightbox-next" id="egLightboxNext" aria-label="Next">&#8250;</button>
        <div class="eg-lightbox-counter" id="egLightboxCounter"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var container = document.querySelector('.fj-gallery');

            function rowHeight() {
                var w = window.innerWidth;
                if (w <= 700) return 110;
                if (w <= 900) return 150;
                if (w <= 1200) return 180;
                return 220;
            }

            fjGallery(container, {
                itemSelector: '.eg-gallery-item',
                rowHeight: rowHeight(),
                gutter: 10,
                lastRow: 'left',
            });

            var resizeTimer;
            window.addEventListener('resize', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function () {
                    container.fjGallery.updateOptions({ rowHeight: rowHeight() });
                }, 200);
            });

            // ── Lightbox ─────────────────────────────────────────────────
            var photos = @json($photos->pluck('url')->values());
            var items = container.querySelectorAll('.eg-gallery-item');
            var lightbox = document.getElementById('egLightbox');
            var lbImg = document.getElementById('egLightboxImg');
            var counter = document.getElementById('egLightboxCounter');
            var current = 0;

            function render() {
                lbImg.src = photos[current];
                counter.textContent = (current + 1) + ' / ' + photos.length;
            }
            function open(index) {
                current = index;
                render();
                lightbox.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
            function close() {
                lightbox.classList.remove('active');
                document.body.style.overflow = '';
            }
            function prev() { current = (current - 1 + photos.length) % photos.length; render(); }
            function next() { current = (current + 1) % photos.length; render(); }

            items.forEach(function (item, i) {
                item.addEventListener('click', function (e) {
                    e.preventDefault();
                    open(i);
                });
            });
            document.getElementById('egLightboxClose').addEventListener('click', close);
            document.getElementById('egLightboxPrev').addEventListener('click', prev);
            document.getElementById('egLightboxNext').addEventListener('click', next);
            lightbox.addEventListener('click', function (e) {
                if (e.target === lightbox) close();
            });
            document.addEventListener('keydown', function (e) {
                if (!lightbox.classList.contains('active')) return;
                if (e.key === 'Escape') close();
                if (e.key === 'ArrowLeft') prev();
                if (e.key === 'ArrowRight') next();
            });
        });
    </script>
@endif
