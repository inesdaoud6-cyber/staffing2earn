@php
    use Illuminate\View\ComponentAttributeBag;
    $sortWireKey = $sortWireKey ?? 'list-time-sort';
@endphp

<div class="flex items-center gap-x-2">
    <span class="whitespace-nowrap text-sm font-medium text-gray-950 dark:text-white">
        {{ __('admin.sort_by_date') }}
    </span>
    <div class="w-44 sm:w-52">
        <x-filament::input.wrapper>
            <x-filament::input.select
                :attributes="
                    (new ComponentAttributeBag)->merge([
                        'wire:model.live' => 'timeSort',
                        'wire:key' => $sortWireKey,
                    ])
                "
            >
                <option value="newest">{{ __('admin.sort_newest_first') }}</option>
                <option value="oldest">{{ __('admin.sort_oldest_first') }}</option>
            </x-filament::input.select>
        </x-filament::input.wrapper>
    </div>
</div>
