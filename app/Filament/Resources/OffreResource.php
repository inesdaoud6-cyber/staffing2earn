<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OffreResource\Pages;
use App\Filament\Support\TableLayoutConfigurator;
use App\Models\CandidateNotification;
use App\Models\Offre;
use App\Models\Test;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;

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

            Forms\Components\Section::make('Parcours par niveau')
                ->description('Le niveau 1 est toujours le CV. Indiquez le nombre total de niveaux, puis choisissez un test pour chaque niveau à partir du niveau 2.')
                ->schema(function (Get $get): array {
                    $fields = [
                        Forms\Components\TextInput::make('levels_count')
                            ->label('Nombre de niveaux (total)')
                            ->numeric()
                            ->minValue(2)
                            ->maxValue(20)
                            ->default(2)
                            ->required()
                            ->live(debounce: 400)
                            ->afterStateUpdated(function (Set $set, $state, Get $get): void {
                                $n = max(2, min(20, (int) $state));
                                $set('level_test_ids', self::paddedLevelTestIds($get('level_test_ids'), $n));
                            })
                            ->helperText('Exemple : 3 = CV + 2 tests.'),
                        Forms\Components\Placeholder::make('niveau_1_cv')
                            ->label('Niveau 1')
                            ->content(new HtmlString(
                                '<p class="text-sm text-gray-600 dark:text-gray-400">'
                                . 'Envoi du CV — obligatoire pour toutes les offres. Aucun test à sélectionner.'
                                . '</p>'
                            )),
                    ];

                    $levelsCount = max(2, min(20, (int) ($get('levels_count') ?? 2)));

                    for ($level = 2; $level <= $levelsCount; $level++) {
                        $idx = $level - 2;
                        $fields[] = Forms\Components\Select::make('level_test_ids.' . $idx)
                            ->label('Niveau ' . $level . ' — test')
                            ->options(fn () => Test::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required();
                    }

                    return $fields;
                })
                ->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return self::configureTable($table);
    }

    public static function configureTable(Table $table, string $layout = TableLayoutConfigurator::LAYOUT_LIST): Table
    {
        return TableLayoutConfigurator::apply(
            $table,
            $layout,
            self::offreListColumns('applicationProgresses_count'),
            self::offreCardColumns('applicationProgresses_count'),
        )
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

    public static function configureOffersHubTable(Table $table, string $layout = TableLayoutConfigurator::LAYOUT_LIST): Table
    {
        return TableLayoutConfigurator::apply(
            $table,
            $layout,
            self::offreListColumns('applications_count'),
            self::offreCardColumns('applications_count'),
        );
    }

    public static function configureCandidateOffersHubTable(Table $table, string $layout = TableLayoutConfigurator::LAYOUT_LIST): Table
    {
        return TableLayoutConfigurator::apply(
            $table,
            $layout,
            [
                Tables\Columns\TextColumn::make('title')
                    ->label(__('nav.job_offer'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('applications_count')
                    ->label(__('nav.candidates'))
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deadline')
                    ->label(__('admin.deadline'))
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->sortable(),
            ],
            [
                TableLayoutConfigurator::cardStack([
                    Tables\Columns\TextColumn::make('title')
                        ->label(__('nav.job_offer'))
                        ->searchable()
                        ->weight('bold'),
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\TextColumn::make('applications_count')
                            ->label(__('nav.candidates'))
                            ->badge()
                            ->color('info'),
                        Tables\Columns\TextColumn::make('deadline')
                            ->label(__('admin.deadline'))
                            ->date('d/m/Y')
                            ->placeholder('—')
                            ->color('gray')
                            ->size('sm'),
                    ]),
                ]),
            ],
        );
    }

    private static function offreApplicationsCountColumn(string $applicationsCountColumn): Tables\Columns\TextColumn
    {
        $column = Tables\Columns\TextColumn::make($applicationsCountColumn)
            ->label(__('admin.applications'));

        if ($applicationsCountColumn === 'applicationProgresses_count') {
            $column->counts('applicationProgresses');
        }

        return $column;
    }

    /**
     * @return array<int, Tables\Columns\Column>
     */
    private static function offreListColumns(string $applicationsCountColumn): array
    {
        return [
            Tables\Columns\TextColumn::make('title')
                ->label(__('admin.title'))
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('domain')
                ->label(__('admin.domain'))
                ->toggleable(),
            Tables\Columns\TextColumn::make('contract_type')
                ->label(__('admin.contract_type'))
                ->badge()
                ->toggleable(),
            Tables\Columns\IconColumn::make('is_published')
                ->label(__('admin.published'))
                ->boolean(),
            self::offreApplicationsCountColumn($applicationsCountColumn)
                ->alignEnd()
                ->sortable(),
            Tables\Columns\TextColumn::make('deadline')
                ->label(__('admin.deadline'))
                ->date('d/m/Y')
                ->placeholder('—')
                ->sortable(),
        ];
    }

    /**
     * @return array<int, Tables\Columns\Layout\Component>
     */
    private static function offreCardColumns(string $applicationsCountColumn): array
    {
        return [
            TableLayoutConfigurator::cardStack([
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
                    Tables\Columns\TextColumn::make('levels_count')
                        ->label('Niveaux')
                        ->badge()
                        ->color('gray'),
                    Tables\Columns\TextColumn::make('test.name')
                        ->label(__('admin.associated_test'))
                        ->badge()
                        ->color('success')
                        ->default(__('admin.none')),
                    self::offreApplicationsCountColumn($applicationsCountColumn)
                        ->badge()
                        ->color('warning'),
                ]),
                Tables\Columns\TextColumn::make('deadline')
                    ->label(__('admin.deadline'))
                    ->date('d/m/Y')
                    ->icon('heroicon-o-calendar')
                    ->color('gray')
                    ->size('sm'),
            ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normalizeLevelsFormData(array $data): array
    {
        $n = max(2, min(20, (int) ($data['levels_count'] ?? 2)));
        $data['levels_count'] = $n;

        $raw = $data['level_test_ids'] ?? [];
        if (! is_array($raw)) {
            $raw = [];
        }

        $ids = [];
        for ($i = 0; $i < $n - 1; $i++) {
            $v = $raw[$i] ?? null;
            $ids[] = $v !== null && $v !== '' ? (int) $v : null;
        }

        $data['level_test_ids'] = $ids;

        return $data;
    }

    /**
     * Ensure `level_test_ids` has exactly (levels_count - 1) entries (indices for tests after CV).
     *
     * @param  array<int|string, mixed>|null  $current
     * @return list<int|null>
     */
    public static function paddedLevelTestIds(?array $current, int $levelsCount): array
    {
        $n = max(2, min(20, $levelsCount));
        $need = $n - 1;
        $cur = is_array($current) ? array_values($current) : [];

        return array_pad(array_slice($cur, 0, $need), $need, null);
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