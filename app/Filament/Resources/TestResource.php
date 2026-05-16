<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestResource\Pages;
use App\Models\Block;
use App\Models\Group;
use App\Models\Question;
use App\Models\Test;
use App\Support\QuestionFormOptions;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class TestResource extends Resource
{
    /** Value for "questions without a group" in group selects. */
    public const GROUP_DIRECT_VALUE = '__direct__';

    protected static ?string $model = Test::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Évaluations';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return 'Gestion des tests';
    }

    public static function getModelLabel(): string
    {
        return 'Test';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Tests';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Propriétés du test')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom du test')
                    ->required()
                    ->placeholder('Ex : Test PHP Développeur Senior'),
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->placeholder('Compétences évaluées, contexte, etc.'),
                Forms\Components\TextInput::make('eligibility_threshold')
                    ->label('Seuil d’admissibilité (%)')
                    ->numeric()
                    ->default(50),
                Forms\Components\TextInput::make('talent_threshold')
                    ->label('Seuil talents (%)')
                    ->numeric()
                    ->default(80),
                Forms\Components\Toggle::make('whole_test_timer_enabled')
                    ->label('Activer une limite de temps sur tout le test')
                    ->default(false)
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state): void {
                        if (! $state) {
                            $set('whole_test_timer_minutes', null);
                            $set('_whole_test_time_display', '1:00');
                        }
                    }),
                Forms\Components\TextInput::make('_whole_test_time_display')
                    ->label('Temps total')
                    ->placeholder('1:00')
                    ->default('1:00')
                    ->maxLength(8)
                    ->markAsRequired(false)
                    ->extraInputAttributes(['class' => 'fi-input tabular-nums max-w-xs'])
                    ->visible(fn (Get $get): bool => (bool) $get('whole_test_timer_enabled')),
            ])->columns(2),
            Forms\Components\Section::make(__('test.blocks_section'))->schema([
                Forms\Components\Repeater::make('block_assignments')
                    ->label(__('test.blocks_repeater_label'))
                    ->helperText(__('test.blocks_section_help'))
                    ->schema([
                        Forms\Components\Grid::make(['default' => 2, 'lg' => 4])->schema([
                            Forms\Components\Select::make('block_id')
                                ->label(__('test.filter-block'))
                                ->options(
                                    fn (): array => Block::query()
                                        ->orderBy('order')
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->all()
                                )
                                ->searchable(false)
                                ->native(true)
                                ->live(debounce: 0)
                                ->afterStateUpdated(function (Set $set): void {
                                    $set('group_id', null);
                                    $set('component_filter', null);
                                    $set('level_filter', null);
                                    $set('question_ids', []);
                                }),
                            Forms\Components\Select::make('group_id')
                                ->label(__('test.filter-group'))
                                ->placeholder(__('test.choose-group'))
                                ->options(function (Get $get): array {
                                    $blockId = $get('block_id');
                                    if (! $blockId) {
                                        return [];
                                    }

                                    $opts = [
                                        self::GROUP_DIRECT_VALUE => __('test.scope-direct-on-block'),
                                    ];

                                    $groups = Group::query()
                                        ->where('block_id', $blockId)
                                        ->orderBy('order')
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->all();

                                    return $opts + $groups;
                                })
                                ->searchable(false)
                                ->native(true)
                                ->live(debounce: 0)
                                ->disabled(fn (Get $get): bool => ! filled($get('block_id')))
                                ->afterStateUpdated(function (Set $set): void {
                                    $set('component_filter', null);
                                    $set('level_filter', null);
                                    $set('question_ids', []);
                                }),
                            Forms\Components\Select::make('component_filter')
                                ->label(__('admin.filter_type'))
                                ->placeholder(__('admin.filter_all_types'))
                                ->options(fn (): array => QuestionFormOptions::componentOptions())
                                ->searchable(false)
                                ->native(true)
                                ->live(debounce: 0)
                                ->disabled(fn (Get $get): bool => ! filled($get('block_id')) || ! filled($get('group_id')))
                                ->afterStateUpdated(fn (Set $set) => $set('question_ids', [])),
                            Forms\Components\Select::make('level_filter')
                                ->label(__('admin.filter_level'))
                                ->placeholder(__('admin.filter_all_levels'))
                                ->options(fn (Get $get): array => static::assignmentLevelFilterOptions($get))
                                ->searchable(false)
                                ->native(true)
                                ->live(debounce: 0)
                                ->disabled(fn (Get $get): bool => ! filled($get('block_id')) || ! filled($get('group_id')))
                                ->afterStateUpdated(fn (Set $set) => $set('question_ids', [])),
                        ]),
                        Forms\Components\Placeholder::make('pick_group_first')
                            ->label('')
                            ->content(fn (): HtmlString => new HtmlString(
                                '<p class="text-sm text-gray-500 dark:text-gray-400">'
                                .e(__('test.pick-group-for-questions'))
                                .'</p>'
                            ))
                            ->visible(fn (Get $get): bool => filled($get('block_id'))
                                && ! filled($get('group_id')))
                            ->columnSpanFull(),
                        Actions::make([
                            Action::make('select_all_questions')
                                ->label(__('filament-forms::components.checkbox_list.actions.select_all.label'))
                                ->link()
                                ->action(fn (Set $set, Get $get) => $set(
                                    'question_ids',
                                    static::assignmentQuestionIdsForSelection($get)
                                )),
                            Action::make('deselect_all_questions')
                                ->label(__('filament-forms::components.checkbox_list.actions.deselect_all.label'))
                                ->link()
                                ->color('gray')
                                ->action(fn (Set $set) => $set('question_ids', [])),
                        ])
                            ->visible(fn (Get $get): bool => filled($get('block_id')) && filled($get('group_id')))
                            ->columnSpanFull(),
                        Forms\Components\CheckboxList::make('question_ids')
                            ->label(__('test.selectionner-questions'))
                            ->options(fn (Get $get): array => static::assignmentQuestionOptions($get))
                            ->visible(fn (Get $get): bool => filled($get('block_id')) && filled($get('group_id')))
                            ->columns(1)
                            ->gridDirection('row')
                            ->default([])
                            ->helperText(__('test.test-form-questions-hint')),
                    ])
                    ->addActionLabel(__('test.add-block'))
                    ->defaultItems(0)
                    ->reorderable(false)
                    ->itemLabel(function (array $state): ?string {
                        if (empty($state['block_id'])) {
                            return null;
                        }

                        $block = Block::query()->find($state['block_id']);
                        if (! $block) {
                            return null;
                        }

                        $g = $state['group_id'] ?? null;
                        if ($g === null || $g === '') {
                            return $block->name;
                        }
                        if ($g === self::GROUP_DIRECT_VALUE) {
                            return $block->name.' — '.__('test.scope-direct-on-block');
                        }

                        $groupName = Group::query()->find($g)?->name;

                        return $block->name.' — '.($groupName ?? '?');
                    }),
            ]),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public static function assignmentQuestionOptions(Get $get): array
    {
        $query = static::assignmentQuestionsQuery($get);
        if (! $query) {
            return [];
        }

        return $query
            ->orderBy('level')
            ->orderBy('id')
            ->get()
            ->mapWithKeys(fn (Question $question) => [
                (string) $question->id => Str::limit((string) $question->question_fr, 90),
            ])
            ->all();
    }

    /**
     * @return list<string>
     */
    public static function assignmentQuestionIdsForSelection(Get $get): array
    {
        return array_keys(static::assignmentQuestionOptions($get));
    }

    /**
     * @return array<int|string, int|string>
     */
    public static function assignmentLevelFilterOptions(Get $get): array
    {
        $query = static::assignmentQuestionsQuery($get, applyComponentFilter: false, applyLevelFilter: false);
        if (! $query) {
            return [];
        }

        return $query
            ->clone()
            ->select('level')
            ->distinct()
            ->orderBy('level')
            ->pluck('level', 'level')
            ->all();
    }

    /**
     * Questions available for the current block / group / type / level filters in a repeater row.
     */
    public static function assignmentQuestionsQuery(
        Get $get,
        bool $applyComponentFilter = true,
        bool $applyLevelFilter = true,
    ): ?Builder {
        $blockId = $get('block_id');
        $groupFilter = $get('group_id');
        if (! $blockId || $groupFilter === null || $groupFilter === '') {
            return null;
        }

        $query = Question::query()->where('block_id', $blockId);

        if ($groupFilter === self::GROUP_DIRECT_VALUE) {
            $query->whereNull('group_id');
        } else {
            $query->where('group_id', $groupFilter);
        }

        if ($applyComponentFilter && filled($get('component_filter'))) {
            $query->where('component', $get('component_filter'));
        }

        if ($applyLevelFilter && filled($get('level_filter'))) {
            $query->where('level', (int) $get('level_filter'));
        }

        return $query;
    }

    /**
     * Build repeater rows for the test edit form: one row per (block, group) that has questions,
     * plus one empty row per linked block that has no questions yet.
     *
     * @return list<array{block_id: int, group_id: int|string|null, question_ids: list<int>}>
     */
    public static function blockAssignmentsFormStateForTest(Test $test): array
    {
        $test->load([
            'blocks' => fn ($q) => $q->orderBy('order')->orderBy('name'),
            'questions' => fn ($q) => $q->orderBy('level')->orderBy('id'),
        ]);

        $rowsByKey = [];

        foreach ($test->questions as $question) {
            if (! $question->block_id) {
                continue;
            }

            $gKey = $question->group_id ? (string) $question->group_id : self::GROUP_DIRECT_VALUE;
            $rowKey = $question->block_id.'_'.$gKey;

            if (! isset($rowsByKey[$rowKey])) {
                $rowsByKey[$rowKey] = [
                    'block_id' => (int) $question->block_id,
                    'group_id' => $question->group_id ? (int) $question->group_id : self::GROUP_DIRECT_VALUE,
                    'question_ids' => [],
                ];
            }

            $rowsByKey[$rowKey]['question_ids'][] = (string) $question->id;
        }

        $rows = array_values($rowsByKey);

        foreach ($test->blocks as $block) {
            $hasRow = collect($rows)->contains(fn (array $r): bool => (int) ($r['block_id'] ?? 0) === (int) $block->id);
            if (! $hasRow) {
                $rows[] = [
                    'block_id' => (int) $block->id,
                    'group_id' => null,
                    'question_ids' => [],
                ];
            }
        }

        return $rows;
    }

    /**
     * Persist blocks and questions from the test form repeater.
     *
     * @param  array<int, array{block_id?: mixed, group_id?: mixed, question_ids?: mixed}>  $assignments
     */
    public static function syncTestFromBlockAssignments(Test $test, array $assignments): void
    {
        $blockIds = collect($assignments)
            ->pluck('block_id')
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $test->blocks()->sync($blockIds);

        $questionIds = collect($assignments)
            ->pluck('question_ids')
            ->flatten()
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $test->questions()->sync($questionIds);
    }

    /**
     * @deprecated Use {@see syncTestFromBlockAssignments()}
     * @param  array<int, array{block_id?: int|string|null}>  $assignments
     */
    public static function syncTestBlocksFromAssignments(Test $test, array $assignments): void
    {
        static::syncTestFromBlockAssignments($test, $assignments);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nom du test')->searchable(),
                Tables\Columns\TextColumn::make('questions_count')->label('Questions')->counts('questions')->badge()->color('info'),
                Tables\Columns\TextColumn::make('eligibility_threshold')->label('Seuil admissibilité')->suffix('%'),
                Tables\Columns\TextColumn::make('talent_threshold')->label('Seuil talents')->suffix('%'),
                Tables\Columns\TextColumn::make('created_at')->label('Date')->date('d/m/Y'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Modifier'),
                Tables\Actions\DeleteAction::make()->label('Supprimer'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTests::route('/'),
            'create' => Pages\CreateTest::route('/create'),
            'edit' => Pages\EditTest::route('/{record}/edit'),
            'view' => Pages\ViewTest::route('/{record}'),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function mergeWholeTestTimerFromHourMinuteFields(array $data): array
    {
        if (! empty($data['whole_test_timer_enabled'])) {
            $data['whole_test_timer_minutes'] = self::parseWholeTestTimeDisplayToMinutes(
                (string) ($data['_whole_test_time_display'] ?? '')
            );
        } else {
            $data['whole_test_timer_minutes'] = null;
        }

        unset($data['_whole_test_time_display']);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function splitWholeTestTimerIntoHourMinuteFields(array $data): array
    {
        $total = (int) ($data['whole_test_timer_minutes'] ?? 0);
        if ($total > 0) {
            $h = intdiv($total, 60);
            $m = $total % 60;
            $data['_whole_test_time_display'] = $h . ':' . str_pad((string) $m, 2, '0', STR_PAD_LEFT);
        } else {
            $data['_whole_test_time_display'] = '1:00';
        }

        return $data;
    }

    private static function parseWholeTestTimeDisplayToMinutes(string $raw): int
    {
        $raw = trim($raw);
        if ($raw === '') {
            return 60;
        }

        if (preg_match('/^(\d{1,3}):(\d{1,2})$/', $raw, $m)) {
            $hours = (int) $m[1];
            $mins = (int) $m[2];
            if ($mins > 59) {
                return 60;
            }

            return max(1, $hours * 60 + $mins);
        }

        return 60;
    }
}
