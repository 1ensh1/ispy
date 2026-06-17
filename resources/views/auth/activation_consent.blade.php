<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activate Your Account — iSpy World</title>
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
            background-color: #eef2f7;
            color: #1e3a5f;
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
        .consent-box h3 {
            font-size: 15px;
            color: #1e3a5f;
            margin: 16px 0 8px;
        }
        .consent-box h3:first-child { margin-top: 0; }
        .consent-box p {
            font-size: 13px;
            line-height: 1.6;
            color: #4b5563;
            margin-bottom: 10px;
        }
        .consent-box ul {
            margin: 0 0 10px 20px;
            padding: 0;
        }
        .consent-box li {
            font-size: 13px;
            line-height: 1.6;
            color: #4b5563;
            margin-bottom: 4px;
        }
        .agree-row {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            text-align: left;
            margin-bottom: 20px;
        }
        .agree-row input { margin-top: 3px; }
        .agree-row label {
            font-size: 14px;
            line-height: 1.5;
            color: #4b5563;
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
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn:hover { background-color: #162d4a; }
        .btn:disabled {
            background-color: #9ca3af;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">iSpy World</div>
        <span class="brand-sub">Future Minds Academy</span>

        <div class="icon">&#9776;</div>

        <h1>Before You Activate Your Account</h1>
        <p>Hi {{ $name }}, please review the Data Privacy Notice and Terms &amp; Conditions below before activating your {{ $roleLabel }} account.</p>

        <div class="consent-box" style="max-height:280px; overflow-y:auto; border:1px solid #e5e7eb; border-radius:8px; padding:16px; font-size:13px; line-height:1.6; text-align:left; margin:16px 0;">
            @include('partials.consent_notice', ['roleLabel' => $roleLabel])
        </div>

        <form method="POST" action="{{ $postUrl }}">
            @csrf

            <div class="agree-row">
                <input type="checkbox" id="agree" name="agree" required>
                <label for="agree">I have read and agree to the Data Privacy Notice and Terms &amp; Conditions (Version {{ $termsVersion }}).</label>
            </div>

            <button type="submit" id="activateBtn" class="btn" disabled>Activate My Account</button>
        </form>
    </div>

    <script>
        (function () {
            var agree = document.getElementById('agree');
            var btn = document.getElementById('activateBtn');
            agree.addEventListener('change', function () {
                btn.disabled = !agree.checked;
            });
        })();
    </script>
</body>
</html>
