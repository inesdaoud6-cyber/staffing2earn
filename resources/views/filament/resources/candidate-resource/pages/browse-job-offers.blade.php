<x-filament-panels::page>
    @php
        $freeCount = $this->getFreeApplicationsCount();
        $freeUrl = $this->getFreeApplicationsUrl();
        $isCards = $this->tableLayout === 'cards';
    @endphp

    <a
        href="{{ $freeUrl }}"
        @class([
            'candidate-hub-free-entry',
            'candidate-hub-free-entry--card' => $isCards,
            'candidate-hub-free-entry--list' => ! $isCards,
        ])
    >
        <span class="candidate-hub-free-entry__title">{{ __('admin.application_title_free_applications') }}</span>
        <span class="candidate-hub-free-entry__meta">
            {{ __('admin.candidate_free_applications_count', ['count' => $freeCount]) }}
        </span>
        @svg('heroicon-o-arrow-right', 'candidate-hub-free-entry__icon')
    </a>

    {{ $this->table }}
</x-filament-panels::page>
