<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Activated | iSpy World</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #1e3a5f;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25);
            max-width: 460px;
            width: 100%;
            padding: 40px 36px;
            text-align: center;
        }
        .brand {
            font-size: 22px;
            font-weight: bold;
            color: #1e3a5f;
            letter-spacing: 0.5px;
        }
        .brand-sub {
            display: block;
            font-size: 13px;
            color: #6b7280;
            margin-top: 4px;
            margin-bottom: 28px;
        }
        .icon {
            width: 64px;
            height: 64px;
            line-height: 64px;
            border-radius: 50%;
            background-color: #f0fdf4;
            color: #16a34a;
            font-size: 34px;
            font-weight: bold;
            margin: 0 auto 20px;
        }
        h1 {
            font-size: 20px;
            color: #111827;
            margin-bottom: 12px;
        }
        p {
            font-size: 15px;
            line-height: 1.6;
            color: #4b5563;
            margin-bottom: 20px;
        }
        .creds {
            background-color: #f0f4f8;
            border: 1px solid #d0dce8;
            border-radius: 6px;
            padding: 20px 24px;
            text-align: left;
            margin: 0 0 28px;
        }
        .creds-label {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #5a7a99;
            margin: 0 0 4px;
        }
        .creds-email {
            font-size: 15px;
            color: #1e3a5f;
            font-weight: bold;
            margin: 0 0 16px;
        }
        .creds-pass {
            font-size: 18px;
            font-family: 'Courier New', Courier, monospace;
            background-color: #ffffff;
            border: 1px solid #b0c8e0;
            border-radius: 4px;
            padding: 10px 16px;
            letter-spacing: 1.5px;
            color: #1e3a5f;
            margin: 0;
        }
        .btn {
            display: inline-block;
            background-color: #1e3a5f;
            color: #ffffff;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            padding: 12px 28px;
            border-radius: 8px;
            transition: background-color 0.2s;
        }
        .btn:hover { background-color: #162d4a; }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">iSpy World</div>
        <span class="brand-sub">Future Minds Academy</span>

        <div class="icon">&check;</div>

        <h1>Account Activated!</h1>
        <p>Welcome, {{ $name }}! Your {{ $roleLabel }} account is now active.</p>
        <p>Please save your login credentials below — for security, this password will not be shown again.</p>

        <div class="creds">
            <p class="creds-label">Email</p>
            <p class="creds-email">{{ $email }}</p>

            <p class="creds-label">Password</p>
            <p class="creds-pass">{{ $password }}</p>
        </div>

        <a href="{{ route('login') }}" class="btn">Go to Login</a>
    </div>
</body>
</html>
