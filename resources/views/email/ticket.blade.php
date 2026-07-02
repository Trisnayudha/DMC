<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body {
            font-family: sans-serif;
            background: linear-gradient(72.03deg, #FBFBFB 0%, #F6F7F7 99.18%);
            margin: 3% 25%;
        }

        .body {
            width: 100%;
            background: #FFF;
            border-radius: 8px;
        }

        .header-email {
            padding: 15px 30px 12px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            text-align: center;
        }

        .header-email img {
            width: 100%;
            display: block;
            border-radius: 4px;
        }

        .header-email h1 {
            font-size: 15px;
            color: #222;
            margin: 10px 0 3px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .header-email h2 {
            font-size: 12px;
            color: #555;
            margin: 0;
            font-weight: 600;
            text-transform: uppercase;
        }

        .body-email {
            padding: 10px 30px 15px;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #222;
            margin: 14px 0 4px;
            text-transform: uppercase;
        }

        .detail-table {
            border-collapse: collapse;
            border: none;
            margin: 2px 0;
        }

        .detail-table td {
            font-size: 12px;
            color: #333;
            padding: 2px 0;
            border: none;
            vertical-align: top;
        }

        .detail-table .label {
            font-weight: bold;
            color: #222;
            white-space: nowrap;
            width: 70px;
            padding-right: 0;
        }

        .detail-table .colon {
            font-weight: bold;
            color: #222;
            width: 1%;
            padding: 2px 4px;
        }

        .qr-section {
            margin: 6px 0;
            text-align: left;
        }

        .qr-section img {
            width: 120px;
            height: 120px;
        }

        .qr-section p {
            font-size: 11px;
            color: #555;
            margin: 4px 0 0;
            font-weight: 600;
        }

        .info-list {
            margin: 4px 0;
            padding-left: 18px;
            font-size: 12px;
            color: #333;
        }

        .info-list li {
            margin: 1px 0;
        }

        .footer-email {
            border-top: 1px solid #E5E5E5;
            padding: 10px 30px;
            font-size: 10px;
            color: #888;
            text-align: center;
        }

        .divider {
            border: none;
            border-top: 1px solid #E5E5E5;
            margin: 8px 0;
        }

        .body-email p {
            margin: 3px 0;
        }

        @media only screen and (max-device-width: 601px) {
            body {
                margin: 3% 5%;
            }
        }
    </style>
</head>

<body>

    <div class="body">
        <div class="header-email">
            <img src="{{ asset('image/banner71_revisi3.png') }}" alt="DMC Logo">
            <h1>{{ $events_name }} | PwC's 2026 Mine Report: Ambition to Action</h1>
            <h2>Official Event E-Ticket / Entry Pass</h2>
        </div>

        <div class="body-email">
            <table class="detail-table">
                <tr>
                    <td class="section-title" colspan="3" style="padding-top: 10px;">Attendee Details</td>
                </tr>
                <tr>
                    <td class="label">Name</td>
                    <td class="colon">:</td>
                    <td>{{ $users_name }}</td>
                </tr>
                <tr>
                    <td class="label">Position</td>
                    <td class="colon">:</td>
                    <td>{{ $job_title }}</td>
                </tr>
                <tr>
                    <td class="label">Company</td>
                    <td class="colon">:</td>
                    <td>{{ $company_name }}</td>
                </tr>
                <tr>
                    <td class="label">Email</td>
                    <td class="colon">:</td>
                    <td>{{ $users_email }}</td>
                </tr>
                <tr>
                    <td class="label">Mobile</td>
                    <td class="colon">:</td>
                    <td>{{ $phone }}</td>
                </tr>
                <tr>
                    <td class="section-title" colspan="3" style="padding-top: 14px;">Event Details</td>
                </tr>
                <tr>
                    <td class="label">Event</td>
                    <td class="colon">:</td>
                    <td>{{ $events_name }} | PwC's 2026 Mine Report: Ambition to Action</td>
                </tr>
                <tr>
                    <td class="label">Date</td>
                    <td class="colon">:</td>
                    <td>{{ date('l', strtotime($start_date)) . ' - ' . date('j F Y', strtotime($end_date)) }}</td>
                </tr>
                <tr>
                    <td class="label">Time</td>
                    <td class="colon">:</td>
                    <td>{{ date('h.i a', strtotime($start_time)) . ' - ' . date('h.i a', strtotime($end_time)) }}</td>
                </tr>
            </table>

            <div class="section-title">QR Code</div>
            <div class="qr-section">
                <img src="https://quickchart.io/qr?text={{ $code_payment }}&size=300" alt="QR Code">
                <p>Scan this QR code for event check-in and badge collection</p>
            </div>

            <div class="section-title">Important Information</div>
            <ul class="info-list">
                <li>Please present this e-ticket (printed or digital) upon arrival.</li>
                <li>A valid business card is required for registration verification.</li>
                <li>This ticket is valid for one (1) attendee only.</li>
                <li>Entry will be granted upon successful QR code scanning.</li>
            </ul>

            <p style="margin-top: 10px;">Should you require any assistance, please contact us at
                <b>register@djakarta-miningclub.com</b> or <b style="white-space: nowrap;">+62 811-1937-399</b>.
            </p>

            <p style="margin-top: 50px;">Yours Sincerely,<br>
                <b>The Djakarta Mining Club Team</b>
            </p>
        </div>

    </div>

</body>

</html>
