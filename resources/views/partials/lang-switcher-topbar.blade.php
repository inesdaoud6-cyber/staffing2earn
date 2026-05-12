<div style="display:flex;align-items:center;gap:4px;padding:0 0.75rem;">
    <a href="{{ route('lang.switch', 'fr') }}"
       style="padding:3px 9px;border-radius:6px;font-size:0.72rem;font-weight:700;text-decoration:none;border:1.5px solid {{ app()->getLocale()==='fr' ? '#4f46e5' : '#d1d5db' }};background:{{ app()->getLocale()==='fr' ? '#4f46e5' : 'transparent' }};color:{{ app()->getLocale()==='fr' ? '#fff' : '#6b7280' }};">
        FR
    </a>
    <a href="{{ route('lang.switch', 'en') }}"
       style="padding:3px 9px;border-radius:6px;font-size:0.72rem;font-weight:700;text-decoration:none;border:1.5px solid {{ app()->getLocale()==='en' ? '#4f46e5' : '#d1d5db' }};background:{{ app()->getLocale()==='en' ? '#4f46e5' : 'transparent' }};color:{{ app()->getLocale()==='en' ? '#fff' : '#6b7280' }};">
        EN
    </a>
    <a href="{{ route('lang.switch', 'ar') }}"
       style="padding:3px 9px;border-radius:6px;font-size:0.72rem;font-weight:700;text-decoration:none;border:1.5px solid {{ app()->getLocale()==='ar' ? '#4f46e5' : '#d1d5db' }};background:{{ app()->getLocale()==='ar' ? '#4f46e5' : 'transparent' }};color:{{ app()->getLocale()==='ar' ? '#fff' : '#6b7280' }};">
        AR
    </a>
</div>