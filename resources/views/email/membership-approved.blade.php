<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership Approved</title>
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
            line-height: 1.6;
        }

        .button {
            display: inline-block;
            background: #007bff;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 18px;
            border-radius: 6px;
            margin: 8px 0 12px;
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
            <p>We are pleased to inform you that your membership has been successfully approved.</p>
            <p>Below are your account details:</p>
            <p>Member ID: {{ $member_id }}</p>
            <p>Registered Email: {{ $registered_email }}</p>
            <p>For security purposes, we do not send passwords via email.</p>
            <p>To access your account, please set your password using the link below:</p>
            <p>
                <a href="{{ $set_password_url }}" class="button" target="_blank" rel="noopener noreferrer">Set Your Password</a>
            </p>
            <p>Set Your Password: {{ $set_password_url }}</p>
            <p>This link is secure and will expire within {{ $link_expiry_hours }} hours. If it expires, you can request a new link via the login page.</p>
            <p>Once your account is activated, you will be able to:</p>
            <ul>
                <li>Access member-exclusive features</li>
                <li>Stay updated with the latest event information</li>
                <li>Manage your participation and profile</li>
            </ul>
            <p>If you have any questions or require assistance, please do not hesitate to contact us.</p>
            <p>We look forward to your participation and engagement.</p>
            <p>Best regards,<br>Djakarta Mining Club Team</p>
            <p>If the button does not work, open this link in your browser:</p>
            <p>{{ $set_password_url }}</p>
        </div>
        <div class="footer">
            Do not reply to emails to this email address. This email is sent automatically by our system.
        </div>
    </div>
</body>

</html>
