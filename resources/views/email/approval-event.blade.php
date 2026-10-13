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
            padding: 0 40px 20px;
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

        .img-logo {
            width: 100%;
            height: auto;
            display: block;
        }

        @media only screen and (max-device-width: 601px) {
            body {
                margin: 5% 5%;
            }
        }
    </style>
</head>

<body>

    <div class="body">
        <div class="header-email">
            <img src="{{ asset('image/banner_mccloskey_v7.png') }}" alt="Djakarta Mining Club" class="img-logo">
        </div>
        <div class="body-email">
            <p>Dear {{ $users_name }},</p>
            <p>Your registration for {{ $events_name }} has been approved.
                Your registration number is
                <b
                    style="background-color: #F1B22C; color: black; padding: 5px 10px; display: inline-block; margin: 4px 2px; border-radius: 16px;">{{ $code_payment }}</b>
            </p>

            <p style="margin-top: 20px; margin-bottom: 5px;"><strong>EVENT DETAILS :</strong></p>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                {{-- <tr>
                    <td style="width: 80px; padding: 4px 0; vertical-align: top; font-weight: bold; font-size: 13px; color: #333;">Topic</td>
                    <td style="width: 15px; padding: 4px 0; vertical-align: top; font-size: 13px; color: #333;">:</td>
                    <td style="padding: 4px 0; vertical-align: top; font-size: 13px; color: #333;">{{ $events_name }}</td>
                </tr> --}}
                <tr>
                    <td
                        style="width: 80px; padding: 4px 0; vertical-align: top; font-weight: bold; font-size: 13px; color: #333;">
                        Date</td>
                    <td style="width: 15px; padding: 4px 0; vertical-align: top; font-size: 13px; color: #333;">:</td>
                    <td style="padding: 4px 0; vertical-align: top; font-size: 13px; color: #333;">
                        {{ date('l', strtotime($start_date)) . ' - ' . date(' j F Y', strtotime($end_date)) }}
                    </td>
                </tr>
                <tr>
                    <td
                        style="width: 80px; padding: 4px 0; vertical-align: top; font-weight: bold; font-size: 13px; color: #333;">
                        Time</td>
                    <td style="width: 15px; padding: 4px 0; vertical-align: top; font-size: 13px; color: #333;">:</td>
                    <td style="padding: 4px 0; vertical-align: top; font-size: 13px; color: #333;">
                        {{ date('h.i a', strtotime($start_time)) . ' - ' . date('h.i a', strtotime($end_time)) }}
                        (Followed by Networking Dinner & Drinks)
                        {{-- 08.00 – 14.00 WITA & 17.00 – 21.00 WITA --}}
                    </td>
                </tr>
                <tr>
                    <td
                        style="width: 80px; padding: 4px 0; vertical-align: top; font-weight: bold; font-size: 13px; color: #333;">
                        Venue</td>
                    <td style="width: 15px; padding: 4px 0; vertical-align: top; font-size: 13px; color: #333;">:</td>
                    <td style="padding: 4px 0; vertical-align: top; font-size: 13px; color: #333;">The Dharmawangsa
                        Hotel Jakarta (Jl. Brawijaya Raya No. 26, Kebayoran Baru, 12160, Indonesia)</td>
                    {{-- <td>Le Grande Ballroom 10th Floor, Grand Jatra Hotel Balikpapan</td> --}}
                </tr>
            </table>

            <p style="margin-top: 20px; line-height: 1.5;">You can view the latest event program by clicking here:
                (https://membership.djakarta-miningclub.com/events/2026/4/indonesia-energy-market-briefing-2026)</p>

            <p style="margin-top: 20px; margin-bottom: 5px;"><strong>Collecting Your Badge</strong></p>
            <ul style="margin-top: 0; padding-left: 20px; line-height: 1.5;">
                <li>Please bring your business card to exchange for a badge at the registration table.</li>
                <li>Kindly note that collecting a badge on behalf of someone else is not permitted. All attendees must
                    collect their own badge and present a matching business card to gain entry.</li>
                {{-- <li>If you are unable to attend, please contact our team for substitution of attendee information at the
                    latest by 1 day before the event.</li> --}}
            </ul>

            <p style="margin-top: 20px; line-height: 1.5;"><b>IMPORTANT:</b> If you are unable to attend, please
                contact our team for a substitution. If no replacement is provided, we may offer your spot to the next
                registrant on the waitlist.</p>

            <p style="margin-top: 20px; line-height: 1.5;">Should you require any assistance, please contact us at
                register@djakarta-miningclub.com or +62 811-1937-399.</p>

            <p style="margin-top: 20px; line-height: 1.5;">We look forward to meeting you in person. Thank you.</p>

            <p style="margin-top: 25px; line-height: 1.5; margin-bottom: 0;">Yours Sincerely,<br />
                The Djakarta Mining Club Team</p>
        </div>
        <div class="footer-email">
            Do not reply to emails to this email address. This email is sent automatically by our system.
        </div>
    </div>

</body>

</html>
