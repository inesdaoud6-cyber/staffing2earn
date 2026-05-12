<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Staffing2Earn — Admin Login</title>
    @vite(['resources/css/auth/login.css'])
</head>
<body>

<div class="login-topbar">
    <a href="{{ route('auth.login') }}" class="back-btn">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        {{ __('Candidate space') }}
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

    <div class="card">
        <div class="card-accent" style="background: linear-gradient(90deg, #7c3aed, #1a1a8c, #0f172a);"></div>
        <div class="card-body">

            <div class="logo-area">
                <div class="logo-icon" style="background: linear-gradient(135deg, #7c3aed, #1a1a8c);">🛡️</div>
                <div>
                    <div class="logo-name">Staffing2Earn</div>
                    <div class="logo-sub">{{ __('login.espace-admin') }}</div>
                </div>
            </div>

            <h1>{{ __('login.acces-admin') }}</h1>
            <p class="sub">{{ __('login.connexion-admin') }}</p>

            @if ($errors->any())
                <div class="alert error">{{ $errors->first() }}</div>
            @endif
            @if (session('success'))
                <div class="alert success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('auth.admin.login.post') }}">
                @csrf

                <div class="field">
                    <label>{{ __('Email') }}</label>
                    <div class="input-icon-wrap">
                        <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="admin@staffing.com" required autofocus>
                    </div>
                    @error('email')<span class="error-message">{{ $message }}</span>@enderror
                </div>

                <div class="field">
                    <label>{{ __('Password') }}</label>
                    <div class="pw-wrap">
                        <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input type="password" id="pw" name="password" placeholder="••••••••" required>
                        <button type="button" class="pw-toggle" onclick="togglePw()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    @error('password')<span class="error-message">{{ $message }}</span>@enderror
                </div>

                <div class="check-row">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        {{ __('Remember me') }}
                    </label>
                </div>

                <button type="submit" class="btn-submit" style="background: linear-gradient(135deg, #7c3aed, #1a1a8c);">
                    {{ __('Sign in as Admin') }}
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </button>
            </form>

            <div class="divider"><span>{{ __('or') }}</span></div>

            <a href="{{ route('auth.login') }}" class="btn-admin">
                👤 {{ __('login.espace-candidat') }}
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
</html>