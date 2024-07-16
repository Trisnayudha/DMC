<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="{{ $event->description }}">
    <meta name="keyword" content="{{ $event->title }}">
    <meta name="context" content="https://djakarta-miningclub.com/event/{{ $event->slug }}">
    <meta name="description" content="DMC event: {{ $event->title }}" />
    <meta property="og:title" content="{{ $event->title }}">
    <meta property="og:image" content="https://membership.djakarta-miningclub.com/{{ $event->image }}">
    <meta property="og:type" content="article" />
    <meta property="og:locale" content="en_GB" />
    <meta property="og:locale:alternate" content="fr_FR" />
    <meta property="og:locale:alternate" content="es_ES" />
    <meta property="og:description" content="{!! $event->description !!}">
    <meta property="og:url" content="https://djakarta-miningclub.com/events/{{ $event->slug }}">
    <meta property="og:type" content="article">
    <meta http-equiv='cache-control' content='no-cache'>
    <meta http-equiv='expires' content='0'>
    <meta http-equiv='pragma' content='no-cache'>
    <title>DMC Event: {{ $event->title }}</title>
    <link rel="canonical" href="https://getbootstrap.com/docs/5.2/examples/checkout/">
</head>

<body>
    <small>
        <i>Redirect to https://djakarta-miningclub.com/events/{{ $event->slug }}</i>
    </small>
    <script>
        // Redirect ke halaman yang ditentukan
        window.location.href = "https://djakarta-miningclub.com/events/{{ $event->slug }}";
    </script>
</body>

</html>
