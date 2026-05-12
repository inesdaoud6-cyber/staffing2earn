<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationProgressResource\Pages;
use App\Models\ApplicationProgress;
use App\Models\CandidateNotification;
use App\Models\Offre;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class ApplicationProgressResource extends Resource
{
    protected static ?string $model = ApplicationProgress::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Recrutement';
    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string { return __('admin.applications'); }
    public static function getModelLabel(): string { return __('Application'); }
    public static function getPluralModelLabel(): string { return __('admin.applications'); }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informations')->schema([
                Forms\Components\Select::make('candidate_id')
                    ->label(__('nav.candidate'))
                    ->options(
                        \App\Models\Candidate::with('user')
                            ->get()
                            ->mapWithKeys(fn ($c) => [$c->id => ($c->full_name ?? $c->user?->name) . ' (' . $c->user?->email . ')'])
                    )
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('offre_id')
                    ->label(__('nav.job_offer'))
                    ->options(Offre::pluck('title', 'id'))
                    ->searchable()
                    ->placeholder('Candidature libre'),
                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options([
                        'pending'     => __('Pending'),
                        'in_progress' => __('In Progress'),
                        'validated'   => __('Validated'),
                        'rejected'    => __('Rejected'),
                    ])
                    ->required(),
                Forms\Components\TextInput::make('current_level')
                    ->label(__('Level'))
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('main_score')
                    ->label(__('admin.primary_score'))
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('secondary_score')
                    ->label(__('admin.secondary_score'))
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('apply_enabled')
                    ->label('Candidature activée'),
                Forms\Components\Toggle::make('score_published')
                    ->label(__('admin.published')),
            ])->columns(2),
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
                        Tables\Columns\TextColumn::make('candidate.user.name')
                            ->label(__('admin.full_name'))
                            ->searchable()
                            ->sortable()
                            ->weight('bold'),
                        Tables\Columns\TextColumn::make('status')
                            ->label(__('Status'))
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'pending'     => __('Pending'),
                                'in_progress' => __('In Progress'),
                                'validated'   => __('Validated'),
                                'rejected'    => __('Rejected'),
                                default       => $state,
                            })
                            ->color(fn ($state) => match ($state) {
                                'validated'   => 'success',
                                'rejected'    => 'danger',
                                'in_progress' => 'info',
                                default       => 'warning',
                            }),
                    ]),
                    Tables\Columns\TextColumn::make('offre.title')
                        ->label(__('nav.job_offer'))
                        ->default('Candidature libre')
                        ->searchable()
                        ->icon('heroicon-o-briefcase')
                        ->size('sm'),
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\TextColumn::make('current_level')
                            ->label(__('Level'))
                            ->badge()
                            ->color('info')
                            ->prefix('Niv. ')
                            ->sortable(),
                        Tables\Columns\TextColumn::make('main_score')
                            ->label(__('Score'))
                            ->badge()
                            ->color('success')
                            ->suffix('/100')
                            ->sortable(),
                        Tables\Columns\IconColumn::make('score_published')
                            ->label(__('admin.published'))
                            ->boolean(),
                    ]),
                    Tables\Columns\TextColumn::make('created_at')
                        ->label(__('Date'))
                        ->dateTime('d/m/Y')
                        ->sortable()
                        ->color('gray')
                        ->size('sm'),
                ])->space(2),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'pending'     => __('Pending'),
                        'in_progress' => __('In Progress'),
                        'validated'   => __('Validated'),
                        'rejected'    => __('Rejected'),
                    ]),
                Tables\Filters\SelectFilter::make('offre_id')
                    ->label(__('nav.job_offer'))
                    ->options(Offre::pluck('title', 'id')),
            ])
            ->actions([
                Tables\Actions\Action::make('valider_niveau')
                    ->label('✅ Valider niveau → suivant')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Valider ce niveau et passer au suivant ?')
                    ->modalDescription('Le candidat sera notifié et pourra répondre aux questions du niveau suivant.')
                    ->action(function ($record) {
                        $newLevel = $record->current_level + 1;
                        $record->update([
                            'current_level' => $newLevel,
                            'status'        => 'in_progress',
                        ]);
                        CandidateNotification::create([
                            'user_id'  => $record->candidate->user_id,
                            'type'     => 'info',
                            'title'    => '🎯 Niveau ' . ($newLevel - 1) . ' validé !',
                            'message'  => 'Bravo ! Votre niveau ' . ($newLevel - 1) . ' a été validé par l\'administrateur. Vous pouvez maintenant passer au niveau ' . $newLevel . '.',
                            'offre_id' => $record->offre_id,
                        ]);
                        Notification::make()
                            ->title('Niveau ' . ($newLevel - 1) . ' validé — Niveau ' . $newLevel . ' débloqué !')
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === 'in_progress'),

                Tables\Actions\Action::make('valider_finale')
                    ->label('🏆 Valider définitivement')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Valider définitivement cette candidature ?')
                    ->action(function ($record) {
                        $record->update(['status' => 'validated']);
                        CandidateNotification::create([
                            'user_id'  => $record->candidate->user_id,
                            'type'     => 'validated',
                            'title'    => '✅ Candidature acceptée !',
                            'message'  => 'Félicitations ! Votre candidature' . ($record->offre ? ' pour l\'offre "' . $record->offre->title . '"' : '') . ' a été entièrement validée.',
                            'offre_id' => $record->offre_id,
                        ]);
                        Notification::make()->title('Candidature validée + notification envoyée')->success()->send();
                    })
                    ->visible(fn ($record) => in_array($record->status, ['in_progress', 'pending'])),

                Tables\Actions\Action::make('rejeter')
                    ->label('❌ Rejeter')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Rejeter cette candidature ?')
                    ->action(function ($record) {
                        $record->update(['status' => 'rejected']);
                        CandidateNotification::create([
                            'user_id'  => $record->candidate->user_id,
                            'type'     => 'rejected',
                            'title'    => '❌ Candidature rejetée',
                            'message'  => 'Votre candidature' . ($record->offre ? ' pour l\'offre "' . $record->offre->title . '"' : '') . ' n\'a pas été retenue.',
                            'offre_id' => $record->offre_id,
                        ]);
                        Notification::make()->title('Candidature rejetée + notification envoyée')->danger()->send();
                    })
                    ->visible(fn ($record) => !in_array($record->status, ['rejected', 'validated'])),

                Tables\Actions\Action::make('publier_score')
                    ->label('📊 Publier score')
                    ->icon('heroicon-o-eye')
                    ->color('warning')
                    ->action(function ($record) {
                        $record->update(['score_published' => true]);
                        CandidateNotification::create([
                            'user_id'  => $record->candidate->user_id,
                            'type'     => 'info',
                            'title'    => '📊 Votre score est disponible !',
                            'message'  => 'L\'administrateur a publié votre score : ' . $record->main_score . '/100.',
                            'offre_id' => $record->offre_id,
                        ]);
                        Notification::make()->title('Score publié + notification envoyée')->success()->send();
                    })
                    ->visible(fn ($record) => !$record->score_published),

                Tables\Actions\EditAction::make()->label(__('Edit')),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('archiver')
                    ->label('Archiver sélection')
                    ->icon('heroicon-o-archive-box')
                    ->requiresConfirmation()
                    ->action(fn ($records) => $records->each->update(['is_archived' => true])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListApplicationProgress::route('/'),
            'create' => Pages\CreateApplicationProgress::route('/create'),
            'edit'   => Pages\EditApplicationProgress::route('/{record}/edit'),
        ];
    }
}