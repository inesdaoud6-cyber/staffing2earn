<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CandidateResource\Pages;
use App\Models\Candidate;
use App\Models\CandidateNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CandidateResource extends Resource
{
    protected static ?string $model = Candidate::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Recrutement';
    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('nav.candidates_management');
    }

    public static function getModelLabel(): string
    {
        return __('nav.candidate');
    }

    public static function getPluralModelLabel(): string
    {
        return __('nav.candidates');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.personal_info'))->schema([
                    Forms\Components\TextInput::make('first_name')
                        ->label(__('First Name'))
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('last_name')
                        ->label(__('Last Name'))
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('phone')
                        ->label(__('admin.phone'))
                        ->tel()
                        ->maxLength(255),
                ])->columns(3),

                Forms\Components\Section::make(__('admin.status_scores'))->schema([
                    Forms\Components\Select::make('status')
                        ->label(__('Status'))
                        ->options([
                            'pending'     => __('Pending'),
                            'in_progress' => __('In Progress'),
                            'validated'   => __('Validated'),
                            'rejected'    => __('Rejected'),
                        ])
                        ->default('pending'),
                    Forms\Components\TextInput::make('primary_score')
                        ->label(__('admin.primary_score'))
                        ->numeric(),
                    Forms\Components\TextInput::make('secondary_score')
                        ->label(__('admin.secondary_score'))
                        ->numeric(),
                ])->columns(3),
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
                        Tables\Columns\TextColumn::make('user.name')
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
                    Tables\Columns\TextColumn::make('user.email')
                        ->label(__('Email'))
                        ->searchable()
                        ->color('gray')
                        ->size('sm'),
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\TextColumn::make('phone')
                            ->label(__('admin.phone'))
                            ->icon('heroicon-o-phone')
                            ->size('sm'),
                        Tables\Columns\IconColumn::make('cv_path')
                            ->label('CV')
                            ->boolean()
                            ->trueIcon('heroicon-o-document')
                            ->falseIcon('heroicon-o-x-mark'),
                        Tables\Columns\TextColumn::make('primary_score')
                            ->label(__('Score'))
                            ->badge()
                            ->color('success')
                            ->suffix('/100'),
                    ]),
                    Tables\Columns\TextColumn::make('user.created_at')
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
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('voir_cv')
                    ->label(__('admin.view_cv'))
                    ->icon('heroicon-o-document')
                    ->color('info')
                    ->url(fn ($record) => $record->cv_path
                        ? asset('storage/' . $record->cv_path)
                        : null)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => (bool) $record->cv_path),

                Tables\Actions\Action::make('approuver')
                    ->label(__('admin.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status' => 'validated']);
                        CandidateNotification::create([
                            'user_id' => $record->user_id,
                            'type'    => 'validated',
                            'title'   => '✅ ' . __('admin.profile_approved'),
                            'message' => __('admin.profile_approved_msg'),
                        ]);
                        Notification::make()
                            ->title(__('admin.approved_notif'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === 'pending'),

                Tables\Actions\Action::make('rejeter')
                    ->label(__('admin.reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status' => 'rejected']);
                        CandidateNotification::create([
                            'user_id' => $record->user_id,
                            'type'    => 'rejected',
                            'title'   => '❌ ' . __('admin.profile_rejected'),
                            'message' => __('admin.profile_rejected_msg'),
                        ]);
                        Notification::make()
                            ->title(__('admin.rejected_notif'))
                            ->danger()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status !== 'rejected'),

                Tables\Actions\EditAction::make()->label(__('Edit')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCandidates::route('/'),
            'create' => Pages\CreateCandidate::route('/create'),
            'edit'   => Pages\EditCandidate::route('/{record}/edit'),
        ];
    }
}