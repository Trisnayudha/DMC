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

        .table {
            border-collapse: collapse;
            width: 100%;
            max-width: 100%;
            margin-bottom: 1rem;
            background-color: transparent;
            color: #555;
        }

        .table td,
        .table th {
            font-size: 13px;
            border-top-width: 0;
            border-bottom: 1px solid;
            border-color: transparent !important;
            padding: .75rem !important;
            text-align: left;
        }
    </style>
</head>

<body>

    <div class="body">
        <div class="header-email">
            <img src="https://api.djakarta-miningclub.com/image/dmc.png" alt="Gambar" class="img-logo">
        </div>
        <div class="body-email">
            <p>Dear {{ $users_name }},</p>
            <p>Pendaftaran Anda untuk {{ $events_name }} telah disetujui. Nomor registrasi Anda adalah sebagai berikut
                <b
                    style="background-color: #F1B22C;
                border: none;
                color: black;
                padding: 5px 10px;
                text-align: center;
                text-decoration: none;
                display: inline-block;
                margin: 4px 2px;
                border-radius: 16px;">
                    {{ $code_payment }} </b>
            </p>
            <table class="table">
                <tr>
                    <th colspan="3">
                        RINCIAN ACARA :
                    </th>
                </tr>
                <tr>
                    <th>Tanggal</th>
                    <th>:</th>
                    <td>Selasa / {{ date('j F Y', strtotime($end_date)) }}</td>
                </tr>
                <tr>
                    <th>Waktu</th>
                    <th>:</th>
                    <td> {{ date('h.i ', strtotime($start_time)) }} WIB - Selesai</td>
                </tr>
                <tr>
                    <th>Tempat</th>
                    <th>:</th>
                    <td>The Dharmawangsa Hotel Jakarta</td>
                </tr>
            </table>

            <p>Berikut link untuk agenda acara
                (https://djakarta-miningclub.com/dmc/2023/metals-magnified-analyzing-key-markets-and-indonesias-prospects/)
            </p>

            <br>
            <p>
                <b>
                    Penukaran ID Acara
                </b>
            </p>
            <p>
            <ul>
                <li>Mohon untuk dapat membawa kartu nama dan menunjukan email ini di area registrasi. </li>
                <li>Pengambilan ID Acara tidak bisa diwakilkan. </li>
                <li>Jika Anda berhalangan hadir, mohon memberikan konfirmasi sebelum acara. Sehingga tempat dapat kami
                    berikan kepada peserta yang masih ada di daftar tunggu. </li>
            </ul>
            </p>

            <p>Untuk bantuan lebih lanjut, silahkan menghubungi kami melalui email secretariat@djakarta-miningclub.com
                atau Whatsapp +62 811 1937 300.
            </p>
            <p>Terima kasih.</p>
            <br>
            <p>Hormat kami,</p>
            <span>Sekretariat Djakarta Mining Club</span>
        </div>
        <div class="footer-email">
            Jangan balas email ke alamat email ini. Email ini dikirim secara otomatis oleh sistem kami.
        </div>
    </div>

</body>

</html>
