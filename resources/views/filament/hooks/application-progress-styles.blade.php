@if (
    request()->routeIs('filament.admin.resources.application-progresses.*')
    || request()->routeIs('filament.admin.resources.candidates.*')
)
    @vite(['resources/css/admin-application-progress.css'])
@endif
