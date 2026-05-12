<x-filament-panels::page>
    <form wire:submit="submit">
        {{ $this->form }}
        <div class="mt-6">
            <button type="submit" class="px-6 py-3 bg-purple-500 text-white rounded-lg font-bold hover:bg-purple-600">
                📄 {{ __('Submit My CV') }}
            </button>
        </div>
    </form>
</x-filament-panels::page>