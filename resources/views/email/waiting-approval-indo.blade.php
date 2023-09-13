<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body {
            font-family: sans-serif;
            background: linear-gradient(72.03deg, #FBFBFB 0%, #F6F7F7 99.18%);
            margin: 5% 25%;
        }

        .body {
            width: 100%;
            background: #FFF;
            border-radius: 8px;
        }

        .header-email {
            padding: 60px;
            padding-bottom: 20px;
            border-bottom-right-radius: 33%;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .body-email {
            padding: 20px 40px;
            font-size: 13px;
            color: #333;
        }

        .footer-email {
            border-top: 1px solid #E5E5E5;
            padding: 20px;
            font-size: 11px;
            color: #333;
            font-weight: bold;
        }

        .flex-code {
            display: flex;
            margin: 4% auto;
        }

        .flex-code .code {
            border-radius: 10px;
            background: #F6F7F7;
            margin-right: 10px;
            font-weight: bold;
            padding: 20px 25px;
        }

        .link-confirm {
            background: #F6F7F7;
            margin: 4% auto;
            padding: 15px 15px;
            border-radius: 7px;
            border: 1px dotted #E5E5E5;
        }

        .btn-link-confirm {
            text-decoration: none;
            background: #F1B22C;
            color: #FFF;
            padding: 17px 16px;
            text-align: center;
            display: block;
            margin: 5% auto;
            width: 20%;
            border-radius: 25px;
            font-weight: bold;
            box-shadow: 2px 1px 3px #ddd;
        }

        .img-logo {
            height: 70px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        @media only screen and (max-device-width: 601px) {
            body {
                margin: 5% 5%;
            }

            .btn-link-confirm {
                width: 40%;
            }
        }
    </style>
</head>

<body>

    <div class="body">
        <div class="header-email">
            <img src="https://api.djakarta-miningclub.com/image/dmc.png" alt="Gambar" class="img-logo">
        </div>
        <div class="body-email">
            <p>Kepada {{ $users_name }},</p>
            <p>Terima kasih atas registrasi Anda untuk acara {{ $events_name }}.</p>
            <p>Saat ini registrasi Anda sedang diproses dan menunggu konfirmasi. Kami akan mengirimkan konfirmasi maksimal 72 jam setelah Anda mendapatkan email ini. </p>
            <p>Jika ada pertanyaan lebih lanjut, silahkan menghubungi kami melalui email secretariat@djakarta-miningclub.com atau Whatsapp +62 811 1937 300. </p>
            <p>Hormat kami,</p>
            <span>Sekretariat Djakarta Mining Club
            </span>
        </div>
        <div class="footer-email">
            Jangan balas email ke alamat email ini. Email ini dikirim secara otomatis oleh sistem kami.
        </div>
    </div>

</body>

</html>
