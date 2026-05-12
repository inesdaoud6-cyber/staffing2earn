<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Staffing2Earn – {{ __('Login') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/auth/login.css'])
</head>
<body>

<div class="login-topbar">
    <a href="{{ url('/') }}" class="back-btn">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        {{ __('Back to home') }}
    </a>
    <div class="lang-dropdown">
        <button class="lang-btn" onclick="toggleLang(event)">
            {{ strtoupper(app()->getLocale()) }}
            <svg width="10" height="10" viewBox="0 0 10 10"><path d="M2 3l3 4 3-4" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round"/></svg>
        </button>
        <div class="lang-menu" id="lang-menu">
            <a href="{{ route('lang.switch', 'fr') }}" class="{{ app()->getLocale() === 'fr' ? 'active' : '' }}">🇫🇷 FR – Français</a>
            <a href="{{ route('lang.switch', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'active' : '' }}">🇬🇧 EN – English</a>
            <a href="{{ route('lang.switch', 'ar') }}" class="{{ app()->getLocale() === 'ar' ? 'active' : '' }}">🇸🇦 AR – العربية</a>
        </div>
    </div>
</div>

<div class="page">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <div class="card">
        <div class="card-accent"></div>
        <div class="card-body">

            <div class="logo-area">
                <div class="logo-icon">S2</div>
                <div>
                    <div class="logo-name">Staffing2Earn</div>
                    <div class="logo-sub">{{ __('Candidate Space') }}</div>
                </div>
            </div>

            <h1>{{ __('Welcome back') }} 👋</h1>
            <p class="sub">{{ __('Sign in to your account to continue') }}</p>

            @if ($errors->any())
                <div class="alert error">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session('success'))
                <div class="alert success">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('auth.login.post') }}">
                @csrf

                <div class="field">
                    <label>{{ __('Email') }}</label>
                    <div class="input-icon-wrap">
                        <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="votre@email.com" required autofocus>
                    </div>
                </div>

                <div class="field">
                    <label>{{ __('Password') }}</label>
                    <div class="pw-wrap">
                        <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input type="password" id="pw" name="password" placeholder="••••••••" required>
                        <button type="button" class="pw-toggle" onclick="togglePw()">
                            <svg id="eye-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <div class="check-row">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember">
                        {{ __('Remember me') }}
                    </label>
                </div>

                <button type="submit" class="btn-submit">
                    {{ __('Sign in') }}
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </button>
            </form>

            <p class="register-link">
                {{ __('No account?') }}
                <a href="{{ route('auth.register') }}">{{ __('Create one') }}</a>
            </p>

            <div class="divider"><span>{{ __('Admin access') }}</span></div>

            <a href="{{ route('auth.admin.login') }}" class="btn-admin">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                {{ __('Admin portal') }}
            </a>

        </div>
    </div>
</div>

<script>
function togglePw() {
    const i = document.getElementById('pw');
    i.type = i.type === 'password' ? 'text' : 'password';
}
function toggleLang(e) {
    e.stopPropagation();
    document.getElementById('lang-menu').classList.toggle('open');
}
document.addEventListener('click', () => document.getElementById('lang-menu').classList.remove('open'));
</script>
</body>
</html