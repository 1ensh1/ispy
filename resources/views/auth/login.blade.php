<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign In — iSpy World</title>
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

        .eye-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0 0.25rem;
            color: #6b7280;
            display: flex;
            align-items: center;
        }
        .eye-btn:hover { color: #374151; }

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
                Welcome back
            </h2>
            <p class="text-center text-gray-500 text-sm" style="margin-bottom:1.75rem;">
                Sign in to access your literacy portal.
            </p>

            <form method="POST" action="{{ route('login') }}">
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
                </div>

                {{-- Password --}}
                <div style="margin-bottom:1.1rem;">
                    <label for="password" class="block text-sm font-medium text-gray-700" style="margin-bottom:0.35rem;">
                        Password
                    </label>
                    <div style="position:relative; display:flex; align-items:center;">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                            style="padding-right:4.5rem;"
                        />
                        <div style="position:absolute; right:0.6rem; display:flex; gap:0.2rem;">
                            <button type="button" class="eye-btn" onclick="togglePassword('show')" id="btn-show" title="Show password">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M1.5 12s3.75-7.5 10.5-7.5S22.5 12 22.5 12s-3.75 7.5-10.5 7.5S1.5 12 1.5 12z"/>
                                    <circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <button type="button" class="eye-btn" onclick="togglePassword('hide')" id="btn-hide" title="Hide password" style="display:none;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.477 10.485A3 3 0 0013.5 13.5m-3.023-3.015A3 3 0 0112 9a3 3 0 012.985 2.716M6.347 6.354C4.26 7.8 2.5 10.2 1.5 12c1.5 3.75 5.25 7.5 10.5 7.5 1.73 0 3.36-.39 4.8-1.08M17.657 17.657C19.74 16.2 21.5 13.8 22.5 12c-1.5-3.75-5.25-7.5-10.5-7.5-.6 0-1.19.05-1.766.143"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Remember Me + Forgot Password --}}
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.4rem;">
                    <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                        <input
                            id="remember_me"
                            type="checkbox"
                            name="remember"
                            class="rounded border-gray-300 text-indigo-600"
                            style="width:16px; height:16px; accent-color:#1e3a5f;"
                        />
                        <span class="text-sm text-gray-600">Remember me</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                           class="text-sm font-medium"
                           style="color:#1e3a5f; text-decoration:none;"
                           onmouseover="this.style.textDecoration='underline'"
                           onmouseout="this.style.textDecoration='none'">
                            Forgot password?
                        </a>
                    @endif
                </div>

                {{-- Validation Errors --}}
                @if ($errors->any())
                    <div style="margin-bottom:1rem; padding:0.75rem 1rem; background:#fef2f2; border:1px solid #fecaca; border-radius:0.5rem;">
                        @foreach ($errors->all() as $error)
                            <p class="text-sm" style="color:#dc2626;">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <button type="submit" class="sign-in-btn">Sign In</button>

            </form>
        </div>

        {{-- Footer --}}
        <p class="text-center" style="font-size:0.72rem; color:#9ca3af; margin-top:1.5rem; line-height:1.6;">
            &copy; 2024 &ndash; 2025 iSpy World. &nbsp;Licensed to Future Minds Academy
            | <a href="#" style="color:#9ca3af;">Terms of Service</a>
            | <a href="#" style="color:#9ca3af;">Privacy Policy</a>
        </p>

    </div>
</div>

<script>
    function togglePassword(action) {
        const input = document.getElementById('password');
        const btnShow = document.getElementById('btn-show');
        const btnHide = document.getElementById('btn-hide');
        if (action === 'show') {
            input.type = 'text';
            btnShow.style.display = 'none';
            btnHide.style.display = 'flex';
        } else {
            input.type = 'password';
            btnShow.style.display = 'flex';
            btnHide.style.display = 'none';
        }
    }
</script>

</body>
</html>
