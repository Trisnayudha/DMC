<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link href="https://www.dafontfree.net/embed/bm93LWJvbGQmZGF0YS81MC9uLzMxNDMxL05vdy1Cb2xkLm90Zg" rel="stylesheet"
        type="text/css" />
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
            address {
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
            <span style="font-size: 19px"> {{ $name }} </span> <br>
            <span style="font-size: 17px"> {{ $company }} </span>
        </div>
    </div>
</body>

<script>
    window.focus();
    window.print();
    window.onafterprint = function() {
        window.close();
    };
</script>

</html>
