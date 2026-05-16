<x-filament-panels::page>
    @php
        $stats = $this->getFreeApplicationStats();
    @endphp

    <div class="app-mgmt-free-banner">
        <div class="app-mgmt-free-banner__inner">
            <div class="app-mgmt-free-banner__head">
                <div>
                    <h2 class="app-mgmt-free-banner__title">{{ __('admin.application_section_free') }}</h2>
                    <p class="app-mgmt-free-banner__desc">{{ __('admin.application_section_free_desc') }}</p>
                </div>
                <a
                    href="{{ \App\Filament\Resources\ApplicationProgressResource::getUrl('free') }}"
                    class="app-mgmt-free-banner__cta"
                >
                    {{ __('admin.application_manage_free') }} →
                </a>
            </div>
            <div class="app-mgmt-free-banner__stats">
                <div class="app-mgmt-stat">
                    <span class="app-mgmt-stat__value">{{ $stats['total'] }}</span>
                    <span class="app-mgmt-stat__label">{{ __('admin.applications_tab_all') }}</span>
                </div>
                <div class="app-mgmt-stat app-mgmt-stat--warning">
                    <span class="app-mgmt-stat__value">{{ $stats['pending'] }}</span>
                    <span class="app-mgmt-stat__label">{{ __('Pending') }}</span>
                </div>
                <div class="app-mgmt-stat app-mgmt-stat--info">
                    <span class="app-mgmt-stat__value">{{ $stats['in_progress'] }}</span>
                    <span class="app-mgmt-stat__label">{{ __('In Progress') }}</span>
                </div>
                <div class="app-mgmt-stat app-mgmt-stat--review">
                    <span class="app-mgmt-stat__value">{{ $stats['awaiting_review'] }}</span>
                    <span class="app-mgmt-stat__label">{{ __('admin.applications_tab_awaiting_review') }}</span>
                </div>
            </div>
        </div>
    </div>

    <x-filament::section
        :heading="__('admin.application_section_job_offers')"
        :description="__('admin.application_section_job_offers_desc')"
    >
        {{ $this->table }}
    </x-filament::section>
</x-filament-panels::page>
