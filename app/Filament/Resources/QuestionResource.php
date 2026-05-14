<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Models\Block;
use App\Models\Group;
use App\Models\Offre;
use App\Models\Question;
use App\Services\TranslationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationGroup = 'Évaluations';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('nav.questions_management');
    }

    public static function getModelLabel(): string
    {
        return __('admin.question');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Questions');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('admin.question_content'))->schema([
                Forms\Components\Textarea::make('question_fr')
                    ->label(__('admin.question_fr'))
                    ->required()
                    ->live(debounce: 1000)
                    ->afterStateUpdated(function ($state, callable $set) {
                        if (! empty($state)) {
                            $translations = TranslationService::translateToAll($state);
                            $set('question_en', $translations['en']);
                            $set('question_ar', $translations['ar']);
                        }
                    })
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('question_en')
                    ->label(__('admin.question_en'))
                    ->disabled()->columnSpanFull(),
                Forms\Components\Textarea::make('question_ar')
                    ->label(__('admin.question_ar'))
                    ->disabled()->columnSpanFull(),
            ]),

            Forms\Components\Section::make(__('admin.association'))->schema([
                Forms\Components\Select::make('block_id')
                    ->label('Block')
                    ->options(Block::query()->orderBy('order')->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->nullable()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('group_id', null)),
                Forms\Components\Select::make('group_id')
                    ->label(__('admin.group'))
                    ->options(function (Get $get): array {
                        $blockId = $get('block_id');
                        if (! filled($blockId)) {
                            return [];
                        }

                        return Group::query()
                            ->where('block_id', $blockId)
                            ->orderBy('order')
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all();
                    })
                    ->searchable()
                    ->nullable()
                    ->disabled(fn (Get $get): bool => ! filled($get('block_id'))),
                Forms\Components\Select::make('offre_id')
                    ->label(__('admin.associated_offer'))
                    ->options(Offre::where('is_published', true)->pluck('title', 'id'))
                    ->searchable()
                    ->nullable()
                    ->placeholder(__('admin.no_offer')),
            ])->columns(3),

            Forms\Components\Section::make(__('admin.configuration'))->schema([
                Forms\Components\Select::make('component')
                    ->label(__('admin.answer_type'))
                    ->options([
                        'radio' => __('admin.radio'),
                        'list' => __('admin.list'),
                        'text' => __('admin.free_text'),
                        'date' => __('admin.date'),
                        'photo' => __('admin.photo'),
                    ])
                    ->required()
                    ->live(),

                Forms\Components\TagsInput::make('possible_answers')
                    ->label(__('admin.possible_answers'))
                    ->placeholder(__('admin.add_answer'))
                    ->visible(fn ($get) => in_array($get('component'), ['radio', 'list']))
                    ->live()
                    ->helperText(__('admin.press_enter')),

                Forms\Components\TextInput::make('level')
                    ->label(__('Level'))
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->required()
                    ->helperText(__('admin.level_hint')),

                Forms\Components\Select::make('classification')
                    ->label(__('admin.classification'))
                    ->options([
                        'primary' => __('admin.primary'),
                        'secondary' => __('admin.secondary_class'),
                    ])
                    ->required(),

                Forms\Components\Toggle::make('obligatory')->label(__('admin.mandatory')),
                Forms\Components\Toggle::make('scorable')->label(__('admin.scored')),

                Forms\Components\Toggle::make('auto_evaluation')
                    ->label(__('admin.auto_correction'))
                    ->live(),

                Forms\Components\Select::make('correct_answer')
                    ->label(__('admin.correct_answer_select'))
                    ->options(fn ($get) => collect($get('possible_answers') ?? [])->mapWithKeys(fn ($a) => [$a => $a]))
                    ->visible(fn ($get) => (bool) $get('auto_evaluation') && in_array($get('component'), ['radio', 'list']))
                    ->searchable()
                    ->nullable(),

                Forms\Components\TextInput::make('correct_answer')
                    ->label(__('admin.correct_answer_text'))
                    ->visible(fn ($get) => (bool) $get('auto_evaluation') && $get('component') === 'text')
                    ->nullable(),

                Forms\Components\TextInput::make('max_note')->label(__('admin.max_score'))->numeric()->default(0),
                Forms\Components\TextInput::make('second_ratio')->label(__('admin.second_ratio'))->numeric()->default(0),
                Forms\Components\Textarea::make('user_note')->label(__('admin.candidate_note')),
                Forms\Components\Textarea::make('note_rule')->label(__('admin.scoring_rule')),
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
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('question_fr')
                            ->label(__('admin.question'))
                            ->limit(80)
                            ->searchable()
                            ->weight('bold')
                            ->size('sm'),
                    ]),
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\TextColumn::make('component')
                            ->label(__('admin.type'))
                            ->badge()
                            ->color('info'),
                        Tables\Columns\TextColumn::make('level')
                            ->label(__('Level'))
                            ->badge()
                            ->color('warning')
                            ->prefix('Niv. '),
                        Tables\Columns\TextColumn::make('classification')
                            ->label(__('admin.classification'))
                            ->badge()
                            ->color('gray'),
                    ]),
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\IconColumn::make('scorable')
                            ->label(__('admin.scored'))
                            ->boolean(),
                        Tables\Columns\IconColumn::make('auto_evaluation')
                            ->label('Auto')
                            ->boolean(),
                        Tables\Columns\TextColumn::make('block.name')
                            ->label('Block')
                            ->badge()
                            ->color('gray'),
                    ]),
                    Tables\Columns\TextColumn::make('tests.name')
                        ->label(__('admin.tests'))
                        ->badge()
                        ->color('success')
                        ->separator(',')
                        ->placeholder(__('admin.no_test')),
                ])->space(2),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label(__('Edit')),
                Tables\Actions\DeleteAction::make()->label(__('admin.delete')),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestionsForm::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}
