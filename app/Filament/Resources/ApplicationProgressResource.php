<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationProgressResource\Pages;
use App\Models\ApplicationProgress;
use App\Models\Candidate;
use App\Models\CandidateNotification;
use App\Models\Offre;
use App\Models\Response;
use App\Models\Test;
use App\Services\FreeApplicationWorkflow;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Navigation\NavigationItem;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page as FilamentResourcePage;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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

    public static function getNavigationItems(): array
    {
        $awaitingFree = static::countFreeApplicationsAwaitingReview();

        return [
            NavigationItem::make(static::getNavigationLabel())
                ->group(static::getNavigationGroup())
                ->icon(static::getNavigationIcon())
                ->activeIcon(static::getActiveNavigationIcon())
                ->isActiveWhen(fn (): bool => request()->routeIs(static::getRouteBaseName().'.index')
                    || request()->routeIs(static::getRouteBaseName().'.by_offer'))
                ->badge(static::countJobApplicationsAwaitingReview() ?: null)
                ->sort(static::getNavigationSort())
                ->url(static::getUrl('index')),
            NavigationItem::make(__('admin.application_nav_free'))
                ->group(static::getNavigationGroup())
                ->icon('heroicon-o-inbox')
                ->isActiveWhen(fn (): bool => request()->routeIs(static::getRouteBaseName().'.free'))
                ->badge($awaitingFree > 0 ? (string) $awaitingFree : null)
                ->sort(static::getNavigationSort() + 1)
                ->url(static::getUrl('free')),
        ];
    }

    public static function countFreeApplicationsAwaitingReview(): int
    {
        return (int) static::getEloquentQuery()
            ->whereNull('offre_id')
            ->where('level_status', 'awaiting_approval')
            ->count();
    }

    public static function countJobApplicationsAwaitingReview(): int
    {
        return (int) static::getEloquentQuery()
            ->whereNotNull('offre_id')
            ->where('level_status', 'awaiting_approval')
            ->count();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', '!=', 'cancelled');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('admin.application_review_pages_hint_title'))
                ->description(__('admin.application_review_pages_hint_body'))
                ->visibleOn('edit')
                ->collapsible()
                ->collapsed(),
            Section::make(__('admin.application_section_info'))->schema([
                Select::make('candidate_id')
                    ->label(__('nav.candidate'))
                    ->options(
                        Candidate::with('user')
                            ->get()
                            ->mapWithKeys(fn ($c) => [$c->id => ($c->full_name ?? $c->user?->name).' ('.$c->user?->email.')'])
                    )
                    ->searchable()
                    ->required(),
                Select::make('offre_id')
                    ->label(__('nav.job_offer'))
                    ->options(Offre::pluck('title', 'id'))
                    ->searchable()
                    ->placeholder(__('Open application')),
                Select::make('test_id')
                    ->label(__('admin.application_associated_test'))
                    ->relationship('test', 'name')
                    ->searchable()
                    ->preload()
                    ->helperText(fn (?ApplicationProgress $record): string => $record?->isFreeApplication()
                        ? __('admin.application_associated_test_hint_free')
                        : __('admin.application_associated_test_hint')),
                Select::make('status')
                    ->label(__('Status'))
                    ->options([
                        'pending' => __('Pending'),
                        'in_progress' => __('In Progress'),
                        'validated' => __('Validated'),
                        'rejected' => __('Rejected'),
                        'cancelled' => __('Cancelled'),
                    ])
                    ->required(),
                TextInput::make('current_level')
                    ->label(__('Level'))
                    ->numeric()
                    ->default(1),
                TextInput::make('main_score')
                    ->label(__('admin.application_score'))
                    ->helperText(__('admin.application_score_hint'))
                    ->numeric()
                    ->default(0),
                TextInput::make('secondary_score')
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

    public static function configureTable(Table $table, string $layout = 'list', bool $hideOfferColumn = false): Table
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
                ->columns(self::applicationProgressCardColumns($hideOfferColumn));
        } else {
            $table
                ->striped()
                ->columns(self::applicationProgressListColumns($hideOfferColumn));
        }

        return $table
            ->recordUrl(function (ApplicationProgress $record): string {
                if ($record->status === 'pending') {
                    return static::getUrl('review_level', ['record' => $record, 'level' => 1]);
                }
                if ($record->isAwaitingTestAssignment()) {
                    return static::getUrl('review_level', ['record' => $record, 'level' => 1]);
                }
                if ($record->level_status === 'awaiting_approval') {
                    return static::getUrl('review_level', ['record' => $record, 'level' => $record->current_level]);
                }

                return static::getUrl('edit', ['record' => $record]);
            })
            ->filters(self::applicationProgressTableFilters())
            ->actions(self::applicationProgressTableActions())
            ->bulkActions([]);
    }

    /**
     * @return array<int, Tables\Columns\Column>
     */
    private static function applicationProgressListColumns(bool $hideOfferColumn = false): array
    {
        $columns = [
            Tables\Columns\TextColumn::make('candidate.user.name')
                ->label(__('admin.application_column_applicant'))
                ->description(fn (ApplicationProgress $record): ?string => $record->candidate?->user?->email)
                ->searchable(query: self::applicantSearchQuery())
                ->sortable(),
        ];

        if (! $hideOfferColumn) {
            $columns[] = Tables\Columns\TextColumn::make('offre.title')
                ->label(__('admin.application_column_offer'))
                ->placeholder(__('Open application'))
                ->searchable()
                ->limit(36)
                ->tooltip(fn (ApplicationProgress $record): ?string => $record->offre?->title);
        } else {
            $columns[] = Tables\Columns\TextColumn::make('test.name')
                ->label(__('admin.application_associated_test'))
                ->placeholder('—')
                ->limit(32)
                ->toggleable();
        }

        return [
            ...$columns,
            Tables\Columns\TextColumn::make('status')
                ->label(__('Status'))
                ->badge()
                ->formatStateUsing(fn ($state) => match ($state) {
                    'pending' => __('Pending'),
                    'in_progress' => __('In Progress'),
                    'validated' => __('Validated'),
                    'rejected' => __('Rejected'),
                    'cancelled' => __('Cancelled'),
                    default => (string) $state,
                })
                ->color(fn ($state) => match ($state) {
                    'validated' => 'success',
                    'rejected' => 'danger',
                    'cancelled' => 'gray',
                    'in_progress' => 'info',
                    default => 'warning',
                }),
            Tables\Columns\TextColumn::make('current_level')
                ->label(__('Level'))
                ->alignCenter()
                ->sortable(),
            Tables\Columns\TextColumn::make('level_status')
                ->label(__('admin.application_column_level_status'))
                ->badge()
                ->formatStateUsing(fn ($state) => match ($state) {
                    'in_progress' => __('admin.level_status_in_progress'),
                    'awaiting_approval' => __('admin.level_status_awaiting_approval'),
                    'approved' => __('admin.level_status_approved'),
                    'rejected' => __('admin.level_status_rejected'),
                    default => $state ? (string) $state : '—',
                })
                ->color(fn ($state) => match ($state) {
                    'awaiting_approval' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                    default => 'gray',
                })
                ->toggleable(isToggledHiddenByDefault: false),
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
    private static function applicationProgressCardColumns(bool $hideOfferColumn = false): array
    {
        $contextColumns = $hideOfferColumn
            ? [
                Tables\Columns\TextColumn::make('test.name')
                    ->label(__('admin.application_associated_test'))
                    ->placeholder('—')
                    ->weight(FontWeight::Medium)
                    ->limit(48),
            ]
            : [
                Tables\Columns\TextColumn::make('offre.title')
                    ->label(__('admin.application_column_offer'))
                    ->placeholder(__('Open application'))
                    ->searchable()
                    ->weight(FontWeight::Medium)
                    ->limit(48)
                    ->tooltip(fn (ApplicationProgress $record): ?string => $record->offre?->title),
            ];

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
                ...$contextColumns,
                Split::make([
                    Tables\Columns\TextColumn::make('status')
                        ->label(__('Status'))
                        ->badge()
                        ->formatStateUsing(fn ($state) => match ($state) {
                            'pending' => __('Pending'),
                            'in_progress' => __('In Progress'),
                            'validated' => __('Validated'),
                            'rejected' => __('Rejected'),
                            'cancelled' => __('Cancelled'),
                            default => (string) $state,
                        })
                        ->color(fn ($state) => match ($state) {
                            'validated' => 'success',
                            'rejected' => 'danger',
                            'cancelled' => 'gray',
                            'in_progress' => 'info',
                            default => 'warning',
                        }),
                    Tables\Columns\TextColumn::make('current_level')
                        ->label(__('admin.application_column_level_short'))
                        ->badge()
                        ->color('gray')
                        ->alignEnd(),
                ]),
                Tables\Columns\TextColumn::make('level_status')
                    ->label(__('admin.application_column_level_status'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'in_progress' => __('admin.level_status_in_progress'),
                        'awaiting_approval' => __('admin.level_status_awaiting_approval'),
                        'approved' => __('admin.level_status_approved'),
                        'rejected' => __('admin.level_status_rejected'),
                        default => $state ? (string) $state : '—',
                    })
                    ->color(fn ($state) => match ($state) {
                        'awaiting_approval' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->size(TextColumnSize::ExtraSmall),
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
                    'pending' => __('Pending'),
                    'in_progress' => __('In Progress'),
                    'validated' => __('Validated'),
                    'rejected' => __('Rejected'),
                ]),
            Tables\Filters\SelectFilter::make('offre_id')
                ->label(__('nav.job_offer'))
                ->options(Offre::pluck('title', 'id')),
            Tables\Filters\SelectFilter::make('current_level')
                ->label(__('Level'))
                ->options(fn (): array => collect(range(1, 20))
                    ->mapWithKeys(fn (int $n) => [$n => (string) $n])
                    ->all()),
            Tables\Filters\SelectFilter::make('level_status')
                ->label(__('admin.application_column_level_status'))
                ->options([
                    'in_progress' => __('admin.level_status_in_progress'),
                    'awaiting_approval' => __('admin.level_status_awaiting_approval'),
                    'approved' => __('admin.level_status_approved'),
                    'rejected' => __('admin.level_status_rejected'),
                ]),
        ];
    }

    /**
     * @return array<int, Tables\Actions\Action|ActionGroup>
     */
    private static function applicationProgressTableActions(): array
    {
        return [
            Tables\Actions\EditAction::make()
                ->label(__('Edit'))
                ->iconButton(),
            ActionGroup::make([
                Tables\Actions\Action::make('assign_test_free')
                    ->label(__('admin.free_application_assign_test'))
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('primary')
                    ->form([
                        Forms\Components\Select::make('test_id')
                            ->label(__('admin.application_associated_test'))
                            ->options(fn (): array => Test::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->action(function (ApplicationProgress $record, array $data): void {
                        FreeApplicationWorkflow::assignTest($record, (int) $data['test_id']);
                    })
                    ->visible(fn (ApplicationProgress $record): bool => $record->isAwaitingTestAssignment()
                        || ($record->isFreeApplication()
                            && $record->status === 'in_progress'
                            && $record->level_status === 'awaiting_approval')),
                Tables\Actions\Action::make('validate_profile_free')
                    ->label(__('admin.free_application_validate_profile'))
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.free_application_validate_profile_heading'))
                    ->modalDescription(__('admin.free_application_validate_profile_description'))
                    ->action(function (ApplicationProgress $record): void {
                        FreeApplicationWorkflow::validateProfile($record);
                    })
                    ->visible(fn (ApplicationProgress $record): bool => $record->isFreeApplication()
                        && $record->status === 'in_progress'
                        && $record->level_status === 'awaiting_approval'),
                Tables\Actions\Action::make('valider_niveau')
                    ->label(__('admin.application_action_validate_level'))
                    ->icon('heroicon-o-arrow-up-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.application_action_validate_level_heading'))
                    ->modalDescription(__('admin.application_action_validate_level_description'))
                    ->action(function (ApplicationProgress $record) {
                        static::advanceToNextLevel($record);
                    })
                    ->visible(fn (ApplicationProgress $record) => $record->status === 'in_progress' && ! $record->isFreeApplication()),
                Tables\Actions\Action::make('valider_finale')
                    ->label(__('admin.application_action_validate_final'))
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.application_action_validate_final_heading'))
                    ->action(function (ApplicationProgress $record) {
                        if ($record->isFreeApplication()) {
                            FreeApplicationWorkflow::validateProfile($record);

                            return;
                        }

                        $record->update(['status' => 'validated']);
                        CandidateNotification::create([
                            'user_id' => $record->candidate->user_id,
                            'type' => 'validated',
                            'title' => __('admin.candidate_notif_validated_title'),
                            'message' => $record->offre
                                ? __('admin.candidate_notif_validated_body_with_offer', ['offer' => $record->offre->title])
                                : __('admin.candidate_notif_validated_body_open'),
                            'offre_id' => $record->offre_id,
                        ]);
                        Notification::make()
                            ->title(__('admin.application_toast_application_validated'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn (ApplicationProgress $record) => $record->status === 'in_progress' && ! $record->isFreeApplication()),
                Tables\Actions\Action::make('rejeter')
                    ->label(__('admin.application_action_reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.application_action_reject_heading'))
                    ->action(function (ApplicationProgress $record) {
                        $record->update(['status' => 'rejected']);
                        CandidateNotification::create([
                            'user_id' => $record->candidate->user_id,
                            'type' => 'rejected',
                            'title' => __('admin.candidate_notif_rejected_title'),
                            'message' => $record->offre
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
                        static::publishScoreForRecord($record);
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
            'index' => Pages\BrowseApplicationOffers::route('/'),
            'free' => Pages\ListFreeApplications::route('/free'),
            'by_offer' => Pages\ListApplicationProgress::route('/offre/{offre}'),
            'create' => Pages\CreateApplicationProgress::route('/create'),
            'review_level' => Pages\ReviewApplicationLevel::route('/{record}/review/{level}'),
            'edit' => Pages\EditApplicationProgress::route('/{record}/edit'),
        ];
    }

    public static function advanceToNextLevel(ApplicationProgress $record, bool $notifyCandidate = true, bool $notifyAdmin = true): void
    {
        $record->refresh();
        $oldLevel = $record->current_level;
        $newLevel = $oldLevel + 1;
        $payload = [
            'current_level' => $newLevel,
            'status' => 'in_progress',
            'level_status' => 'in_progress',
            'score_published' => false,
            'test_session_expires_at' => null,
        ];
        $nextTestId = $record->offre?->testIdForLevel($newLevel + 1);
        if ($nextTestId !== null) {
            $payload['test_id'] = $nextTestId;
        }
        $record->update($payload);
        $record->loadMissing('candidate.user', 'offre');

        if ($notifyCandidate) {
            CandidateNotification::create([
                'user_id' => $record->candidate->user_id,
                'type' => 'info',
                'title' => __('admin.candidate_notif_level_validated_title'),
                'message' => __('admin.candidate_notif_level_validated_body', [
                    'old' => (string) $oldLevel,
                    'new' => (string) $newLevel,
                ]),
                'offre_id' => $record->offre_id,
            ]);
        }

        if ($notifyAdmin) {
            Notification::make()
                ->title(__('admin.application_toast_level_advanced', [
                    'old' => (string) $oldLevel,
                    'new' => (string) $newLevel,
                ]))
                ->success()
                ->send();
        }
    }

    public static function recalculateScoresForResponse(ApplicationProgress $application, int $responseLevel): void
    {
        $response = Response::query()
            ->where('application_id', $application->id)
            ->where('level', $responseLevel)
            ->first();

        if (! $response) {
            return;
        }

        app(\App\Services\TestScoringService::class)->evaluateResponse($response, $application->fresh());
    }

    public static function publishScoreForRecord(ApplicationProgress $record, ?int $responseLevel = null): void
    {
        $record->loadMissing('candidate.user', 'offre');

        $level = $responseLevel ?? (int) ($record->responses()->max('level') ?? $record->current_level);
        static::recalculateScoresForResponse($record, $level);
        $record->refresh();

        $record->update(['score_published' => true]);
        CandidateNotification::create([
            'user_id' => $record->candidate->user_id,
            'type' => 'info',
            'title' => __('admin.candidate_notif_score_title'),
            'message' => __('admin.candidate_notif_score_body', ['score' => (string) $record->main_score]),
            'offre_id' => $record->offre_id,
        ]);
        Notification::make()
            ->title(__('admin.application_toast_score_published'))
            ->success()
            ->send();
    }

    public static function getRecordSubNavigation(FilamentResourcePage $page): array
    {
        if (! method_exists($page, 'getRecord')) {
            return [];
        }

        $record = $page->getRecord();
        if (! $record instanceof ApplicationProgress) {
            return [];
        }

        if ($record->isFreeApplication()) {
            $maxLevel = min(20, max(
                1,
                (int) ($record->responses()->max('level') ?? 0),
                (int) $record->current_level
            ));
        } else {
            $maxFromOffer = (int) ($record->offre?->levels_count ?? 1);
            $maxFromResponses = (int) ($record->responses()->max('level') ?? 1);
            $maxLevel = min(20, max(1, $maxFromOffer, $maxFromResponses));
        }

        $items = [
            NavigationItem::make('application-details')
                ->label(__('admin.application_subnav_details'))
                ->icon('heroicon-o-adjustments-horizontal')
                ->url(static::getUrl('edit', ['record' => $record]))
                ->isActiveWhen(fn (): bool => $page instanceof Pages\EditApplicationProgress),
        ];

        $responseLevels = $record->responses()->pluck('level')->map(fn ($l) => (int) $l)->all();

        for ($level = 1; $level <= $maxLevel; $level++) {
            $lv = $level;
            $hasResponse = in_array($lv, $responseLevels, true);
            $label = $lv === 1 && ! $hasResponse
                ? __('admin.application_review_nav_level_1')
                : ($hasResponse
                    ? __('admin.application_review_nav_level_test', ['n' => (string) $lv])
                    : __('admin.application_review_nav_level_n', ['n' => $lv]));

            $items[] = NavigationItem::make('review-'.$lv)
                ->label($label)
                ->icon('heroicon-o-clipboard-document-check')
                ->url(static::getUrl('review_level', ['record' => $record, 'level' => $lv]))
                ->isActiveWhen(function () use ($page, $lv): bool {
                    return $page instanceof Pages\ReviewApplicationLevel
                        && $page->reviewLevel === $lv;
                });
        }

        return $items;
    }
}
