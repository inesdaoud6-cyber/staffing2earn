<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OffreResource\Pages;
use App\Models\CandidateNotification;
use App\Models\Offre;
use App\Models\Test;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class OffreResource extends Resource
{
    protected static ?string $model = Offre::class;
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'Recrutement';
    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('nav.job_offers_management');
    }

    public static function getModelLabel(): string
    {
        return __('nav.job_offer');
    }

    public static function getPluralModelLabel(): string
    {
        return __('nav.job_offers');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('admin.offer_info'))->schema([
                Forms\Components\TextInput::make('title')->label(__('admin.title'))->required(),
                Forms\Components\Textarea::make('description')->label(__('admin.description'))->required(),
                Forms\Components\TextInput::make('domain')->label(__('admin.domain')),
                Forms\Components\TextInput::make('location')->label(__('admin.location')),
                Forms\Components\Select::make('contract_type')
                    ->label(__('admin.contract_type'))
                    ->options(['CDI' => 'CDI', 'CDD' => 'CDD', 'Stage' => 'Stage', 'Freelance' => 'Freelance']),
                Forms\Components\DatePicker::make('deadline')->label(__('admin.deadline')),
                Forms\Components\Toggle::make('is_published')->label(__('admin.publish')),
            ])->columns(2),

            Forms\Components\Section::make(__('admin.associated_test'))->schema([
                Forms\Components\Select::make('test_id')
                    ->label(__('admin.select_test'))
                    ->options(Test::pluck('name', 'id'))
                    ->searchable()
                    ->placeholder(__('admin.choose_test')),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\TextColumn::make('title')
                            ->label(__('admin.title'))
                            ->searchable()
                            ->weight('bold'),
                        Tables\Columns\IconColumn::make('is_published')
                            ->label(__('admin.published'))
                            ->boolean(),
                    ]),
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\TextColumn::make('domain')
                            ->label(__('admin.domain'))
                            ->icon('heroicon-o-map-pin')
                            ->size('sm'),
                        Tables\Columns\TextColumn::make('contract_type')
                            ->label(__('admin.contract_type'))
                            ->badge()
                            ->color('info'),
                    ]),
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\TextColumn::make('test.name')
                            ->label(__('admin.associated_test'))
                            ->badge()
                            ->color('success')
                            ->default(__('admin.none')),
                        Tables\Columns\TextColumn::make('applicationProgresses_count')
                            ->label(__('admin.applications'))
                            ->counts('applicationProgresses')
                            ->badge()
                            ->color('warning'),
                    ]),
                    Tables\Columns\TextColumn::make('deadline')
                        ->label(__('admin.deadline'))
                        ->date('d/m/Y')
                        ->icon('heroicon-o-calendar')
                        ->color('gray')
                        ->size('sm'),
                ])->space(2),
            ])
            ->actions([
                Tables\Actions\Action::make('notifier_tous')
                    ->label(__('admin.notify_candidates'))
                    ->icon('heroicon-o-bell')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.notify_all_heading'))
                    ->modalDescription(__('admin.notify_all_desc'))
                    ->action(function ($record) {
                        $candidats = User::role('candidate')->get();
                        $now = now();
                        $rows = $candidats->map(fn ($c) => [
                            'user_id'    => $c->id,
                            'type'       => 'offre',
                            'title'      => '💼 ' . __('admin.new_offer_published'),
                            'message'    => __('admin.new_offer_msg', ['title' => $record->title]),
                            'is_read'    => false,
                            'offre_id'   => $record->id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ])->all();

                        if (! empty($rows)) {
                            CandidateNotification::insert($rows);
                        }

                        Notification::make()->title($candidats->count() . ' ' . __('admin.candidates_notified'))->success()->send();
                    })
                    ->visible(fn ($record) => $record->is_published),

                Tables\Actions\EditAction::make()->label(__('Edit')),
                Tables\Actions\DeleteAction::make()->label(__('admin.delete')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOffres::route('/'),
            'create' => Pages\CreateOffre::route('/create'),
            'edit'   => Pages\EditOffre::route('/{record}/edit'),
        ];
    }
}