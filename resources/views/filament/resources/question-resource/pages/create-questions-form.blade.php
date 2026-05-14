<x-filament-panels::page class="fi-resource-create-questions-form-page">
    <div
        class="mb-6 rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-200">
        <p class="font-semibold text-gray-900 dark:text-white">{{ __('question_form.intro_title') }}</p>
        <p class="mt-1 leading-relaxed">{{ __('question_form.intro_body') }}</p>
    </div>

    <x-filament-panels::form
        id="form"
        :wire:key="$this->getId() . '.forms.' . $this->getFormStatePath()"
        wire:submit="save"
    >
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>
</x-filament-panels::page>
