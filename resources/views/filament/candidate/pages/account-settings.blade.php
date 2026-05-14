<x-filament-panels::page>
    @vite('resources/css/candidate-settings.css')

    @can('manage-candidates')
    <div style="background:#fef3c7;border:1px solid #f59e0b;border-radius:10px;padding:0.75rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;">
        <span style="color:#92400e;font-weight:600;">🛡️ {{ __('Vue administrateur') }}</span>
        <a href="/admin" style="background:#f59e0b;color:#fff;padding:0.35rem 0.9rem;border-radius:6px;font-size:0.85rem;text-decoration:none;">← Retour admin</a>
    </div>
    @endcan

    <div class="settings-header">
        <div class="settings-header-icon">⚙️</div>
        <div>
            <h2>{{__("Paramètres du Compte") }} </h2>
            <p>{{__("Modifiez vos informations personnelles et votre mot de passe") }}</p>
        </div>
    </div>

    <form wire:submit.prevent="save">
        {{ $this->form }}
        <button type="submit" class="settings-save-btn">
            {{__(" 💾 Enregistrer les modifications") }}
        </button>
    </form>
</x-filament-panels::page>