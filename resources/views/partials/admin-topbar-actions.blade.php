<style>
    .topbar-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0 0.5rem;
    }

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
    .nb-trigger:hover { background: #f3f4f6; color: #5b21b6; border-color: #ddd6fe; }

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
        animation: nb-slide-admin 0.16s ease-out;
    }

    @keyframes nb-slide-admin {
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
    .nb-panel-title-icon { width: 16px; height: 16px; color: #5b21b6; }

    .nb-link {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        background: transparent;
        border: none;
        color: #5b21b6;
        font-size: 0.78rem;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
    }
    .nb-link:hover { text-decoration: underline; }
    .nb-link-icon { width: 13px; height: 13px; }

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

    .nb-item-type-application .nb-item-icon { background: #ede9fe; color: #5b21b6; }

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
        background: #5b21b6;
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
    .lang-switcher a:hover { border-color: #5b21b6; color: #5b21b6; }
    .lang-switcher a.is-active {
        background: #5b21b6;
        color: #fff;
        border-color: #5b21b6;
    }
</style>

<div class="topbar-actions">
    @auth
        @livewire('admin-notification-bell')
    @endauth

    <div class="lang-switcher">
        @php $locale = app()->getLocale(); @endphp
        <a href="{{ route('lang.switch', 'fr') }}" data-navigate-ignore wire:navigate.off class="{{ $locale === 'fr' ? 'is-active' : '' }}">FR</a>
        <a href="{{ route('lang.switch', 'en') }}" data-navigate-ignore wire:navigate.off class="{{ $locale === 'en' ? 'is-active' : '' }}">EN</a>
        <a href="{{ route('lang.switch', 'ar') }}" data-navigate-ignore wire:navigate.off class="{{ $locale === 'ar' ? 'is-active' : '' }}">AR</a>
    </div>
</div>