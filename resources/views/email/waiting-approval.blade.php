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
        <div class="content">
            <p>Dear {{ $users_name }},</p>
            <p>Thank you for your membership registration.</p>
            <p>We have received your application and it is currently under review. The verification process may take up
                to <strong>48 hours</strong>. Once completed, we will notify you of the outcome via email.
            </p>
            <p>If you have any questions, please reply to this email or contact us via WhatsApp at <strong>+62
                    811-1937-399</strong>.
            </p>
            <p>
                Best regards,<br>
                <strong>Membership Team</strong><br>
                <strong>Djakarta Mining Club</strong>
            </p>
        </div>
        <div class="footer">
            Do not reply to emails to this email address. For inquiries, contact us via WhatsApp at +62 811-1937-399.
        </div>
    </div>
</body>

</html>
