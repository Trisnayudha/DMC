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
            width: 100%;
            height: auto;
            display: block;

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
            padding: .75rem;
            text-align: left;
        }
    </style>
</head>

<body>

    @php($isEnergyBriefing = ($event_slug ?? null) === 'indonesian-energy-market-briefing-2026')
    <div class="body">
        <div class="header-email">
            <img src="{{ $isEnergyBriefing ? asset('image/banner_mccloskey_v8.png') : 'https://membership.djakarta-miningclub.com/image/the71banner.png' }}"
                alt="Event banner" class="img-logo" style="max-width: 100%; height: auto;">
        </div>
        <div class="body-email">
            <p>Dear {{ $users_name }},</p>
            <p>You are registered to attend {{ $events_name }}@unless ($isEnergyBriefing)
                , taking place tomorrow
            @endunless. Your registration
            number is
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
        <p style="margin-top: 20px; margin-bottom: 5px;"><strong>EVENT DETAILS :</strong></p>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <tr>
                <td
                    style="width: 80px; padding: 4px 0; vertical-align: top; font-weight: bold; font-size: 13px; color: #333;">
                    Date</td>
                <td style="width: 15px; padding: 4px 0; vertical-align: top; font-size: 13px; color: #333;">:</td>
                <td style="padding: 4px 0; vertical-align: top; font-size: 13px; color: #333;">
                    {{ date('l', strtotime($start_date)) . ' - ' . date(' j F Y', strtotime($end_date)) }}</td>
            </tr>
            <tr>
                <td
                    style="width: 80px; padding: 4px 0; vertical-align: top; font-weight: bold; font-size: 13px; color: #333;">
                    Time</td>
                <td style="width: 15px; padding: 4px 0; vertical-align: top; font-size: 13px; color: #333;">:</td>
                <td style="padding: 4px 0; vertical-align: top; font-size: 13px; color: #333;">
                    {{ date('h.i A', strtotime($start_time)) . ' - ' . date('h.i A', strtotime($end_time)) }}
                    @unless ($isEnergyBriefing)
                        (Followed by Networking Dinner & Drinks)
                    @endunless
                </td>
            </tr>
            <tr>
                <td
                    style="width: 80px; padding: 4px 0; vertical-align: top; font-weight: bold; font-size: 13px; color: #333;">
                    Venue</td>
                <td style="width: 15px; padding: 4px 0; vertical-align: top; font-size: 13px; color: #333;">:</td>
                <td style="padding: 4px 0; vertical-align: top; font-size: 13px; color: #333;">The Dharmawangsa
                    Hotel Jakarta (Jl. Brawijaya Raya No. 26, Kebayoran Baru, 12160, Indonesia)</td>
            </tr>
        </table>

        <p style="margin-top: 20px; line-height: 1.5;">You may access the event details and agenda here:
            @if ($isEnergyBriefing)
                <a
                    href="https://www.djakarta-miningclub.com/events/{{ $event_slug }}">https://www.djakarta-miningclub.com/events/{{ $event_slug }}</a>
            @else
                (https://membership.djakarta-miningclub.com/events/2026/2/ambition-to-action)
            @endif
        </p>

        <p style="margin-top: 20px; margin-bottom: 5px;"><strong>Collecting Your Badge</strong></p>
        <ul style="margin-top: 0; padding-left: 20px; line-height: 1.5;">
            <li>Please bring your business card to exchange for a badge at the registration table.</li>
            <li>Kindly note that collecting a badge on behalf of someone else is not permitted. All attendees must
                collect their own badge and present a matching business card to gain entry.</li>
        </ul>
        <p style="margin-top: 20px; line-height: 1.5;"><b>IMPORTANT:</b> If you are unable to attend, please contact
            our team for a substitution. If no
            replacement is provided, we may offer your spot to the next registrant on the waitlist.</p>
        <p style="margin-top: 20px; line-height: 1.5;">Should you require any assistance, please contact us at
            register@djakarta-miningclub.com or +62
            811-1937-399.</p>
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
