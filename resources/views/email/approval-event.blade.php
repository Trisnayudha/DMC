<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body {
            font-family: sans-serif;
            /*background: #F6F7F7;*/
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
            /*width: 50px;*/
            /*height: 50px;*/
            border-radius: 10px;
            background: #F6F7F7;
            margin-right: 10px;
            /*display: flex;*/
            /*align-items: center;*/
            /*justify-content: center;*/
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
            <img src="https://api.djakarta-miningclub.com/image/logo-dmc-snp.png" alt="Image" class="img-logo">
        </div>
        <div class="body-email">
            <p>Dear {{ $users_name }},</p>
            <p>Your registration for {{ $events_name }} has been approved. Your registration number is
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
                        EVENT DETAILS :
                    </th>
                </tr>
                {{-- <tr>
                    <th>Topic</th>
                    <th>:</th>
                    <td>{{ $events_name }}</td>
                </tr> --}}
                <tr>
                    <th>Date</th>
                    <th>:</th>
                    <td>{{ date('l', strtotime($start_date)) . ' - ' . date(' j F Y', strtotime($end_date)) }}</td>
                </tr>
                <tr>
                    <th>Time</th>
                    <th>:</th>
                    <td>
                        {{ date('h.i a', strtotime($start_time)) . ' - ' . date('h.i a', strtotime($end_time)) }}
                        {{-- 08.00 – 14.00 WITA & 17.00 – 21.00 WITA --}}
                    </td>
                </tr>
                <tr>
                    <th>Venue</th>
                    <th>:</th>
                    <td>The Dharmawangsa Hotel Jakarta (Jl. Brawijaya Raya No. 26,
                        Kebayoran Baru, 12160, Indonesia)</td>
                    {{-- <td>Le Grande Ballroom 10th Floor, Grand Jatra Hotel Balikpapan</td> --}}
                </tr>
            </table>

            <p>View the latest of the event program by clicking here
                (https://www.djakarta-miningclub.com/events/analyzing-credit-risks-climate-trends-and-market-insights-in-metals
                )
            </p>

            <br>
            <p>
                <b>
                    Collecting Your Badge
                </b>
            </p>
            <p>
            <ul>
                <li>Bring your business card to change with a badge at our registration table.</li>
                <li>Please note that picking up a badge for someone else is not permitted, all attendees will need to
                    collect their own badge and show a matching business card to gain entry.
                </li>
                {{-- <li>If you are unable to attend, please contact our team for substitution of attendee information at the
                    latest by 1 day before the event.</li> --}}

            </ul>
            </p>
            <p><b>IMPORTANT:</b> If you are unable to attend, please contact our team for substitution or if no
                replacement, we can offer your space to the next registrant on the waitlist.</p>
            <p>Should you require any assistance, please contact us at secretariat@djakarta-miningclub.com or +62 811
                1937 300.</p>
            <p>
                We look forward to meeting you in person. Thank you</p>
            <br>
            <p>Your Sincerely,</p>
            <span>The Djakarta Mining Club Team</span>
        </div>
        <div class="footer-email">
            Do not reply to emails to this email address. This email is sent automatically by our system.
        </div>
    </div>

</body>

</html>
