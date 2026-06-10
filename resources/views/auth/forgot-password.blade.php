<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Forgot Password — iSpy World</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        .hex-bg {
            background-color: #1e3a5f;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='56' height='100'%3E%3Cpath d='M28 66L0 50V18L28 2l28 16v32L28 66zm0-6l22-13V23L28 8 6 23v24l22 13z' fill='none' stroke='%23ffffff' stroke-opacity='0.07' stroke-width='1'/%3E%3C/svg%3E");
        }

        input[type="email"], input[type="password"], input[type="text"] {
            width: 100%;
            padding: 0.6rem 0.85rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s;
            background: #fff;
        }
        input[type="email"]:focus, input[type="password"]:focus, input[type="text"]:focus {
            border-color: #1e3a5f;
            box-shadow: 0 0 0 3px rgba(30,58,95,0.1);
        }

        .sign-in-btn {
            width: 100%;
            padding: 0.75rem;
            background-color: #1e3a5f;
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: background-color 0.2s;
            letter-spacing: 0.01em;
        }
        .sign-in-btn:hover { background-color: #162d4a; }

        .form-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 8px 40px rgba(0,0,0,0.13);
            padding: 2.5rem 2.25rem 2rem;
            width: 100%;
            max-width: 420px;
        }

        @media (max-width: 700px) {
            .outer-wrapper { flex-direction: column !important; }
            .left-panel { width: 100% !important; min-height: 220px !important; justify-content: flex-start !important; padding: 2rem !important; }
            .right-panel { width: 100% !important; }
        }
    </style>
</head>
<body>

<div class="outer-wrapper" style="display:flex; min-height:100vh;">

    {{-- LEFT PANEL --}}
    <div class="left-panel hex-bg" style="width:40%; display:flex; flex-direction:column; justify-content:flex-end; padding:3rem 2.5rem;">
        <div>
            <img src="{{ asset('images/fma-logo.png') }}"
                 alt="FMA Logo"
                 style="width:80px; height:80px; object-fit:contain; margin-bottom:1.5rem;" />

            <h1 class="text-white font-bold leading-snug" style="font-size:1.65rem; margin-bottom:0.75rem;">
                iSpy World: Bilingual<br>Literacy Support Platform
            </h1>

            <p style="font-size:0.8rem; color:rgba(255,255,255,0.55); line-height:1.5;">
                Elevating Global Literacy | Future Minds Academy |<br>Mandaluyong City
            </p>
        </div>
    </div>

    {{-- RIGHT PANEL --}}
    <div class="right-panel" style="width:60%; background-color:#f5f0e8; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:2.5rem 2rem;">

        <div class="form-card">

            <h2 class="text-center font-bold text-gray-900" style="font-size:1.75rem; margin-bottom:0.35rem;">
                Forgot password?
            </h2>
            <p class="text-center text-gray-500 text-sm" style="margin-bottom:1.75rem;">
                Enter your email address and we&rsquo;ll send you a link to reset your password.
            </p>

            {{-- Session Status --}}
            @if (session('status'))
                <div style="margin-bottom:1.25rem; padding:0.75rem 1rem; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:0.5rem;">
                    <p class="text-sm" style="color:#15803d;">{{ session('status') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                {{-- Email --}}
                <div style="margin-bottom:1.1rem;">
                    <label for="email" class="block text-sm font-medium text-gray-700" style="margin-bottom:0.35rem;">
                        Email Address
                    </label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="Enter your email"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    @error('email')
                        <p class="text-sm" style="color:#dc2626; margin-top:0.4rem;">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="sign-in-btn" style="margin-top:0.4rem;">Send Reset Link</button>

            </form>

            <p class="text-center" style="font-size:0.85rem; color:#6b7280; margin-top:1.5rem;">
                <a href="{{ route('login') }}"
                   style="color:#1e3a5f; font-weight:500; text-decoration:none;"
                   onmouseover="this.style.textDecoration='underline'"
                   onmouseout="this.style.textDecoration='none'">
                    &larr; Back to Login
                </a>
            </p>

        </div>

        {{-- Footer --}}
        <p class="text-center" style="font-size:0.72rem; color:#9ca3af; margin-top:1.5rem; line-height:1.6;">
            &copy; 2026 &ndash; 2027 iSpy World. &nbsp;Licensed to Future Minds Academy
            | <a href="#" style="color:#9ca3af;">Terms of Service</a>
            | <a href="#" style="color:#9ca3af;">Privacy Policy</a>
        </p>

    </div>
</div>

</body>
</html>
