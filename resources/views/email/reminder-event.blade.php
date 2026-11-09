<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f2f4f7;
            font-family: Arial, Helvetica, sans-serif;
        }

        .email-wrap {
            padding: 32px 16px;
        }

        .body {
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
        }

        .header-email {
            background: #ffffff;
        }

        .img-logo {
            display: block;
            width: 100%;
            height: auto;
            border: 0;
        }

        .body-email {
            padding: 32px 40px 36px;
            font-size: 15px;
            line-height: 1.65;
            color: #273142;
        }

        .body-email p {
            margin: 0 0 18px;
        }

        .body-email a {
            color: #b80017;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .details {
            width: 100%;
            border-collapse: collapse;
            background: #f6f7f9;
            border-radius: 8px;
        }

        .details td {
            padding: 10px 4px;
            font-size: 14px;
            line-height: 1.5;
            vertical-align: top;
        }

        .details .detail-label {
            width: 90px;
            padding-left: 16px;
            color: #596579;
            font-weight: bold;
        }

        .details .detail-colon {
            width: 12px;
            color: #596579;
        }

        .details .detail-value {
            padding-right: 16px;
            color: #202a3a;
        }

        .footer-email {
            border-top: 1px solid #e6e9ee;
            padding: 20px 40px;
            font-size: 12px;
            line-height: 1.5;
            color: #687386;
        }

        @media only screen and (max-width: 600px) {
            .email-wrap {
                padding: 12px 8px !important;
            }

            .body-email {
                padding: 24px 20px 28px !important;
            }

            .footer-email {
                padding: 18px 20px !important;
            }

            .details .detail-label {
                width: 78px !important;
                padding-left: 12px !important;
            }

            .details .detail-value {
                padding-right: 12px !important;
            }

            .question-button {
                display: block !important;
                text-align: center !important;
            }
        }
    </style>
</head>

