<?php

namespace App\Filament\Candidate\Pages;

use App\Models\ApplicationProgress;
use App\Models\Candidate;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class UploadCV extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';
    protected static ?string $slug = 'upload-cv';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.candidate.pages.upload-cv';

    public function getTitle(): string
    {
        return __('Submit My CV');
    }

    public ?array $data = [];

    public function mount(): void
    {
        $candidate = Candidate::where('user_id', auth()->id())->first();

        $this->form->fill([
            'first_name' => $candidate?->first_name ?? '',
            'last_name'  => $candidate?->last_name ?? '',
            'phone'      => $candidate?->phone ?? '',
            'address'    => $candidate?->address ?? '',
            'birth_date' => $candidate?->birth_date ?? null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Personal Information'))
                    ->description(__('temoignage.fill_profile_desc'))
                    ->schema([
                        TextInput::make('first_name')
                            ->label(__('First Name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->label(__('Last Name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label(__('admin.phone'))
                            ->tel()
                            ->required(),
                        DatePicker::make('birth_date')
                            ->label(__('temoignage.birth_date'))
                            ->required(),
                        TextInput::make('address')
                            ->label(__('temoignage.address'))
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('Your CV'))
                    ->description(__('temoignage.cv_desc'))
                    ->schema([
                        FileUpload::make('cv')
                            ->label(__('Upload your CV') . ' (PDF)')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->disk('public')
                            ->directory('cvs')
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        Candidate::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'cv_path'    => $data['cv'],
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'phone'      => $data['phone'],
                'birth_date' => $data['birth_date'] ?? null,
                'address'    => $data['address'] ?? null,
            ]
        );

        Notification::make()
            ->title(__('temoignage.cv_saved'))
            ->success()
            ->send();

        $this->redirect(route('filament.candidate.pages.take-test'));
    }
}