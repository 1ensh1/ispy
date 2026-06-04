<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activation Link Invalid — iSpy World</title>
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
            background-color: #fef2f2;
            color: #dc2626;
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
            margin-bottom: 28px;
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

        <div class="icon">&times;</div>

        <h1>Activation Link Invalid</h1>
        <p>This activation link is invalid or has already been used.</p>

        <a href="{{ route('login') }}" class="btn">Back to Login</a>
    </div>
</body>
</html>
