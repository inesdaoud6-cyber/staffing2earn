<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('verify.title') }} - Staffing2Earn</title>
    @vite(['resources/css/global.css', 'resources/css/auth/login.css', 'resources/css/auth/verify.css'])
</head>
<body>
    <div class="verify-page-wrapper">
        <div class="verify-container">

            <div class="lang-bar">
                <a href="{{ route('lang.switch', 'fr') }}" class="lang-bar-btn {{ app()->getLocale()==='fr' ? 'active' : '' }}">FR</a>
                <a href="{{ route('lang.switch', 'en') }}" class="lang-bar-btn {{ app()->getLocale()==='en' ? 'active' : '' }}">EN</a>
                <a href="{{ route('lang.switch', 'ar') }}" class="lang-bar-btn {{ app()->getLocale()==='ar' ? 'active' : '' }}">AR</a>
            </div>

            <div class="verify-icon">📧</div>
            <h2 class="verify-heading">{{ __('verify.heading') }}</h2>
            <p class="verify-message">{{ __('verify.message') }}</p>

            @if (session('success'))
                <div class="alert-success"><p>{{ session('success') }}</p></div>
            @endif
            @if (session('status') == 'verification-link-sent')
                <div class="alert-success"><p>{{ __('verify.sent') }}</p></div>
            @endif

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="verify-btn">✉️ {{ __('verify.resend') }}</button>
            </form>

            <div class="verify-logout">
                <form method="POST" action="{{ route('auth.logout') }}">
                    @csrf
                    <button type="submit" class="verify-logout-btn">← {{ __('verify.logout') }}</button>
                </form>
            </div>

        </div>
    </div>
</body>
</html>