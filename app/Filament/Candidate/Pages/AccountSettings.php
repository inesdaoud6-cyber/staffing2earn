<?php

namespace App\Filament\Candidate\Pages;

use App\Models\Candidate;
use App\Services\CandidateService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;

class AccountSettings extends Page
{
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.candidate.pages.account-settings';
    protected static ?string $slug = 'account-settings';

    public function getTitle(): string
    {
        return __('My Profile');
    }

    public ?array $data = [];

    public function mount(CandidateService $service): void
    {
        $user      = auth()->user();
        $candidate = $service->getCandidateByUser($user);

        $this->form->fill([
            'first_name'                => $candidate?->first_name ?? '',
            'last_name'                 => $candidate?->last_name ?? '',
            'phone'                     => $candidate?->phone ?? '',
            'birth_date'                => $candidate?->birth_date ?? null,
            'address'                   => $candidate?->address ?? '',
            'cv'                        => $candidate?->cv_path,
            'name'                      => $user->name,
            'email'                     => $user->email,
            'current_password'          => '',
            'new_password'              => '',
            'new_password_confirmation' => '',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('Personal Information'))
                ->schema([
                    TextInput::make('first_name')
                        ->label(__('First Name'))
                        ->required(),
                    TextInput::make('last_name')
                        ->label(__('Last Name'))
                        ->required(),
                    TextInput::make('phone')
                        ->label(__('admin.phone'))
                        ->tel(),
                    DatePicker::make('birth_date')
                        ->label(__('temoignage.birth_date')),
                    TextInput::make('address')
                        ->label(__('temoignage.address'))
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make(__('Your CV'))
                ->description(__('Your default CV used for new applications. You can still override it per application from the upload-CV step.'))
                ->schema([
                    Placeholder::make('current_cv_link')
                        ->label(__('Current CV'))
                        ->content(function () {
                            $candidate = Candidate::where('user_id', auth()->id())->first();

                            return $candidate?->cv_path
                                ? new HtmlString(
                                    '<a href="' . e(asset('storage/' . $candidate->cv_path)) . '" target="_blank" '
                                    . 'style="color:#1a1a8c;font-weight:600;text-decoration:underline;">'
                                    . '📄 ' . e(basename($candidate->cv_path)) . '</a>'
                                )
                                : __('No CV uploaded yet.');
                        }),

                    FileUpload::make('cv')
                        ->label(__('Replace CV (PDF)'))
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(5120)
                        ->disk('public')
                        ->directory('cvs')
                        ->helperText(__('Leave empty to keep the current CV.')),
                ]),

            Section::make(__('Account'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Username'))
                        ->required(),
                    TextInput::make('email')
                        ->label(__('Email'))
                        ->email()
                        ->required(),
                ])
                ->columns(2),

            Section::make(__('Security'))
                ->schema([
                    TextInput::make('current_password')
                        ->label(__('Current password'))
                        ->password()
                        ->dehydrated(false),
                    TextInput::make('new_password')
                        ->label(__('New password'))
                        ->password()
                        ->minLength(8)
                        ->dehydrated(false),
                    TextInput::make('new_password_confirmation')
                        ->label(__('Confirm new password'))
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

        $candidate = $service->getCandidateByUser($user)
            ?? Candidate::firstOrNew(['user_id' => $user->id]);

        $candidate->user_id    = $user->id;
        $candidate->first_name = $data['first_name'];
        $candidate->last_name  = $data['last_name'];
        $candidate->phone      = $data['phone']      ?? null;
        $candidate->birth_date = $data['birth_date'] ?? null;
        $candidate->address    = $data['address']    ?? null;

        if (! empty($data['cv'])) {
            $candidate->cv_path = $data['cv'];
        }

        $candidate->save();

        $user->update([
            'name'  => $data['name'] ?: trim($data['first_name'] . ' ' . $data['last_name']),
            'email' => $data['email'],
        ]);

        if (! empty($data['new_password'])) {
            if (! Hash::check($data['current_password'] ?? '', $user->password)) {
                Notification::make()
                    ->title(__('Current password is incorrect'))
                    ->danger()
                    ->send();

                return;
            }

            if ($data['new_password'] !== $data['new_password_confirmation']) {
                Notification::make()
                    ->title(__('Password confirmation does not match'))
                    ->danger()
                    ->send();

                return;
            }

            $service->updatePassword($user, $data['new_password']);
        }

        $this->mount($service);

        Notification::make()
            ->title(__('Profile updated.'))
            ->success()
            ->send();
    }
}
