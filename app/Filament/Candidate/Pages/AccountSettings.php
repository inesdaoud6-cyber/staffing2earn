<?php

namespace App\Filament\Candidate\Pages;

use App\Models\Candidate;
use App\Services\CandidateService;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;

class AccountSettings extends Page
{
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.candidate.pages.account-settings';
    protected static ?string $title = 'Paramètres du Compte';
    protected static ?string $slug = 'account-settings';

    public ?array $data = [];

    public function mount(CandidateService $service): void
    {
        $user = auth()->user();
        $candidate = $service->getCandidateByUser($user);

        $this->form->fill([
            'first_name'                => $candidate?->first_name ?? '',
            'last_name'                 => $candidate?->last_name ?? '',
            'phone'                     => $candidate?->phone ?? '',
            'name'                      => $user->name,
            'email'                     => $user->email,
            'current_password'         => '',
            'new_password'             => '',
            'new_password_confirmation' => '',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Informations personnelles')
                ->schema([
                    TextInput::make('first_name')
                        ->label('Prénom')
                        ->required(),

                    TextInput::make('last_name')
                        ->label('Nom')
                        ->required(),

                    TextInput::make('phone')
                        ->label(__('admin.phone'))
                        ->tel(),
                ])
                ->columns(2),

            Section::make('Compte')
                ->schema([
                    TextInput::make('name')
                        ->label("Nom d'utilisateur")
                        ->required(),

                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required(),
                ])
                ->columns(2),

            Section::make('Sécurité')
                ->schema([
                    TextInput::make('current_password')
                        ->label('Mot de passe actuel')
                        ->password()
                        ->dehydrated(false),

                    TextInput::make('new_password')
                        ->label('Nouveau mot de passe')
                        ->password()
                        ->minLength(8)
                        ->dehydrated(false),

                    TextInput::make('new_password_confirmation')
                        ->label('Confirmation')
                        ->password()
                        ->dehydrated(false),
                ])
                ->columns(1),
        ])
        ->statePath('data');
    }

    public function save(CandidateService $service): void
    {
        $data = $this->form->getState();
        $user = auth()->user();

        $candidate = $service->getCandidateByUser($user);

        // Update profile
        if ($candidate) {
            $service->updateProfile($user, $candidate, $data);
        } else {
            Candidate::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => $data['first_name'],
                    'last_name'  => $data['last_name'],
                    'phone'      => $data['phone'],
                ]
            );

            $user->update([
                'name'  => $data['name'],
                'email' => $data['email'],
            ]);
        }

        // Password update (sécurisé)
        if (!empty($data['new_password'])) {

            if (!Hash::check($data['current_password'] ?? '', $user->password)) {
                Notification::make()
                    ->title('Mot de passe actuel incorrect')
                    ->danger()
                    ->send();

                return;
            }

            if ($data['new_password'] !== $data['new_password_confirmation']) {
                Notification::make()
                    ->title('La confirmation du mot de passe ne correspond pas')
                    ->danger()
                    ->send();

                return;
            }

            $service->updatePassword($user, $data['new_password']);
        }

        $this->mount($service);

        Notification::make()
            ->title('Modifications enregistrées !')
            ->success()
            ->send();
    }
}