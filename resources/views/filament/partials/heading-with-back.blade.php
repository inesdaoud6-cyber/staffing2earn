@props([
    'url',
    'label',
    'heading',
])

<div class="fi-page-back-heading flex flex-col gap-2">
    <a
        href="{{ $url }}"
        class="fi-page-back-link inline-flex w-fit items-center gap-1.5 text-sm font-medium text-gray-600 transition hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400"
    >
        @svg('heroicon-o-arrow-left', 'h-4 w-4 shrink-0')
        <span>{{ $label }}</span>
    </a>

    <h1 class="fi-header-heading text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl">
        {{ $heading }}
    </h1>
</div>
