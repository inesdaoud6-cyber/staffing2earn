<style>
    /* Notification bell + language switcher group */
    .topbar-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0 0.5rem;
    }

    /* ─── Bell ─── */
    .nb-wrapper { position: relative; }

    .nb-trigger {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 9px;
        background: transparent;
        color: #6b7280;
        border: 1.5px solid #e5e7eb;
        cursor: pointer;
        transition: background 0.15s, color 0.15s, border-color 0.15s;
    }
    .nb-trigger:hover { background: #f3f4f6; color: #1a1a8c; border-color: #c7d2fe; }

    .nb-icon { display: inline-flex; }
    .nb-icon svg { width: 20px; height: 20px; }

    .nb-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        color: #fff;
        border-radius: 999px;
        font-size: 0.65rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        box-shadow: 0 2px 6px rgba(220, 38, 38, 0.4);
        border: 2px solid #fff;
    }

    .nb-panel {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        width: 360px;
        max-width: calc(100vw - 2rem);
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        box-shadow: 0 12px 36px rgba(15, 23, 42, 0.18);
        z-index: 1000;
        overflow: hidden;
        animation: nb-slide 0.16s ease-out;
    }

    @keyframes nb-slide {
        from { transform: translateY(-4px); opacity: 0; }
        to   { transform: translateY(0); opacity: 1; }
    }

    .nb-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        background: #fafafa;
    }

    .nb-panel-title {
        font-weight: 700;
        font-size: 0.9rem;
        color: #111827;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }
    .nb-panel-title-icon { width: 16px; height: 16px; color: #1a1a8c; }

    .nb-link {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    .nb-link-icon { width: 13px; height: 13px; }

    .nb-link {
        background: transparent;
        border: none;
        color: #1a1a8c;
        font-size: 0.78rem;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
    }
    .nb-link:hover { text-decoration: underline; }

    .nb-panel-body {
        max-height: 360px;
        overflow-y: auto;
    }

    .nb-item {
        display: flex;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        cursor: pointer;
        border-bottom: 1px solid #f8fafc;
        transition: background 0.12s;
        position: relative;
    }
    .nb-item:hover { background: #f8fafc; }
    .nb-item:last-child { border-bottom: none; }

    .nb-item-unread { background: #f5f3ff; }
    .nb-item-unread:hover { background: #ede9fe; }

    .nb-item-icon {
        flex-shrink: 0;
        width: 34px;
        height: 34px;
        background: #f3f4f6;
        color: #4b5563;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .nb-item-icon-svg { width: 18px; height: 18px; }

    /* Type-tinted backgrounds for the small icon square */
    .nb-item-type-offre       .nb-item-icon { background: #eef2ff; color: #3730a3; }
    .nb-item-type-result      .nb-item-icon { background: #fef3c7; color: #92400e; }
    .nb-item-type-application .nb-item-icon { background: #dbeafe; color: #1e40af; }
    .nb-item-type-validated   .nb-item-icon { background: #d1fae5; color: #065f46; }
    .nb-item-type-rejected    .nb-item-icon { background: #fee2e2; color: #991b1b; }

    .nb-item-body { flex: 1; min-width: 0; }

    .nb-item-title {
        font-weight: 700;
        color: #111827;
        font-size: 0.85rem;
        margin-bottom: 0.15rem;
    }
    .nb-item-message {
        color: #4b5563;
        font-size: 0.78rem;
        line-height: 1.4;
        margin-bottom: 0.3rem;
        word-break: break-word;
    }
    .nb-item-meta {
        color: #9ca3af;
        font-size: 0.7rem;
    }

    .nb-dot {
        flex-shrink: 0;
        align-self: center;
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #1a1a8c;
    }

    .nb-empty {
        padding: 2rem 1rem;
        text-align: center;
        color: #9ca3af;
        font-size: 0.85rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.4rem;
    }
    .nb-empty-icon { width: 32px; height: 32px; color: #9ca3af; }

    .nb-panel-footer {
        padding: 0.6rem 1rem;
        border-top: 1px solid #f1f5f9;
        text-align: right;
        background: #fafafa;
    }

    /* ─── Lang switcher (kept inline for self-containment) ─── */
    .lang-switcher {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .lang-switcher a {
        padding: 3px 9px;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 700;
        text-decoration: none;
        border: 1.5px solid #d1d5db;
        background: transparent;
        color: #6b7280;
        transition: background 0.15s, color 0.15s, border-color 0.15s;
    }
    .lang-switcher a:hover { border-color: #4f46e5; color: #4f46e5; }
    .lang-switcher a.is-active {
        background: #4f46e5;
        color: #fff;
        border-color: #4f46e5;
    }
</style>

<div class="topbar-actions">
    @auth
        @livewire('notification-bell')
    @endauth

    <div class="lang-switcher">
        @php $locale = app()->getLocale(); @endphp
        <a href="{{ route('lang.switch', 'fr') }}" class="{{ $locale === 'fr' ? 'is-active' : '' }}">FR</a>
        <a href="{{ route('lang.switch', 'en') }}" class="{{ $locale === 'en' ? 'is-active' : '' }}">EN</a>
        <a href="{{ route('lang.switch', 'ar') }}" class="{{ $locale === 'ar' ? 'is-active' : '' }}">AR</a>
    </div>
</div>
