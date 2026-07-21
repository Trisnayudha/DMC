<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="Event gallery for {{ $event->name }}">
    <meta property="og:title" content="{{ $event->name }} — Event Gallery">
    <meta property="og:type" content="article">
    @if ($bannerUrl)
        <meta property="og:image" content="{{ $bannerUrl }}">
    @endif
    <meta property="og:url" content="{{ url()->current() }}">
    <title>{{ $event->name }} — Event Gallery | Djakarta Mining Club</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flickr-justified-gallery@2.2.0/dist/fjGallery.css">
    <script src="https://cdn.jsdelivr.net/npm/flickr-justified-gallery@2.2.0/dist/fjGallery.min.js" defer></script>

    <style>
        :root {
            --eg-primary: #F10110;
            --eg-neutral-100: #F5F7F9;
            --eg-neutral-800: #232529;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: var(--eg-neutral-100);
            color: var(--eg-neutral-800);
        }

        /* ── Gallery section ──────────────────────────────────────────── */
        .eg-gallery-section {
            max-width: 1440px;
            margin: 0 auto;
            padding: 48px 20px 80px;
        }

        .eg-section-title {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 26px;
        }

        .eg-section-title .eg-line {
            flex: 1;
            height: 1px;
            background: linear-gradient(to right, transparent, rgba(241, 1, 16, .3));
        }

        .eg-section-title .eg-line:last-child {
            background: linear-gradient(to left, transparent, rgba(241, 1, 16, .3));
        }

        .eg-section-title h2 {
            color: var(--eg-primary);
            text-transform: uppercase;
            font-weight: 800;
            font-size: clamp(18px, 3vw, 26px);
            white-space: nowrap;
            margin: 0;
        }

        .eg-empty {
            text-align: center;
            color: #9aa1ab;
            padding: 70px 20px;
            font-size: 15px;
        }

        .eg-more-note {
            text-align: center;
            color: #232529;
            font-size: 20px;
            font-weight: 600;
            font-style: italic;
            margin: 32px 0 0;
        }

        .eg-gallery-item {
            display: block;
            border-radius: 14px;
            overflow: hidden;
            cursor: zoom-in;
            background: #e3e5ea;
        }

        .eg-gallery-item img {
            transition: transform .35s ease;
        }

        .eg-gallery-item:hover img {
            transform: scale(1.045);
        }

        /* ── Lightbox ─────────────────────────────────────────────────── */
        .eg-lightbox {
            position: fixed;
            inset: 0;
            background: rgba(10, 10, 12, .92);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 999;
            padding: 24px;
        }

        .eg-lightbox.active {
            display: flex;
        }

        .eg-lightbox img {
            max-width: 92vw;
            max-height: 88vh;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, .5);
        }

        .eg-lightbox-close,
        .eg-lightbox-prev,
        .eg-lightbox-next {
            position: absolute;
            background: rgba(255, 255, 255, .12);
            border: none;
            color: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background .2s;
        }

        .eg-lightbox-close:hover,
        .eg-lightbox-prev:hover,
        .eg-lightbox-next:hover {
            background: rgba(255, 255, 255, .25);
        }

        .eg-lightbox-close {
            top: 20px;
            right: 20px;
            width: 44px;
            height: 44px;
            font-size: 22px;
        }

        .eg-lightbox-prev,
        .eg-lightbox-next {
            top: 50%;
            transform: translateY(-50%);
            width: 48px;
            height: 48px;
            font-size: 26px;
        }

        .eg-lightbox-prev {
            left: 20px;
        }

        .eg-lightbox-next {
            right: 20px;
        }

        .eg-lightbox-counter {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: rgba(255, 255, 255, .75);
            font-size: 13px;
        }

        /* ── Footer ───────────────────────────────────────────────────── */
        .eg-footer {
            text-align: center;
            padding: 32px 20px;
            color: #9aa1ab;
            font-size: 13px;
        }
    </style>
</head>

<body>
    @include('event_gallery._gallery')

    <footer class="eg-footer">
        <p>&copy; {{ date('Y') }} Djakarta Mining Club</p>
    </footer>
</body>

</html>
