<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership Approval Confirmation</title>
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
            <p>
                <strong>Member ID:</strong> {{ $member_id }}<br>
                <strong>Registered Email:</strong> {{ $registered_email }}
            </p>
            <p>For security purposes, we do not send passwords via email.</p>

            @if (!empty($set_password_url))
                <p>To activate your account, please set your password using the link below:</p>
                <p>
                    <a href="{{ $set_password_url }}" class="button" target="_blank" rel="noopener noreferrer">
                        Set Your Password
                    </a>
                </p>
                <p>Please note that this password setup link is valid for <strong>48 hours (2 x 24 hours)</strong>.</p>
            @else
                <p>Your account password is already active. You can log in directly using your registered email and existing password.</p>
                @if (!empty($login_url))
                    <p>
                        <a href="{{ $login_url }}" class="button" target="_blank" rel="noopener noreferrer">
                            Go To Login
                        </a>
                    </p>
                @endif
            @endif

            <p>Once your account is activated, we encourage you to:</p>
            <ul>
                <li>Set up and complete your profile, and keep your personal details updated.</li>
                <li>Ensure our emails are delivered to your inbox and avoid the spam folder by adding us to your safe sender list.</li>
                <li>Regularly check our programs and register for those relevant to your interests.</li>
                <li>Stay informed by reading our weekly newsletters and industry insights.</li>
                <li>Share your ideas, feedback, or program suggestions with our Membership Team.</li>
            </ul>

            <p>
                If you have any questions, require assistance, or if your password setup link has expired,
                please reply to this email or contact us via WhatsApp at <strong>+62 811-1937-399</strong>.
            </p>
            <p>We look forward to your participation and engagement.</p>
            <p>
                Best regards,<br>
                <strong>Membership Team</strong><br>
                Djakarta Mining Club
            </p>

            @if (!empty($set_password_url))
                <p style="font-size:12px; color:#888888;">
                    If the button above does not work, copy and paste this link into your browser:<br>
                    {{ $set_password_url }}
                </p>
            @endif
        </div>
        <div class="footer">
            Do not reply to emails to this email address. For inquiries, contact us via WhatsApp at +62 811-1937-399.
        </div>
    </div>
</body>

</html>
