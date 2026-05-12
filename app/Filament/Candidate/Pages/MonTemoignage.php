<?php

namespace App\Filament\Candidate\Pages;

use App\Models\Temoignage;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class MonTemoignage extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';
    protected static string $view = 'filament.candidate.pages.mon-temoignage';
    protected static ?string $slug = 'mon-temoignage';
    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string
    {
        return __('nav.testimonials');
    }

    public function getTitle(): string
    {
        return __('nav.testimonials');
    }

    public ?array $data = [];
    public ?Temoignage $existing = null;

    public function mount(): void
    {
        $this->existing = Temoignage::where('user_id', auth()->id())->first();

        $this->form->fill([
            'contenu' => $this->existing?->contenu ?? '',
            'note'    => $this->existing?->note ?? 5,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('nav.testimonial'))->schema([
                Textarea::make('contenu')
                    ->label(__('admin.content'))
                    ->required()
                    ->rows(5)
                    ->maxLength(1000),

                Select::make('note')
                    ->label(__('admin.rating'))
                    ->options([
                        1 => '⭐ 1',
                        2 => '⭐⭐ 2',
                        3 => '⭐⭐⭐ 3',
                        4 => '⭐⭐⭐⭐ 4',
                        5 => '⭐⭐⭐⭐⭐ 5',
                    ])
                    ->default(5)
                    ->required(),
            ]),
        ])->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        Temoignage::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'contenu' => $data['contenu'],
                'note' => $data['note'],
                'is_approved' => false,
            ]
        );

        Notification::make()
            ->title(__('temoignage.saved'))
            ->success()
            ->send();
    }
}