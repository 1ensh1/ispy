<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your iSpy World Teacher Account</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f8; font-family:Arial, Helvetica, sans-serif; font-size:15px; color:#333333;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08);">

                    {{-- Header --}}
                    <tr>
                        <td style="background-color:#1e3a5f; padding:28px 40px; text-align:center;">
                            <span style="font-size:22px; font-weight:bold; color:#ffffff; letter-spacing:0.5px;">iSpy World</span>
                            <span style="display:block; font-size:13px; color:#a8c4e0; margin-top:4px;">Future Minds Academy</span>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:36px 40px;">

                            <p style="margin:0 0 20px 0; font-size:16px;">Hi <strong>{{ $name }}</strong>,</p>

                            <p style="margin:0 0 16px 0; line-height:1.6;">
                                Welcome to the iSpy World portal! A teacher account has been created for you at
                                <strong>Future Minds Academy</strong>. You can use the credentials below to log in
                                once your account is activated.
                            </p>

                            {{-- Login details --}}
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0; background-color:#f0f4f8; border-radius:6px; border:1px solid #d0dce8;">
                                <tr>
                                    <td style="padding:20px 24px;">
                                        <p style="margin:0 0 12px 0; font-size:13px; text-transform:uppercase; letter-spacing:0.6px; color:#5a7a99;">Login Email</p>
                                        <p style="margin:0 0 20px 0; font-size:15px; color:#1e3a5f; font-weight:bold;">{{ $email }}</p>

                                        <p style="margin:0 0 12px 0; font-size:13px; text-transform:uppercase; letter-spacing:0.6px; color:#5a7a99;">Temporary Password</p>
                                        <p style="margin:0; font-size:18px; font-family:'Courier New', Courier, monospace; background-color:#ffffff; border:1px solid #b0c8e0; border-radius:4px; padding:10px 16px; letter-spacing:1.5px; color:#1e3a5f;">{{ $temporary_password }}</p>
                                    </td>
                                </tr>
                            </table>

                            {{-- Activation warning --}}
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px 0; background-color:#fff8e1; border-left:4px solid #f59e0b; border-radius:4px;">
                                <tr>
                                    <td style="padding:16px 20px;">
                                        <p style="margin:0 0 8px 0; font-size:14px; font-weight:bold; color:#92400e;">Account Activation Required</p>
                                        <p style="margin:0; font-size:14px; line-height:1.6; color:#78350f;">
                                            Your account is currently <strong>inactive</strong>. You must click the activation link below
                                            before you can log in. The link will expire, so please activate soon.
                                        </p>
                                        <p style="margin:12px 0 0 0; font-size:14px; color:#78350f;">
                                            [Activation link will appear here]
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 16px 0; font-size:14px; line-height:1.6; color:#555555;">
                                For security, please change your password immediately after your first login.
                            </p>

                            <p style="margin:0; font-size:14px; line-height:1.6; color:#555555;">
                                If you did not expect this email or believe it was sent in error, please ignore it or
                                contact your school administrator.
                            </p>

                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color:#f0f4f8; padding:20px 40px; text-align:center; border-top:1px solid #d8e4f0;">
                            <p style="margin:0; font-size:13px; color:#6b7280;">Future Minds Academy &mdash; iSpy World</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
