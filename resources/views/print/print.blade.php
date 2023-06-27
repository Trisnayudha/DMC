<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/izitoast/dist/css/iziToast.min.css">
    <style>
        /* Styles for the screen */
        @font-face {
            font-family: 'Now Regular';
            font-style: normal;
            font-weight: normal;
            src: url('font/Now-Regular.woff') format('truetype');
        }

        body {
            margin: 0;
            font-family: 'now-bold', sans-serif;
            font-weight: normal;
            /* font-size: 42px */
        }

        @page {
            margin: 0;
            size: auto;
        }

        @media print {

            header,
            footer,
            nav,
            aside,
            time,
            address,
            .toast-container {
                display: none !important;
            }
        }

        .container {
            width: 100%;
            display: flex;
            justify-content: center;
            flex-direction: column;
            align-content: center;
            height: 100vh;
        }

        .center {
            text-align: center;
            text-transform: uppercase;
        }
    </style>

</head>

<body>
    <div class="container">
        <div class="center">
            <span style="font-size: 19px" contenteditable="true"> {{ $name }} </span> <br>
            <span style="font-size: 17px" contenteditable="true"> {{ $company }} </span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/izitoast/dist/js/iziToast.min.js"></script>
    <script>
        window.focus();

        iziToast.show({
            title: '{{ $package }}'.toUpperCase(),
            position: 'topRight',
            backgroundColor: '#28a745',
            onClosed: function() {
                setTimeout(function() {
                    window.print();
                }, 10);
            }
        });
    </script>
</body>

</html>
