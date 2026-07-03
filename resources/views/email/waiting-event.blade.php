<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership Application Under Review</title>
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
    </style>
</head>

<body>
    <div class="card">
        <img src="{{ asset('image/banner71_revisi4.png') }}" alt="Djakarta Mining Club" style="width:100%;display:block;">
        <div class="content">
            <p>Dear {{ $users_name }},</p>
            <p>Thank you for registering for {{ $events_name }} | PwC's 2026 Mine Report: Ambition to Action.
            </p>
            <p>Your registration is currently being processed and awaiting confirmation. We will send you a confirmation
                within <strong>48 hours</strong> of receiving this email.</p>
            <p>If you have any further questions, please contact us via email at
                <strong>register@djakarta-miningclub.com</strong> or WhatsApp at <strong>+62 811-1937-399</strong>.
            </p>
            <p>Yours Sincerely,</p>
            <span>The Djakarta Mining Club Team</span>
        </div>
        <div class="footer">
            Do not reply to emails to this email address. For inquiries, contact us via WhatsApp at +62 811-1937-399.
        </div>
    </div>
</body>

</html>
