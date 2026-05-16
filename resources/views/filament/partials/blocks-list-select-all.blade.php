<label
    class="s2e-blocks-select-all-control inline-flex cursor-pointer select-none items-center gap-2.5 rounded-lg border-2 border-violet-300 bg-white px-3.5 py-2 shadow-sm ring-1 ring-violet-100 transition hover:border-violet-500 hover:bg-violet-50 dark:border-violet-500/60 dark:bg-gray-900 dark:ring-violet-500/20 dark:hover:border-violet-400 dark:hover:bg-violet-500/10"
>
    <x-filament::input.checkbox
        :attributes="
            (new \Illuminate\View\ComponentAttributeBag)->merge([
                'wire:loading.attr' => 'disabled',
                'wire:target' => implode(',', \Filament\Tables\Table::LOADING_TARGETS),
                'class' => 's2e-blocks-select-all-checkbox',
                'x-bind:checked' => '
                    const recordsOnPage = getRecordsOnPage()

                    if (recordsOnPage.length && areRecordsSelected(recordsOnPage)) {
                        $el.checked = true

                        return \'checked\'
                    }

                    $el.checked = false

                    return null
                ',
                'x-on:click' => 'toggleSelectRecordsOnPage',
            ], escape: false)
        "
    />
    <span class="text-sm font-semibold leading-none text-gray-900 dark:text-white">
        {{ __('admin.delete_all') }}
    </span>
</label>