<body style="margin: 0; padding: 0; background-color: #f2f4f7;">
    <div class="email-wrap" style="padding: 32px 16px;">

        @php($isEnergyBriefing = ($event_slug ?? null) === 'indonesian-energy-market-briefing-2026')
        <div class="body"
            style="width: 100%; max-width: 640px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden;">
            <div class="header-email">
                <img src="{{ $isEnergyBriefing ? asset('image/banner_mccloskey_v8.png') : 'https://membership.djakarta-miningclub.com/image/the71banner.png' }}"
                    alt="Event banner" class="img-logo" style="max-width: 100%; height: auto;">
            </div>
            <div class="body-email"
                style="padding: 32px 40px 36px; font-family: Arial, Helvetica, sans-serif; font-size: 15px; line-height: 1.65; color: #273142;">
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
                    {{ $code_payment }} </b>.
            </p>
            <p style="margin: 28px 0 10px; color: #202a3a; font-size: 13px; letter-spacing: 0.6px;"><strong>EVENT
                    DETAILS :</strong></p>
            <table class="details" role="presentation"
                style="width: 100%; border-collapse: collapse; margin-bottom: 24px; background-color: #f6f7f9; border-radius: 8px;">
                <tr>
                    <td class="detail-label"
                        style="width: 90px; padding: 10px 4px 10px 16px; vertical-align: top; font-weight: bold; font-size: 14px; color: #596579;">
                        Date</td>
                    <td class="detail-colon"
                        style="width: 12px; padding: 10px 4px; vertical-align: top; font-size: 14px; color: #596579;">
                        :</td>
                    <td class="detail-value"
                        style="padding: 10px 16px 10px 4px; vertical-align: top; font-size: 14px; line-height: 1.5; color: #202a3a;">
                        {{ date('l', strtotime($start_date)) . ' - ' . date(' j F Y', strtotime($end_date)) }}</td>
                </tr>
                <tr>
                    <td class="detail-label"
                        style="width: 90px; padding: 10px 4px 10px 16px; vertical-align: top; font-weight: bold; font-size: 14px; color: #596579;">
                        Time</td>
                    <td class="detail-colon"
                        style="width: 12px; padding: 10px 4px; vertical-align: top; font-size: 14px; color: #596579;">
                        :</td>
                    <td class="detail-value"
                        style="padding: 10px 16px 10px 4px; vertical-align: top; font-size: 14px; line-height: 1.5; color: #202a3a;">
                        {{ date('h.i A', strtotime($start_time)) . ' - ' . date('h.i A', strtotime($end_time)) }}
                        @unless ($isEnergyBriefing)
                            (Followed by Networking Dinner & Drinks)
                        @endunless
                    </td>
                </tr>
                <tr>
                    <td class="detail-label"
                        style="width: 90px; padding: 10px 4px 10px 16px; vertical-align: top; font-weight: bold; font-size: 14px; color: #596579;">
                        Venue</td>
                    <td class="detail-colon"
                        style="width: 12px; padding: 10px 4px; vertical-align: top; font-size: 14px; color: #596579;">
                        :</td>
                    <td class="detail-value"
                        style="padding: 10px 16px 10px 4px; vertical-align: top; font-size: 14px; line-height: 1.5; color: #202a3a;">
                        The Dharmawangsa
                        Hotel Jakarta (Jl. Brawijaya Raya No. 26, Kebayoran Baru, 12160, Indonesia)</td>
                </tr>
                <tr>
                    <td class="detail-label"
                        style="width: 90px; padding: 10px 4px 10px 16px; vertical-align: top; font-weight: bold; font-size: 14px; color: #596579;">
                        Dress Code</td>
                    <td class="detail-colon"
                        style="width: 12px; padding: 10px 4px; vertical-align: top; font-size: 14px; color: #596579;">
                        :</td>
                    <td class="detail-value"
                        style="padding: 10px 16px 10px 4px; vertical-align: top; font-size: 14px; line-height: 1.5; color: #202a3a;">
                        Business Attire</td>
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

            @if ($isEnergyBriefing)
                <p style="margin-top: 20px; margin-bottom: 5px; line-height: 1.5;"><strong>Have a question for our
                        speakers?</strong><br>Submit your question ahead of the event.</p>
                <p style="margin: 12px 0 20px;"><a
                        href="{{ route('event-questions.create', ['code_payment' => $code_payment]) }}"
                        class="question-button"
                        style="background-color: #c9001a; color: #ffffff; display: inline-block; padding: 14px 24px; border-radius: 8px; text-decoration: none; font-weight: bold;">Submit
                        Your Question</a></p>
            @endif

            <p style="margin-top: 20px; margin-bottom: 5px;"><strong>Collecting Your Badge</strong></p>
            <ul style="margin-top: 0; padding-left: 20px; line-height: 1.5;">
                <li>Please bring your business card to exchange for a badge at the registration table.</li>
                <li>Kindly note that collecting a badge on behalf of someone else is not permitted. All attendees
                    must
                    collect their own badge and present a matching business card to gain entry.</li>
            </ul>
            <p style="margin-top: 20px; line-height: 1.5;"><b>IMPORTANT:</b> Should your plans change and you are
                unable
                to attend, we would appreciate it if you could notify us by replying to this email. This will help
                us
                arrange a substitution and offer your spot to another registrant on the waitlist if needed.</p>
            <p style="margin-top: 20px; line-height: 1.5;">Should you require any assistance, please contact us at
                register@djakarta-miningclub.com or +62
                811-1937-399.</p>
            <p style="margin-top: 20px; line-height: 1.5;">We look forward to meeting you in person. Thank you.</p>
            <p style="margin-top: 25px; line-height: 1.5; margin-bottom: 0;">Yours Sincerely,<br />
                The Djakarta Mining Club Team</p>
        </div>
        <div class="footer-email"
            style="border-top: 1px solid #e6e9ee; padding: 20px 40px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; line-height: 1.5; color: #687386;">
            @if ($isEnergyBriefing)
                This email is sent automatically by our system.
            @else
                Do not reply to emails to this email address. This email is sent automatically by our system.
            @endif
        </div>
    </div>

</div>
</body>

</html>
