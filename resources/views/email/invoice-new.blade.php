<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>A simple, clean, and responsive HTML invoice template</title>

    <!-- Favicon -->
    <link rel="icon" href="./images/favicon.png" type="image/x-icon" />

    <!-- Invoice styling -->
    <style>
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            text-align: center;
            color: #777;
        }

        body h1 {
            font-weight: 300;
            margin-bottom: 0px;
            padding-bottom: 0px;
            color: #000;
        }

        body h3 {
            font-weight: 300;
            margin-top: 10px;
            margin-bottom: 20px;
            font-style: italic;
            color: #555;
        }

        body a {
            color: #06f;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            line-height: 24px;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
            border-collapse: collapse;
        }

        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }

        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }

        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }

        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }

        @media only screen and (max-width: 600px) {
            .invoice-box table tr.top table td {
                width: 100%;
                display: block;
                text-align: center;
            }

            .invoice-box table tr.information table td {
                width: 100%;
                display: block;
                text-align: center;
            }
        }

        .capsule {
            background-color: yellow;
            border: none;
            color: black;
            padding: 5px 10px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            margin: 4px 2px;
            border-radius: 16px;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <table>
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="title">
                                <img src="{{ asset('image/dmc.png') }}" alt="Company logo"
                                    style="width: 100%; max-width: 250px" />
                            </td>

                            <td align="right">
                                Invoice no: {{ $code_payment }}<br />
                                Created: {{ $create_date }}<br />
                                Due date: {{ $due_date }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class=" information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                {{ $users_name }}.<br />
                                {{ $users_email }}<br />
                                +{{ $phone }}

                            </td>

                            <td align="right">
                                {{ $company_name }}<br />
                                {{ $address_company }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="heading">
                <td>Payment Method</td>

                <td></td>
            </tr>

            <tr class="details">
                <td>Credit Card</td>

                <td></td>
            </tr>
            <tr class="details">
                <td>Status Payment</td>

                <td align="right">
                    <span class="capsule">
                        {{ $status }}
                    </span>
                </td>
            </tr>

            <tr class="heading">
                <td>Item</td>

                <td align="right">Price</td>
            </tr>

            <tr class="item">
                <td>{{ $events_name }}</td>

                <td align="right">IDR {{ $event_price }}</td>
            </tr>

            <tr class="item last">
                <td>Voucher</td>

                <td align="right">IDR {{ $voucher_price }}</td>
            </tr>

            <tr class="total">
                <td align="right"></td>

                <td align="right">Total: IDR {{ $total_price }}</td>
            </tr>
        </table>
        @if ($link != null)
            <a href="{{ $link }}">Please click here to process your payment.</a>
        @endif
        <br>
        <br>
        Thank you for registering for one of the Indonesia Miner events.<br />
        <br />
        <p>Regards,</p>
        <span>Indonesia Miner</span><br />
        <div style="text-align: center;font-size: 15px">
            CIBIS NINE - 11th Floor TB. Simatupang No. 2 Jakarta,<br />
            Indonesia 12560
        </div>

        <div style="font-family:Helvetica, sans-serif;font-size:10px;line-height:25px;text-align:center;color:#7187A5;">
            <p>Do not reply to this email address. This email is sent automatically by our system. If you need further
                assistance, please contact info@indonesiaminer.com. </p>
        </div>
    </div>
</body>

</html>
