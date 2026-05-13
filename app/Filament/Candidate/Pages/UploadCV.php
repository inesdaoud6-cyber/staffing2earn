<?php

namespace App\Filament\Candidate\Pages;

use App\Models\ApplicationProgress;
use App\Models\Candidate;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;

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
    public bool $hasProfileCv = false;
    public ?string $profileCvPath = null;

    public function mount(): void
    {
        $candidate = Candidate::where('user_id', auth()->id())->first();

        $this->hasProfileCv  = (bool) $candidate?->cv_path;
        $this->profileCvPath = $candidate?->cv_path;

        $this->form->fill([
            'first_name' => $candidate?->first_name ?? '',
            'last_name'  => $candidate?->last_name ?? '',
            'phone'      => $candidate?->phone ?? '',
            'address'    => $candidate?->address ?? '',
            'birth_date' => $candidate?->birth_date ?? null,
            'cv_choice'  => $this->hasProfileCv ? 'profile' : 'new',
        ]);
    }

    public function form(Form $form): Form
    {
        $hasProfileCv  = $this->hasProfileCv;
        $profileCvPath = $this->profileCvPath;

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
                        Radio::make('cv_choice')
                            ->label(__('Which CV do you want to use?'))
                            ->options([
                                'profile' => __('Use the CV from my profile'),
                                'new'     => __('Upload a new CV for this application'),
                            ])
                            ->descriptions([
                                'profile' => $profileCvPath
                                    ? __('Currently on file:') . ' ' . basename($profileCvPath)
                                    : '',
                                'new' => __('The uploaded file will also become your profile CV.'),
                            ])
                            ->required()
                            ->live()
                            ->visible($hasProfileCv)
                            ->default($hasProfileCv ? 'profile' : 'new'),

                        Placeholder::make('current_cv_link')
                            ->label(__('Current profile CV'))
                            ->content(fn () => $profileCvPath
                                ? new HtmlString(
                                    '<a href="' . e(asset('storage/' . $profileCvPath)) . '" target="_blank" '
                                    . 'style="color:#1a1a8c;font-weight:600;text-decoration:underline;">'
                                    . '📄 ' . e(basename($profileCvPath)) . '</a>'
                                )
                                : '—')
                            ->visible(fn (Get $get) => $hasProfileCv && $get('cv_choice') === 'profile'),

                        FileUpload::make('cv')
                            ->label(__('Upload your CV') . ' (PDF)')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->disk('public')
                            ->directory('cvs')
                            ->required(fn (Get $get) => ! $hasProfileCv || $get('cv_choice') === 'new')
                            ->visible(fn (Get $get) => ! $hasProfileCv || $get('cv_choice') === 'new'),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $user = auth()->user();

        $candidate = Candidate::firstOrNew(['user_id' => $user->id]);
        $candidate->first_name = $data['first_name'];
        $candidate->last_name  = $data['last_name'];
        $candidate->phone      = $data['phone'];
        $candidate->birth_date = $data['birth_date'] ?? null;
        $candidate->address    = $data['address']    ?? null;

        $choice    = $data['cv_choice'] ?? 'new';
        $newUpload = $data['cv'] ?? null;

        if ($choice === 'new' && $newUpload) {
            $candidate->cv_path = $newUpload;
            $cvForApplication   = $newUpload;
        } else {
            $cvForApplication = $candidate->cv_path;
        }

        $candidate->save();

        $latestApp = ApplicationProgress::where('candidate_id', $candidate->id)
            ->whereNull('cv_path')
            ->latest()
            ->first();

        if ($latestApp) {
            $latestApp->update(['cv_path' => $cvForApplication]);
        }

        Notification::make()
            ->title(__('temoignage.cv_saved'))
            ->success()
            ->send();

        $this->redirect(route('filament.candidate.pages.take-test'));
    }
}
