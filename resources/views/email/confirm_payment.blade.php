<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Invoice Payment</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f6f6f6;
            margin: 0;
            padding: 0;
        }

        .invoice-box {
            max-width: 700px;
            margin: auto;
            background: #ffffff;
            padding: 30px;
            border: 1px solid #eee;
        }

        .header {
            text-align: left;
        }

        .header img {
            max-width: 180px;
        }

        .invoice-info {
            text-align: right;
            font-size: 14px;
            color: #555;
        }

        .section {
            margin-top: 30px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table td {
            padding: 8px;
            font-size: 14px;
        }

        .heading {
            background: #f2f2f2;
            font-weight: bold;
        }

        .total {
            font-weight: bold;
            border-top: 2px solid #eee;
        }

        .capsule {
            background-color: #ffc619;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }

        .va-box {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 6px;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            letter-spacing: 2px;
            margin-top: 10px;
        }

        .cta {
            margin-top: 20px;
            text-align: center;
        }

        .cta a {
            background-color: #015174;
            color: #fff;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }

        .footer {
            margin-top: 40px;
            font-size: 12px;
            text-align: center;
            color: #888;
        }
    </style>
</head>

<body>
    <div class="invoice-box">

        <!-- HEADER -->
        <table width="100%">
            <tr>
                <td class="header">
                    <img src="{{ asset('image/dmc.png') }}" />
                </td>
                <td class="invoice-info">
                    Invoice No: {{ $code_payment }}<br>
                    Created: {{ $create_date }}<br>
                    Due Date: {{ $due_date }}
                </td>
            </tr>
        </table>

        <!-- USER INFO -->
        <div class="section">
            <table class="table">
                <tr>
                    <td>
                        <strong>{{ $users_name }}</strong><br>
                        {{ $users_email }}<br>
                        +{{ $phone }}
                    </td>
                    <td align="right">
                        {{ $company_name }}<br>
                        {{ $company_address }}
                    </td>
                </tr>
            </table>
        </div>

        <!-- PAYMENT INFO -->
        <div class="section">
            <table class="table">
                <tr class="heading">
                    <td>Payment Method</td>
                    <td></td>
                </tr>
                <tr>
                    <td>{{ $payment_method ?: 'Credit Card' }}</td>
                    <td></td>
                </tr>

                <tr class="heading">
                    <td>Status</td>
                    <td align="right"></td>
                </tr>
                <tr>
                    <td>
                        <span class="capsule">{{ $status }}</span>
                    </td>
                    <td></td>
                </tr>
            </table>
        </div>

        <!-- ITEM -->
        <div class="section">
            <table class="table">
                <tr class="heading">
                    <td>Item</td>
                    <td align="right">Price</td>
                </tr>

                <tr>
                    <td>{{ $events_name }}</td>
                    <td align="right">IDR {{ $price }}</td>
                </tr>

                <tr>
                    <td>Voucher</td>
                    <td align="right">IDR {{ $voucher_price }}</td>
                </tr>

                <tr class="total">
                    <td></td>
                    <td align="right">Total: IDR {{ $total_price }}</td>
                </tr>
            </table>
        </div>

        <!-- PAYMENT ACTION -->
        @if ($link != null)
            <div class="cta">
                <a href="{{ $link }}">Proceed to Payment</a>
            </div>
        @endif

        <!-- VIRTUAL ACCOUNT -->
        @if ($fva != null)
            <div class="section">
                <p><strong>Virtual Account Number</strong></p>

                <div class="va-box">
                    {{ $fva }}
                </div>

                <p style="font-size:12px; color:#888; text-align:center;">
                    Tap and hold to copy the number
                </p>

                <p style="text-align:center;">
                    Please complete your payment before the due date.
                </p>
            </div>
        @endif

        <!-- FOOTER -->
        <div class="footer">
            Thank you for registering for Djakarta Mining Club event.<br><br>

            <strong>Djakarta Mining Club</strong><br>
            Gedung 47 Lantai 2 – 201B<br>
            Jalan Letjen TB Simatupang No. 47, Jakarta Selatan<br><br>

            Do not reply to this email.<br>
            For assistance: secretariat@djakarta-miningclub.com<br>
            WhatsApp: +62811 1937 300
        </div>

    </div>
</body>

</html>
