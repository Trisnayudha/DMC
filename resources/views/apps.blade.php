<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Aplikasi Kami</title>
    <style>
        body {
            margin: 0;
            font-family: sans-serif;
            background: linear-gradient(to right, #f7797d, #FBD786, #c2e59c);
            background-size: 200% auto;
            animation: gradient 15s ease infinite;
        }

        @keyframes gradient {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .image {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 20px auto;
            max-width: 100%;
            padding: 0 16px;
        }

        .image img {
            max-width: 100%;
            height: auto;
        }

        .app-download {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            padding: 20px;
        }

        .app-download a img {
            max-width: 250px;
            width: 100%;
            height: auto;
            transition: all 0.3s ease;
        }

        .app-download a:hover {
            opacity: 0.85;
        }

        @media (min-width: 768px) {
            .app-download {
                flex-direction: row;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <div class="image">
        <img src="https://api.djakarta-miningclub.com/image/dmc.png" alt="Image">
    </div>

    <div class="app-download">
        <a href="https://play.google.com/store/apps/details?id=com.djakartaminingclub.android" target="_blank">
            <img src="{{ asset('image/playstore.png') }}" alt="Download di Play Store">
        </a>
        <a href="https://apps.apple.com/id/app/djakarta-mining-club/id1667834762" target="_blank">
            <img src="{{ asset('image/appstore.png') }}" alt="Download di App Store">
        </a>
    </div>
</body>

</html>
