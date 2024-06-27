<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="{{ $news->desc }}">
    <meta name="keyword" content="{{ $news->title }}">
    <meta name="context" content="https://djakarta-miningclub.com/news/{{ $news->slug }}">
    <meta name="path" content="{{ base64_encode(url('/')) }}">
    <meta name="asset" content="{{ base64_encode(asset('/')) }}">

    <meta property="og:type" content="article" />
    <meta property="og:locale" content="en_GB" />
    <meta property="og:locale:alternate" content="fr_FR" />
    <meta property="og:locale:alternate" content="es_ES" />
    <meta property="og:title" content="{{ $news->title }}">
    <meta property="og:description" content="{{ $news->desc }}">
    <meta property="og:image" content="{{ url($news->image) }}">
    <meta property="og:url" content="https://djakarta-miningclub.com/news/{{ $news->slug }}">
    <meta property="og:type" content="article">
    <meta http-equiv='cache-control' content='no-cache'>
    <meta http-equiv='expires' content='0'>
    <meta http-equiv='pragma' content='no-cache'>
    <title>DMC News: {{ $news->title }}</title>
</head>

<body>
    <h1>{{ $news->title }}</h1>
    <img src="{{ url($news->image) }}" alt="">
    <p>{!! $news->desc !!}</p>
    <p>https://djakarta-miningclub.com/news/{{ $news->slug }}</p>
    {{-- <script>
        // Redirect ke halaman yang ditentukan
        window.location.href = "https://djakarta-miningclub.com/news/{{ $news->slug }}";
    </script> --}}
</body>

</html>
