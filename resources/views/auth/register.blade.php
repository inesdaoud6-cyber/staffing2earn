<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('register.create-account') }} - Staffing2Earn</title>
    @vite(['resources/css/global.css', 'resources/css/navbar.css', 'resources/css/auth/register.css'])
</head>
<body>

    <nav class="navbar">
        <div class="navbar-brand">
            <img src="{{ Vite::asset('resources/images/logo.png') }}" alt="Logo">
            <h2>Staffing2Earn</h2>
        </div>
        <ul class="nav-links">
            <li><a href="/">{{ __('nav.home') }}</a></li>
            <li><a href="/#about">{{ __('nav.about') }}</a></li>
            <li>
                <div style="display:flex;align-items:center;gap:4px;">
                    <a href="{{ route('lang.switch', 'fr') }}" style="padding:2px 8px;border-radius:5px;font-size:0.75rem;font-weight:700;text-decoration:none;border:1.5px solid {{ app()->getLocale()==='fr' ? '#667eea' : '#d1d5db' }};background:{{ app()->getLocale()==='fr' ? '#667eea' : 'transparent' }};color:{{ app()->getLocale()==='fr' ? '#fff' : '#6b7280' }};">FR</a>
                    <a href="{{ route('lang.switch', 'en') }}" style="padding:2px 8px;border-radius:5px;font-size:0.75rem;font-weight:700;text-decoration:none;border:1.5px solid {{ app()->getLocale()==='en' ? '#667eea' : '#d1d5db' }};background:{{ app()->getLocale()==='en' ? '#667eea' : 'transparent' }};color:{{ app()->getLocale()==='en' ? '#fff' : '#6b7280' }};">EN</a>
                    <a href="{{ route('lang.switch', 'ar') }}" style="padding:2px 8px;border-radius:5px;font-size:0.75rem;font-weight:700;text-decoration:none;border:1.5px solid {{ app()->getLocale()==='ar' ? '#667eea' : '#d1d5db' }};background:{{ app()->getLocale()==='ar' ? '#667eea' : 'transparent' }};color:{{ app()->getLocale()==='ar' ? '#fff' : '#6b7280' }};">AR</a>
                </div>
            </li>
        </ul>
    </nav>

    <div class="register-container">

        <div class="login-badge candidate">
            👤 {{ __('login.espace-candidat') }}
        </div>

        <h2>{{ __('register.create-your-account') }}</h2>
        <p class="register-subtitle">{{ __('register.subtitle') }}</p>

        @if ($errors->any())
            <div class="alert-error">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('auth.register.post') }}">
            @csrf

            <div class="form-section">
                <h3 class="section-title">{{ __('register.personal-information') }}</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">{{ __('register.first-name') }} <span class="required">*</span></label>
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required autofocus placeholder="{{ __('register.first-name-placeholder') }}">
                        @error('first_name')<span class="error-message">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label for="last_name">{{ __('register.last-name') }} <span class="required">*</span></label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required placeholder="{{ __('register.last-name-placeholder') }}">
                        @error('last_name')<span class="error-message">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">{{ __('register.email') }} <span class="required">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="john.doe@example.com">
                    @error('email')<span class="error-message">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">{{ __('register.security') }}</h3>

                <div class="form-group">
                    <label for="password">{{ __('register.password') }} <span class="required">*</span></label>
                    <div class="input-password">
                        <input type="password" id="password" name="password" required placeholder="{{ __('register.password-placeholder') }}">
                        <button type="button" class="toggle-password" onclick="togglePassword('password')">👁</button>
                    </div>
                    @error('password')<span class="error-message">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">{{ __('register.confirm-password') }} <span class="required">*</span></label>
                    <div class="input-password">
                        <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="{{ __('register.confirm-password-placeholder') }}">
                        <button type="button" class="toggle-password" onclick="togglePassword('password_confirmation')">👁</button>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-register">{{ __('register.submit') }} →</button>
        </form>

        <div class="back-li