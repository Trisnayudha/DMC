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
            padding: 60px 60px 20px;
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
            height: 200px;
            display: block;
            margin: 0 auto;
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
            <img src="https://membership.djakarta-miningclub.com/image/logo_dmc_mccloskey_new.png" alt="Image"
                class="img-logo">
        </div>
        <div class="body-email">
            <p>Dear {{ $users_name }},</p>

            <p>We regret to inform you that your registration for <b>{{ $events_name }}</b> has been cancelled.
                Your seat will be offered to participants on the waiting list.</p>

            <p>If you believe this is an error or would like further assistance, please contact us at
                <b>{{ $contact_email }}</b> or <b>{{ $contact_phone }}</b>.
            </p>

            <br>
            <p>We apologize for any inconvenience caused. We look forward to welcoming you at our future events.
                Thank you.</p>

            <br>
            <p>Yours Sincerely,</p>
            <span>The Djakarta Mining Club Team</span>
        </div>
        <div class="footer-email">
            Do not reply to emails to this email address. This email is sent automatically by our system.
        </div>
    </div>
</body>

</html>
