<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:title" content="{{ $news->title }}">
    <meta property="og:description" content="{{ $news->desc }}">
    <meta property="og:image" content="{{ url($news->image) }}">
    <meta property="og:url" content="https://djakarta-miningclub.com/news/{{ $news->slug }}">
    <meta property="og:type" content="article">
    <title>Djakarta Mining Club</title>
</head>

<body>
    {{ $news->title }}
    {{ $news->desc }}
    {{ $news->image }}
    {{ $news->slug }}
    <script>
        // Redirect ke halaman yang ditentukan
        // window.location.href = "https://djakarta-miningclub.com/news/{{ $news->slug }}";
    </script>
</body>

</html>
