<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationProgressResource\Pages;
use App\Models\ApplicationProgress;
use App\Models\CandidateNotification;
use App\Models\Offre;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Table;

class ApplicationProgressResource extends Resource
{
    protected static ?string $model = ApplicationProgress::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Recrutement';

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('nav.applications_management');
    }

    public static function getModelLabel(): string
    {
        return __('Application');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.applications');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', '!=', 'cancelled');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('admin.application_cv_section'))
                ->description(__('admin.application_cv_section_hint'))
                ->schema([
                    Forms\Components\Placeholder::make('cv_review')
                        ->label(__('admin.application_cv'))
                        ->content(function (?ApplicationProgress $record): HtmlString {
                            if (! $record) {
                                return new HtmlString('');
                            }
                            $url = $record->cvPublicUrl();
                            if (! $url) {
                                return new HtmlString(
                                    '<p class="text-sm text-gray-500 dark:text-gray-400">' . e(__('admin.application_no_cv')) . '</p>'
                                );
                            }

                            return new HtmlString(
                                '<a href="' . e($url) . '" target="_blank" rel="noopener noreferrer" '
                                . 'class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-btn-color-primary fi-btn-variant outlined fi-size-md gap-1.5 px-3 py-2 text-sm inline-flex">'
                                . e(__('admin.application_view_cv_open'))
                                . '</a>'
                            );
                        })
                        ->visibleOn('edit'),
                ])
                ->columns(1)
                ->visibleOn('edit'),
            Forms\Components\Section::make(__('admin.application_section_info'))->schema([
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
                    ->placeholder(__('Open application')),
                Forms\Components\Select::make('test_id')
                    ->label(__('admin.application_associated_test'))
                    ->relationship('test', 'name')
                    ->searchable()
                    ->preload()
                    ->helperText(__('admin.application_associated_test_hint')),
                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options([
                        'pending'     => __('Pending'),
                        'in_progress' => __('In Progress'),
                        'validated'   => __('Validated'),
                        'rejected'    => __('Rejected'),
                        'cancelled'   => __('Cancelled'),
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
                    ->label(__('admin.application_toggle_apply_enabled')),
                Forms\Components\Toggle::make('score_published')
                    ->label(__('admin.published')),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return self::configureTable($table, 'list');
    }

    public static function configureTable(Table $table, string $layout = 'list'): Table
    {
        $layout = in_array($layout, ['list', 'cards'], true) ? $layout : 'list';

        $table = $table->defaultSort('created_at', 'desc');

        if ($layout === 'cards') {
            $table
                ->striped(false)
                ->contentGrid([
                    'md' => 2,
                    'xl' => 3,
                ])
                ->columns(self::applicationProgressCardColumns());
        } else {
            $table
                ->striped()
                ->columns(self::applicationProgressListColumns());
        }

        return $table
            ->recordUrl(fn (ApplicationProgress $record): string => static::getUrl('edit', ['record' => $record]))
            ->filters(self::applicationProgressTableFilters())
            ->actions(self::applicationProgressTableActions())
            ->bulkActions([]);
    }

    /**
     * @return array<int, Tables\Columns\Column>
     */
    private static function applicationProgressListColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('candidate.user.name')
                ->label(__('admin.application_column_applicant'))
                ->description(fn (ApplicationProgress $record): ?string => $record->candidate?->user?->email)
                ->searchable(query: self::applicantSearchQuery())
                ->sortable(),
            Tables\Columns\TextColumn::make('offre.title')
                ->label(__('admin.application_column_offer'))
                ->placeholder(__('Open application'))
                ->searchable()
                ->limit(36)
                ->tooltip(fn (ApplicationProgress $record): ?string => $record->offre?->title),
            Tables\Columns\TextColumn::make('status')
                ->label(__('Status'))
                ->badge()
                ->formatStateUsing(fn ($state) => match ($state) {
                    'pending'     => __('Pending'),
                    'in_progress' => __('In Progress'),
                    'validated'   => __('Validated'),
                    'rejected'    => __('Rejected'),
                    'cancelled'   => __('Cancelled'),
                    default       => (string) $state,
                })
                ->color(fn ($state) => match ($state) {
                    'validated'   => 'success',
                    'rejected'    => 'danger',
                    'cancelled'   => 'gray',
                    'in_progress' => 'info',
                    default       => 'warning',
                }),
            Tables\Columns\TextColumn::make('current_level')
                ->label(__('Level'))
                ->alignCenter()
                ->sortable(),
            Tables\Columns\TextColumn::make('main_score')
                ->label(__('Score'))
                ->suffix('/100')
                ->alignEnd()
                ->sortable(),
            Tables\Columns\IconColumn::make('score_published')
                ->label(__('admin.published'))
                ->boolean()
                ->alignCenter(),
            Tables\Columns\TextColumn::make('created_at')
                ->label(__('Date'))
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false),
        ];
    }

    /**
     * @return array<int, Stack>
     */
    private static function applicationProgressCardColumns(): array
    {
        return [
            Stack::make([
                Split::make([
                    Tables\Columns\TextColumn::make('candidate.user.name')
                        ->weight(FontWeight::Bold)
                        ->searchable(query: self::applicantSearchQuery())
                        ->sortable(),
                    Tables\Columns\TextColumn::make('created_at')
                        ->label(__('Date'))
                        ->dateTime('d/m/Y H:i')
                        ->size(TextColumnSize::ExtraSmall)
                        ->color('gray')
                        ->alignEnd()
                        ->sortable(),
                ]),
                Tables\Columns\TextColumn::make('candidate.user.email')
                    ->label(__('Email'))
                    ->icon('heroicon-o-envelope')
                    ->size(TextColumnSize::Small)
                    ->color('gray')
                    ->copyable(),
                Tables\Columns\TextColumn::make('offre.title')
                    ->label(__('admin.application_column_offer'))
                    ->placeholder(__('Open application'))
                    ->searchable()
                    ->weight(FontWeight::Medium)
                    ->limit(48)
                    ->tooltip(fn (ApplicationProgress $record): ?string => $record->offre?->title),
                Split::make([
                    Tables\Columns\TextColumn::make('status')
                        ->label(__('Status'))
                        ->badge()
                        ->formatStateUsing(fn ($state) => match ($state) {
                            'pending'     => __('Pending'),
                            'in_progress' => __('In Progress'),
                            'validated'   => __('Validated'),
                            'rejected'    => __('Rejected'),
                            'cancelled'   => __('Cancelled'),
                            default       => (string) $state,
                        })
                        ->color(fn ($state) => match ($state) {
                            'validated'   => 'success',
                            'rejected'    => 'danger',
                            'cancelled'   => 'gray',
                            'in_progress' => 'info',
                            default       => 'warning',
                        }),
                    Tables\Columns\TextColumn::make('current_level')
                        ->label(__('admin.application_column_level_short'))
                        ->badge()
                        ->color('gray')
                        ->alignEnd(),
                ]),
                Split::make([
                    Tables\Columns\TextColumn::make('main_score')
                        ->label(__('Score'))
                        ->suffix('/100')
                        ->weight(FontWeight::SemiBold),
                    Tables\Columns\IconColumn::make('score_published')
                        ->label(__('admin.published'))
                        ->boolean()
                        ->alignEnd(),
                ]),
            ])
                ->space(3)
                ->extraAttributes(['class' => 'application-progress-card']),
        ];
    }

    /**
     * @return Closure(object, string): void
     */
    private static function applicantSearchQuery(): Closure
    {
        return function ($query, string $search): void {
            $query->whereHas('candidate.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        };
    }

    /**
     * @return array<int, Tables\Filters\BaseFilter>
     */
    private static function applicationProgressTableFilters(): array
    {
        return [
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
        ];
    }

    /**
     * @return array<int, Tables\Actions\Action|Tables\Actions\ActionGroup>
     */
    private static function applicationProgressTableActions(): array
    {
        return [
            Tables\Actions\EditAction::make()
                ->label(__('Edit'))
                ->iconButton(),
            ActionGroup::make([
                Tables\Actions\Action::make('valider_niveau')
                    ->label(__('admin.application_action_validate_level'))
                    ->icon('heroicon-o-arrow-up-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.application_action_validate_level_heading'))
                    ->modalDescription(__('admin.application_action_validate_level_description'))
                    ->action(function (ApplicationProgress $record) {
                        $newLevel = $record->current_level + 1;
                        $oldLevel = $record->current_level;
                        $payload = [
                            'current_level' => $newLevel,
                            'status'        => 'in_progress',
                        ];
                        $nextTestId = $record->offre?->testIdForLevel($newLevel);
                        if ($nextTestId !== null) {
                            $payload['test_id'] = $nextTestId;
                        }
                        $record->update($payload);
                        CandidateNotification::create([
                            'user_id'  => $record->candidate->user_id,
                            'type'     => 'info',
                            'title'    => __('admin.candidate_notif_level_validated_title'),
                            'message'  => __('admin.candidate_notif_level_validated_body', [
                                'old' => (string) $oldLevel,
                                'new' => (string) $newLevel,
                            ]),
                            'offre_id' => $record->offre_id,
                        ]);
                        Notification::make()
                            ->title(__('admin.application_toast_level_advanced', [
                                'old' => (string) $oldLevel,
                                'new' => (string) $newLevel,
                            ]))
                            ->success()
                            ->send();
                    })
                    ->visible(fn (ApplicationProgress $record) => $record->status === 'in_progress'),
                Tables\Actions\Action::make('valider_finale')
                    ->label(__('admin.application_action_validate_final'))
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.application_action_validate_final_heading'))
                    ->action(function (ApplicationProgress $record) {
                        $record->update(['status' => 'validated']);
                        CandidateNotification::create([
                            'user_id'  => $record->candidate->user_id,
                            'type'     => 'validated',
                            'title'    => __('admin.candidate_notif_validated_title'),
                            'message'  => $record->offre
                                ? __('admin.candidate_notif_validated_body_with_offer', ['offer' => $record->offre->title])
                                : __('admin.candidate_notif_validated_body_open'),
                            'offre_id' => $record->offre_id,
                        ]);
                        Notification::make()
                            ->title(__('admin.application_toast_application_validated'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn (ApplicationProgress $record) => $record->status === 'in_progress'),
                Tables\Actions\Action::make('rejeter')
                    ->label(__('admin.application_action_reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.application_action_reject_heading'))
                    ->action(function (ApplicationProgress $record) {
                        $record->update(['status' => 'rejected']);
                        CandidateNotification::create([
                            'user_id'  => $record->candidate->user_id,
                            'type'     => 'rejected',
                            'title'    => __('admin.candidate_notif_rejected_title'),
                            'message'  => $record->offre
                                ? __('admin.candidate_notif_rejected_body_with_offer', ['offer' => $record->offre->title])
                                : __('admin.candidate_notif_rejected_body_open'),
                            'offre_id' => $record->offre_id,
                        ]);
                        Notification::make()
                            ->title(__('admin.application_toast_rejected'))
                            ->danger()
                            ->send();
                    })
                    ->visible(fn (ApplicationProgress $record) => ! in_array($record->status, ['rejected', 'validated'], true)),
                Tables\Actions\Action::make('publier_score')
                    ->label(__('admin.application_action_publish_score'))
                    ->icon('heroicon-o-chart-bar')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (ApplicationProgress $record) {
                        $record->update(['score_published' => true]);
                        CandidateNotification::create([
                            'user_id'  => $record->candidate->user_id,
                            'type'     => 'info',
                            'title'    => __('admin.candidate_notif_score_title'),
                            'message'  => __('admin.candidate_notif_score_body', ['score' => (string) $record->main_score]),
                            'offre_id' => $record->offre_id,
                        ]);
                        Notification::make()
                            ->title(__('admin.application_toast_score_published'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn (ApplicationProgress $record) => ! $record->score_published),
            ])
                ->label(__('Action'))
                ->icon('heroicon-m-ellipsis-vertical')
                ->button()
                ->outlined()
                ->size('sm'),
        ];
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
