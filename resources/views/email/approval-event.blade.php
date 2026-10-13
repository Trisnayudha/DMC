<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Approved</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f6f7f7;
            margin: 0;
            padding: 24px;
            color: #333333;
        }

        .card {
            max-width: 640px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            border: 1px solid #e5e5e5;
            overflow: hidden;
        }

        .content {
            padding: 24px;
            font-size: 14px;
            line-height: 1.7;
        }

        .footer {
            border-top: 1px solid #e5e5e5;
            padding: 16px 24px;
            font-size: 12px;
            color: #666666;
        }

        .reg-code {
            background-color: #F1B22C;
            color: #000000;
            padding: 2px 10px;
            display: inline-block;
            border-radius: 16px;
        }

        .table {
            border-collapse: collapse;
            width: 100%;
            margin: 8px 0 16px;
            color: #555555;
        }

        .table td,
        .table th {
            font-size: 14px;
            padding: 6px 0;
            text-align: left;
            vertical-align: top;
        }
    </style>
</head>

<body>
    <div class="card">
        <img src="{{ asset('image/banner_mccloskey_v7.png') }}" alt="Djakarta Mining Club"
            style="width:100%;height:auto;display:block;">
        <div class="content">
            <p>Dear {{ $users_name }},</p>
            <p>Your registration for {{ $events_name }} has been approved.
                Your registration number is <b class="reg-code">{{ $code_payment }}</b>
            </p>
            <table class="table">
                <tr>
                    <th colspan="3">
                        EVENT DETAILS :
                    </th>
                </tr>
                {{-- <tr>
                    <th>Topic</th>
                    <th>:</th>
                    <td>{{ $events_name }}</td>
                </tr> --}}
                <tr>
                    <th style="width: 80px;">Date</th>
                    <th style="width: 16px;">:</th>
                    <td>
                        {{ date('l', strtotime($start_date)) . ' - ' . date(' j F Y', strtotime($end_date)) }} </td>
                </tr>
                <tr>
                    <th style="width: 80px;">Time</th>
                    <th style="width: 16px;">:</th>
                    <td>
                        {{ date('h.i a', strtotime($start_time)) . ' - ' . date('h.i a', strtotime($end_time)) }}
                        (Followed
                        by Networking Dinner & Drinks)
                        {{-- 08.00 – 14.00 WITA & 17.00 – 21.00 WITA --}}
                    </td>
                </tr>
                <tr>
                    <th style="width: 80px;">Venue</th>
                    <th style="width: 16px;">:</th>
                    <td>The Dharmawangsa Hotel Jakarta (Jl. Brawijaya Raya No. 26,
                        Kebayoran Baru, 12160, Indonesia)</td>
                    {{-- <td>Le Grande Ballroom 10th Floor, Grand Jatra Hotel Balikpapan</td> --}}
                </tr>
            </table>

            <p>You can view the latest event program by clicking here:
                (https://membership.djakarta-miningclub.com/events/2026/4/indonesia-energy-market-briefing-2026)</p>

            <p><b>Collecting Your Badge</b></p>
            <ul>
                <li>Please bring your business card to exchange for a badge at the registration table.</li>
                <li>Kindly note that collecting a badge on behalf of someone else is not permitted. All attendees must
                    collect their own badge and present a matching business card to gain entry.
                </li>
                {{-- <li>If you are unable to attend, please contact our team for substitution of attendee information at the
                    latest by 1 day before the event.</li> --}}
            </ul>
            <p><b>IMPORTANT:</b> If you are unable to attend, please contact our team for a substitution. If no
                replacement is provided, we may offer your spot to the next registrant on the waitlist.</p>
            <p>Should you require any assistance, please contact us at register@djakarta-miningclub.com or +62
                811-1937-399.</p>
            <p>We look forward to meeting you in person. Thank you.</p>
            <p>Yours Sincerely,</p>
            <span>The Djakarta Mining Club Team</span>
        </div>
        <div class="footer">
            Do not reply to emails to this email address. This email is sent automatically by our system.
        </div>
    </div>
</body>

</html>
