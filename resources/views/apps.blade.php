<!DOCTYPE html>
<html>

<head>
    <title>Download Aplikasi Kami</title>
    <style>
        body {
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

        .app-download {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 20px;
            height: 30vh;
        }

        .app-download a {
            border: none;
            transition: all 0.3s ease;
        }

        .app-download a:hover {
            opacity: 0.8;
        }

        .app-download img {
            height: 200px;
        }

        @media screen and (max-width: 600px) {
            .app-download img {
                height: 200px;
            }

            .image {
                width: 600px
            }
        }

        .image {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto;
            width: 600px;
        }
    </style>
</head>

<body>
    <img src="https://api.djakarta-miningclub.com/image/dmc.png" alt="Image" class="image">

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
