<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your DMC Admin login code</title>
</head>
<body style="margin:0; padding:0; background-color:#f1f4f6; font-family:Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" bgcolor="#f1f4f6">
        <tr>
            <td align="center" style="padding:28px 12px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" bgcolor="#ffffff"
                    style="max-width:460px; border:1px solid #dce3e8; border-radius:12px;">
                    <tr>
                        <td bgcolor="#0b4e78" style="padding:18px 24px; border-radius:11px 11px 0 0; color:#ffffff; font-size:17px; font-weight:bold;">
                            DMC Admin
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:26px 24px; color:#163247;">
                            <h1 style="margin:0 0 8px; font-size:24px; line-height:30px;">Verify your sign-in</h1>
                            <p style="margin:0 0 20px; font-size:16px; line-height:24px;">Enter this code on the CMS login page.</p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" bgcolor="#fff2cc" style="padding:18px 8px; border:1px solid #e9bd4a; border-radius:8px;">
                                        <strong style="font-family:'Courier New', Courier, monospace; font-size:36px; line-height:44px; letter-spacing:4px; color:#123650;">{{ $code }}</strong>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:16px 0 0; font-size:14px; line-height:21px; color:#425968;">This code expires in <strong>{{ $minutes }} minutes</strong>.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
