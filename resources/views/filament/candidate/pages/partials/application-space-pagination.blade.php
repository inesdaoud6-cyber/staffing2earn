@if ($paginator->hasPages())
    <nav class="as-pagination" aria-label="{{ __('candidate.applications.pagination') }}">
        <button
            type="button"
            class="as-pagination__btn"
            wire:click="previousPage('applicationsPage')"
            wire:loading.attr="disabled"
            @disabled($paginator->onFirstPage())
        >
            ← {{ __('candidate.applications.prev_page') }}
        </button>

        <span class="as-pagination__info">
            {{ __('candidate.applications.page_info', [
                'current' => $paginator->currentPage(),
                'last' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ]) }}
        </span>

        <button
            type="button"
            class="as-pagination__btn"
            wire:click="nextPage('applicationsPage')"
            wire:loading.attr="disabled"
            @disabled(! $paginator->hasMorePages())
        >
            {{ __('candidate.applications.next_page') }} →
        </button>
    </nav>
@endif
