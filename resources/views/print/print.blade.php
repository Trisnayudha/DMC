<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        /* Styles for the screen */
        body {
            margin: 0;
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
        }
    </style>

</head>

<body>
    <div class="container">
        <div class="center">
            {{ $name }} <br>
            {{ $company }}
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
