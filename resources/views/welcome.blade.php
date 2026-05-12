<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staffing2Earn — {{ __('Welcome to Staffing2Earn') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --navy: #1E1EA8; --violet: #7C3AED; --magenta: #C026D3;
            --cyan: #06B6D4; --white: #ffffff; --light: #F5F4FE;
            --muted: #6B6B9A; --dark: #0F0F5E;
        }
        body { font-family: 'Inter', sans-serif; background: var(--white); color: #1a1a3e; overflow-x: hidden; }
        nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 5%; height: 68px;
            background: rgba(255,255,255,0.92); backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(124,58,237,0.12);
        }
        .nav-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .nav-brand img { height: 36px; }
        .nav-links { display: flex; align-items: center; gap: 2rem; list-style: none; }
        .nav-links a { text-decoration: none; color: var(--muted); font-size: 0.9rem; font-weight: 500; transition: color 0.2s; }
        .nav-links a:hover { color: var(--navy); }
        .btn-nav { background: var(--navy); color: var(--white) !important; padding: 0.5rem 1.25rem; border-radius: 8px; transition: background 0.2s !important; }
        .btn-nav:hover { background: var(--violet) !important; }

        /* Lang switcher */
        .lang-switcher { display: flex; align-items: center; gap: 4px; margin-left: 1rem; }
        .lang-btn {
            padding: 0.22rem 0.6rem; border-radius: 6px; font-size: 0.75rem; font-weight: 700;
            text-decoration: none; color: var(--muted); border: 1.5px solid transparent;
            transition: all 0.15s; letter-spacing: 0.04em;
        }
        .lang-btn:hover { border-color: var(--navy); color: var(--navy); }
        .lang-btn.active { background: var(--navy); color: #fff; border-color: var(--navy); }

        .hero { min-height: 100vh; display: flex; align-items: center; position: relative; overflow: hidden; padding: 100px 5% 60px; }
        .hero-bg { position: absolute; inset: 0; background: linear-gradient(135deg,#F5F4FE 0%,#EDE9FE 40%,#FAE8FF 70%,#ECFEFF 100%); z-index: 0; }
        .hero-orb { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.35; z-index: 0; }
        .orb-1 { width: 520px; height: 520px; background: var(--navy); top: -120px; right: -80px; }
        .orb-2 { width: 380px; height: 380px; background: var(--magenta); bottom: -80px; left: -60px; }
        .orb-3 { width: 280px; height: 280px; background: var(--cyan); top: 40%; right: 20%; }
        .hero-inner { position: relative; z-index: 1; max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center; width: 100%; }
        .hero-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(124,58,237,0.1); border: 1px solid rgba(124,58,237,0.25); border-radius: 999px; padding: 6px 14px; font-size: 0.78rem; font-weight: 600; color: var(--violet); margin-bottom: 1.5rem; text-transform: uppercase; letter-spacing: 0.06em; }
        .badge-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--magenta); animation: pulse 2s infinite; }
        @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(1.4)} }
        h1 { font-size: clamp(2.2rem,4vw,3.4rem); font-weight: 800; line-height: 1.15; color: var(--dark); margin-bottom: 1.25rem; }
        h1 .accent { background: linear-gradient(90deg,var(--navy),var(--magenta)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .hero-desc { font-size: 1.05rem; color: var(--muted); line-height: 1.7; margin-bottom: 2rem; max-width: 480px; }
        .hero-actions { display: flex; gap: 1rem; flex-wrap: wrap; }
        .btn-primary { display: inline-flex; align-items: center; gap: 8px; background: var(--navy); color: var(--white); text-decoration: none; padding: 0.85rem 1.75rem; border-radius: 10px; font-weight: 600; font-size: 0.95rem; transition: all 0.2s; }
        .btn-primary:hover { background: var(--violet); transform: translateY(-1px); }
        .btn-secondary { display: inline-flex; align-items: center; gap: 8px; background: transparent; color: var(--navy); text-decoration: none; padding: 0.85rem 1.75rem; border-radius: 10px; font-weight: 600; font-size: 0.95rem; border: 1.5px solid rgba(30,30,168,0.3); transition: all 0.2s; }
        .btn-secondary:hover { border-color: var(--navy); background: rgba(30,30,168,0.05); }
        .hero-stats { display: flex; gap: 2rem; margin-top: 2.5rem; padding-top: 2rem; border-top: 1px solid rgba(124,58,237,0.15); }
        .stat-num { font-size: 1.7rem; font-weight: 800; color: var(--dark); line-height: 1; }
        .stat-label { font-size: 0.78rem; color: var(--muted); margin-top: 3px; text-transform: uppercase; letter-spacing: 0.04em; }
        .hero-visual { display: flex; flex-direction: column; gap: 1rem; }
        .card-float { background: white; border-radius: 16px; padding: 1.25rem 1.5rem; border: 1px solid rgba(124,58,237,0.12); box-shadow: 0 4px 24px rgba(30,30,168,0.08); }
        .card-float-top { margin-right: 3rem; }
        .card-float-bottom { margin-left: 3rem; }
        .card-header { display: flex; align-items: center; gap: 10px; margin-bottom: 1rem; }
        .card-icon { width: 36px; height: 36px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: 17px; }
        .icon-navy { background: rgba(30,30,168,0.1); }
        .icon-magenta { background: rgba(192,38,211,0.1); }
        .card-title { font-weight: 700; font-size: 0.9rem; color: var(--dark); }
        .card-sub { font-size: 0.75rem; color: var(--muted); }
        .progress-bar { height: 6px; background: rgba(124,58,237,0.1); border-radius: 999px; overflow: hidden; margin-bottom: 6px; }
        .progress-fill { height: 100%; border-radius: 999px; }
        .fill-navy { background: linear-gradient(90deg,var(--navy),var(--violet)); width: 78%; }
        .fill-magenta { background: linear-gradient(90deg,var(--magenta),var(--cyan)); width: 92%; }
        .fill-cyan { background: linear-gradient(90deg,var(--cyan),var(--violet)); width: 65%; }
        .progress-label { display: flex; justify-content: space-between; font-size: 0.72rem; color: var(--muted); }
        .offre-row { display: flex; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid rgba(124,58,237,0.07); }
        .offre-row:last-child { border-bottom: none; }
        .offre-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
        .dot-navy { background: var(--navy); } .dot-magenta { background: var(--magenta); } .dot-cyan { background: var(--cyan); }
        .offre-title { font-size: 0.82rem; font-weight: 600; color: var(--dark); }
        .offre-domain { font-size: 0.72rem; color: var(--muted); }
        .badge-new { margin-left: auto; font-size: 0.7rem; font-weight: 700; padding: 3px 8px; border-radius: 999px; background: rgba(30,30,168,0.1); color: var(--navy); white-space: nowrap; }
        section.features { padding: 6rem 5%; background: white; }
        .section-tag { text-align: center; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--violet); margin-bottom: 0.75rem; }
        .section-title { text-align: center; font-size: clamp(1.7rem,3vw,2.4rem); font-weight: 800; color: var(--dark); margin-bottom: 0.75rem; }
        .section-desc { text-align: center; font-size: 1rem; color: var(--muted); max-width: 520px; margin: 0 auto 3.5rem; line-height: 1.7; }
        .features-grid { max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: repeat(3,1fr); gap: 1.5rem; }
        .feature-card { background: var(--light); border-radius: 16px; padding: 2rem; border: 1px solid rgba(124,58,237,0.1); transition: transform 0.2s,box-shadow 0.2s; }
        .feature-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(30,30,168,0.1); }
        .feature-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 1.25rem; }
        .fi-1 { background: linear-gradient(135deg,rgba(30,30,168,0.15),rgba(124,58,237,0.15)); }
        .fi-2 { background: linear-gradient(135deg,rgba(192,38,211,0.15),rgba(124,58,237,0.15)); }
        .fi-3 { background: linear-gradient(135deg,rgba(6,182,212,0.15),rgba(30,30,168,0.15)); }
        .feature-title { font-weight: 700; font-size: 1rem; color: var(--dark); margin-bottom: 0.5rem; }
        .feature-desc { font-size: 0.88rem; color: var(--muted); line-height: 1.65; }
        section.how { padding: 6rem 5%; background: var(--light); }
        .steps { max-width: 900px; margin: 0 auto; display: grid; grid-template-columns: repeat(4,1fr); gap: 1rem; position: relative; }
        .steps::before { content: ''; position: absolute; top: 28px; left: 12%; right: 12%; height: 2px; background: linear-gradient(90deg,var(--navy),var(--magenta)); z-index: 0; }
        .step { text-align: center; position: relative; z-index: 1; }
        .step-num { width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: 800; color: white; margin: 0 auto 1rem; }
        .sn1{background:var(--navy)} .sn2{background:var(--violet)} .sn3{background:var(--magenta)} .sn4{background:var(--cyan)}
        .step-title { font-weight: 700; font-size: 0.9rem; color: var(--dark); margin-bottom: 0.4rem; }
        .step-desc { font-size: 0.8rem; color: var(--muted); line-height: 1.55; }
        section.cta { padding: 6rem 5%; background: linear-gradient(135deg,var(--dark) 0%,var(--navy) 50%,#3B0764 100%); text-align: center; position: relative; overflow: hidden; }
        .cta-orb { position: absolute; border-radius: 50%; filter: blur(70px); opacity: 0.2; }
        .cta-orb-1 { width: 400px; height: 400px; background: var(--magenta); top: -100px; right: -80px; }
        .cta-orb-2 { width: 300px; height: 300px; background: var(--cyan); bottom: -80px; left: -60px; }
        .cta-inner { position: relative; z-index: 1; max-width: 620px; margin: 0 auto; }
        .cta h2 { font-size: clamp(1.8rem,3.5vw,2.8rem); font-weight: 800; color: white; margin-bottom: 1rem; line-height: 1.2; }
        .cta p { color: rgba(255,255,255,0.65); font-size: 1rem; margin-bottom: 2rem; line-height: 1.7; }
        .btn-cta { display: inline-flex; align-items: center; gap: 8px; background: white; color: var(--navy); text-decoration: none; padding: 0.95rem 2rem; border-radius: 10px; font-weight: 700; font-size: 0.95rem; transition: all 0.2s; }
        .btn-cta:hover { background: var(--light); transform: translateY(-2px); }
        footer { background: var(--dark); padding: 2rem 5%; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid rgba(255,255,255,0.07); }
        .footer-brand img { height: 28px; filter: brightness(0) invert(1); opacity: 0.7; }
        .footer-copy { font-size: 0.8rem; color: rgba(255,255,255,0.35); }
        .footer-links { display: flex; gap: 1.5rem; list-style: none; }
        .footer-links a { font-size: 0.8rem; color: rgba(255,255,255,0.4); text-decoration: none; transition: color 0.2s; }
        .footer-links a:hover { color: rgba(255,255,255,0.8); }
        @media(max-width:900px){
            .hero-inner{grid-template-columns:1fr;gap:2.5rem}
            .hero-visual{display:none}
            .features-grid{grid-template-columns:repeat(2,1fr)}
            .steps{grid-template-columns:repeat(2,1fr)}
            .steps::before{display:none}
            footer{flex-direction:column;gap:1rem;text-align:center}
        }
        @media(max-width:600px){
            .features-grid{grid-template-columns:1fr}
            .steps{grid-template-columns:1fr}
            .nav-links li:not(:last-child):not(.lang-item){display:none}
        }
    </style>
</head>

<body>

    <nav>
        <a href="{{ route('home') }}" class="nav-brand">
            <img src="{{ asset('images/2earn.png') }}" alt="Staffing2Earn">
        </a>
        <ul class="nav-links">
            <li><a href="#avantages">{{ __('About') }}</a></li>
            <li><a href="#how">{{ __('Home') }}</a></li>
            <li class="lang-item">
                <div class="lang-switcher">
                    <a href="{{ route('lang.switch', 'fr') }}" class="lang-btn {{ app()->getLocale() === 'fr' ? 'active' : '' }}">FR</a>
                    <a href="{{ route('lang.switch', 'en') }}" class="lang-btn {{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
                    <a href="{{ route('lang.switch', 'ar') }}" class="lang-btn {{ app()->getLocale() === 'ar' ? 'active' : '' }}">AR</a>
                </div>
            </li>
            <li><a href="{{ route('auth.login') }}" class="btn-nav">{{ __('Get Started') }}</a></li>
        </ul>
    </nav>

    <section class="hero">
        <div class="hero-bg"></div>
        <div class="orb-1 hero-orb"></div>
        <div class="orb-2 hero-orb"></div>
        <div class="orb-3 hero-orb"></div>
        <div class="hero-inner">
            <div class="hero-content">
                <div class="hero-badge">
                    <span class="badge-dot"></span>
                    {{ __('Smart Tests') }}
                </div>
                <h1>{{ __('Welcome to Staffing2Earn') }}<br><span class="accent">{{ __('Candidate Management') }}</span></h1>
                <p class="hero-desc">{{ __('An intelligent recruitment platform that connects talent with the best opportunities.') }}</p>
                <div class="hero-actions">
                    <a href="{{ route('auth.login') }}" class="btn-primary">
                        {{ __('Get Started') }}
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    <a href="#how" class="btn-secondary">{{ __('About') }}</a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-num">100%</div>
                        <div class="stat-label">{{ __('In Progress') }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-num">{{ __('Smart Tests') }}</div>
                        <div class="stat-label">{{ __('Take the Test') }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-num">{{ __('Validated') }}</div>
                        <div class="stat-label">{{ __('Score') }}</div>
                    </div>
                </div>
            </div>
            <div class="hero-visual">
                <div class="card-float card-float-top">
                    <div class="card-header">
                        <div class="card-icon icon-navy">📋</div>
                        <div>
                            <div class="card-title">{{ __('My Applications') }}</div>
                            <div class="card-sub">{{ __('In Progress') }}</div>
                        </div>
                    </div>
                    <div class="progress-bar"><div class="progress-fill fill-navy"></div></div>
                    <div class="progress-label"><span>{{ __('Questions') }}</span><span>78%</span></div>
                    <div style="margin-top:10px">
                        <div class="progress-bar"><div class="progress-fill fill-magenta"></div></div>
                        <div class="progress-label"><span>{{ __('Score') }}</span><span>{{ __('Validated') }}</span></div>
                    </div>
                    <div style="margin-top:10px">
                        <div class="progress-bar"><div class="progress-fill fill-cyan"></div></div>
                        <div class="progress-label"><span>{{ __('Status') }}</span><span>65%</span></div>
                    </div>
                </div>
                <div class="card-float card-float-bottom">
                    <div class="card-header">
                        <div class="card-icon icon-magenta">💼</div>
                        <div>
                            <div class="card-title">{{ __('Apply to an Offer') }}</div>
                            <div class="card-sub">{{ __('Start New Application') }}</div>
                        </div>
                    </div>
                    <div class="offre-row">
                        <div class="offre-dot dot-navy"></div>
                        <div>
                            <div class="offre-title">{{ __('Free Application') }}</div>
                            <div class="offre-domain">Tech</div>
                        </div>
                        <span class="badge-new">{{ __('Pending') }}</span>
                    </div>
                    <div class="offre-row">
                        <div class="offre-dot dot-magenta"></div>
                        <div>
                            <div class="offre-title">{{ __('Apply to an Offer') }}</div>
                            <div class="offre-domain">Management</div>
                        </div>
                        <span class="badge-new">{{ __('In Progress') }}</span>
                    </div>
                    <div class="offre-row">
                        <div class="offre-dot dot-cyan"></div>
                        <div>
                            <div class="offre-title">{{ __('Smart Tests') }}</div>
                            <div class="offre-domain">Finance</div>
                        </div>
                        <span class="badge-new">{{ __('Validated') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="features" id="avantages">
        <div class="section-tag">{{ __('About Staffing2Earn') }}</div>
        <h2 class="section-title">{{ __('Candidate Management') }}</h2>
        <p class="section-desc">{{ __('An intelligent recruitment platform that connects talent with the best opportunities.') }}</p>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon fi-1">🎯</div>
                <div class="feature-title">{{ __('Smart Tests') }}</div>
                <p class="feature-desc">{{ __('Create and manage multi-level assessment tests') }}</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon fi-2">⏱️</div>
                <div class="feature-title">{{ __('Track candidates throughout recruitment') }}</div>
                <p class="feature-desc">{{ __('Analyze performance and results easily') }}</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon fi-3">✅</div>
                <div class="feature-title">{{ __('Test Results') }}</div>
                <p class="feature-desc">{{ __('This is your personal recruitment space. Start your journey with us today.') }}</p>
            </div>
        </div>
    </section>

    <section class="how" id="how">
        <div class="section-tag">{{ __('Status') }}</div>
        <h2 class="section-title">{{ __('Start Your First Application') }}</h2>
        <p class="section-desc">{{ __('Apply without a specific offer. The admin will suggest a suitable test for you.') }}</p>
        <div class="steps">
            <div class="step">
                <div class="step-num sn1">1</div>
                <div class="step-title">{{ __('Apply to an Offer') }}</div>
                <p class="step-desc">{{ __('Apply to a specific job offer published by the company.') }}</p>
            </div>
            <div class="step">
                <div class="step-num sn2">2</div>
                <div class="step-title">{{ __('Submit My CV') }}</div>
                <p class="step-desc">{{ __('Upload your CV') }}</p>
            </div>
            <div class="step">
                <div class="step-num sn3">3</div>
                <div class="step-title">{{ __('Take the Test') }}</div>
                <p class="step-desc">{{ __('Answer all questions then click "Submit"') }}</p>
            </div>
            <div class="step">
                <div class="step-num sn4">4</div>
                <div class="step-title">{{ __('Test Results') }}</div>
                <p class="step-desc">{{ __('View Results') }}</p>
            </div>
        </div>
    </section>

    <section class="cta">
        <div class="cta-orb cta-orb-1"></div>
        <div class="cta-orb cta-orb-2"></div>
        <div class="cta-inner">
            <h2>{{ __('Welcome to Staffing2Earn') }}</h2>
            <p>{{ __('An intelligent recruitment platform that connects talent with the best opportunities.') }}</p>
            <a href="{{ route('auth.login') }}" class="btn-cta">
                {{ __('Get Started') }}
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </div>
    </section>

    <footer>
        <div class="footer-brand"><img src="{{ asset('images/2earn.png') }}" alt="Staffing2Earn"></div>
        <span class="footer-copy">© {{ date('Y') }} Staffing2Earn.</span>
        <ul class="footer-links">
            <li><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
            <li><a href="#avantages">{{ __('About') }}</a></li>
            <li><a href="{{ route('auth.login') }}">{{ __('Login') }}</a></li>
        </ul>
    </footer>

</body>
</html>