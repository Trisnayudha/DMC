<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership Application Update</title>
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
            <p>Thank you for your interest in becoming a member of Djakarta Mining Club.</p>
            <p>After careful review, we regret to inform you that we are unable to approve your membership application at this time, as our membership is primarily focused on companies and professionals within the mining and related industries.</p>
            <p>We sincerely appreciate your interest and the time you took to complete the registration process. While we are unable to proceed at this stage, we hope to have the opportunity to connect with you in the future.</p>
            <p>Should you have any questions or wish to request a review of your application, please feel free to reply to this email or contact us via WhatsApp at <strong>+62 811-1937-399</strong>.</p>
            <p>Thank you once again for your interest in the Djakarta Mining Club.</p>
            <p>
                Best regards,<br>
                <strong>Membership Team</strong><br>
                Djakarta Mining Club
            </p>
        </div>
        <div class="footer">
            Do not reply to emails to this email address. For inquiries, contact us at +62 811-1937-399.
        </div>
    </div>
</body>
</html>
