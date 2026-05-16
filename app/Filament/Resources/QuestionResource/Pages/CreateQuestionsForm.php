<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Concerns\HasBackHeaderAction;
use App\Filament\Resources\QuestionResource;
use App\Models\Block;
use App\Models\Group;
use App\Models\Question;
use App\Support\QuestionFormOptions;
use App\Services\TranslationService;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Str;

/**
 * Create several questions in one flow (Google Forms–style cards).
 */
class CreateQuestionsForm extends Page implements HasForms
{
    use HasBackHeaderAction;
    use InteractsWithFormActions;
    use InteractsWithForms;

    protected static string $resource = QuestionResource::class;

    protected static string $view = 'filament.resources.question-resource.pages.create-questions-form';

    public ?array $data = [];

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    public function getFormStatePath(): ?string
    {
        return 'data';
    }

    public function getTitle(): string
    {
        return __('question_form.create_title');
    }

    public function mount(): void
    {
        abort_unless(static::getResource()::canCreate(), 403);

        $this->form->fill([
            'block_id' => null,
            'group_id' => null,
            'items' => [$this->emptyQuestionRow()],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyQuestionRow(): array
    {
        return [
            'question_fr' => '',
            'question_en' => '',
            'question_ar' => '',
            'component' => 'radio',
            'possible_answers' => [],
            'level' => 1,
            'classification' => 'primary',
            'obligatory' => true,
            'scorable' => true,
            'auto_evaluation' => true,
            'correct_answer' => null,
            'correct_answers' => [],
            'correct_text_reference' => null,
            'max_note' => 1,
            'second_ratio' => 0,
            'user_note' => null,
            'note_rule' => null,
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.association'))
                    ->schema([
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
                    ])
                    ->columns(2),
                Forms\Components\Repeater::make('items')
                    ->label(__('test_builder.questions'))
                    ->addActionLabel(__('test_builder.add_question'))
                    ->collapsible()
                    ->itemLabel(fn (array $state): string => Str::limit($state['question_fr'] ?? __('test_builder.untitled'), 70))
                    ->schema([
                        Forms\Components\Textarea::make('question_fr')
                            ->label(__('test_builder.question_prompt'))
                            ->rows(3)
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\Select::make('component')
                                ->label(__('test_builder.answer_kind'))
                                ->options([
                                    'radio' => __('test_builder.kind_qcu'),
                                    'checkbox' => __('test_builder.kind_qcm'),
                                    'list' => __('test_builder.kind_dropdown'),
                                    'text' => __('test_builder.kind_paragraph'),
                                    'date' => __('test_builder.kind_date'),
                                    'photo' => __('test_builder.kind_file'),
                                ])
                                ->required()
                                ->live(),
                            Forms\Components\TextInput::make('level')
                                ->label(__('Level'))
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->required(),
                            Forms\Components\Select::make('classification')
                                ->label(__('admin.classification'))
                                ->options([
                                    'primary' => __('admin.primary'),
                                    'secondary' => __('admin.secondary_class'),
                                ])
                                ->default('primary')
                                ->required(),
                        ]),
                        Forms\Components\TagsInput::make('possible_answers')
                            ->label(__('test_builder.options'))
                            ->placeholder(__('test_builder.option_placeholder'))
                            ->helperText(fn (Get $get): string => QuestionFormOptions::isMultipleChoiceComponent($get('component'))
                                ? __('test_builder.options_hint_qcm')
                                : __('test_builder.options_hint'))
                            ->visible(fn (Get $get): bool => QuestionFormOptions::isMcqComponent($get('component')))
                            ->live(onBlur: true),
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\Toggle::make('obligatory')
                                ->label(__('admin.mandatory'))
                                ->default(true),
                            Forms\Components\Toggle::make('scorable')
                                ->label(__('admin.scored'))
                                ->default(true),
                            Forms\Components\Toggle::make('auto_evaluation')
                                ->label(__('admin.auto_correction'))
                                ->default(true)
                                ->live(),
                        ]),
                        Forms\Components\Select::make('correct_answer')
                            ->label(__('admin.correct_answer_select_qcu'))
                            ->options(fn (Get $get): array => collect($get('possible_answers') ?? [])->mapWithKeys(fn ($a) => [$a => $a])->all())
                            ->searchable()
                            ->visible(fn (Get $get): bool => (bool) $get('auto_evaluation')
                                && QuestionFormOptions::isSingleChoiceComponent($get('component'))),

                        Forms\Components\CheckboxList::make('correct_answers')
                            ->label(__('admin.correct_answers_select_qcm'))
                            ->options(fn (Get $get): array => collect($get('possible_answers') ?? [])->mapWithKeys(fn ($a) => [$a => $a])->all())
                            ->columns(2)
                            ->visible(fn (Get $get): bool => (bool) $get('auto_evaluation')
                                && QuestionFormOptions::isMultipleChoiceComponent($get('component'))),
                        Forms\Components\TextInput::make('correct_text_reference')
                            ->label(__('test_builder.reference_answer'))
                            ->visible(fn (Get $get) => (bool) $get('auto_evaluation')
                                && $get('component') === 'text'),
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('max_note')
                                ->label(__('admin.max_score'))
                                ->numeric()
                                ->default(1),
                            Forms\Components\TextInput::make('second_ratio')
                                ->label(__('admin.second_ratio'))
                                ->numeric()
                                ->default(0),
                        ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $items = $state['items'] ?? [];

        $association = [
            'block_id' => $state['block_id'] ?? null,
            'group_id' => $state['group_id'] ?? null,
            'offre_id' => null,
        ];

        $created = 0;

        foreach ($items as $item) {
            if (trim((string) ($item['question_fr'] ?? '')) === '') {
                continue;
            }

            $payload = $this->normalizePayload(array_merge($item, $association));
            $question = Question::query()->create($payload);
            $this->fillTranslationsIfEmpty($question);
            $question->refresh();
            $question->syncAnswerRowsFromMcqOptions();
            $created++;
        }

        if ($created === 0) {
            Notification::make()
                ->title(__('question_form.nothing_saved'))
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title(__('question_form.saved_count', ['count' => $created]))
            ->success()
            ->send();

        $this->redirect(QuestionResource::getUrl('index'));
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function normalizePayload(array $item): array
    {
        $component = $item['component'] ?? 'radio';

        $correctMcq = $item['correct_answer'] ?? null;
        $correctMcqMultiple = $item['correct_answers'] ?? null;
        $correctText = $item['correct_text_reference'] ?? null;

        $correctAnswer = null;
        if (! empty($item['auto_evaluation'])) {
            if (QuestionFormOptions::isMcqComponent($component)) {
                $correctAnswer = Question::encodeCorrectAnswerForStorage(
                    $component,
                    $correctMcq,
                    $correctMcqMultiple,
                );
            } elseif ($component === 'text') {
                $correctAnswer = $correctText;
            }
        }

        $possible = $item['possible_answers'] ?? null;
        if (! is_array($possible)) {
            $possible = [];
        }

        return [
            'block_id' => $item['block_id'] ?? null,
            'group_id' => $item['group_id'] ?? null,
            'offre_id' => null,
            'question_fr' => $item['question_fr'] ?? '',
            'question_en' => $item['question_en'] ?? '',
            'question_ar' => $item['question_ar'] ?? '',
            'component' => $component,
            'level' => (int) ($item['level'] ?? 1),
            'obligatory' => (bool) ($item['obligatory'] ?? true),
            'scorable' => (bool) ($item['scorable'] ?? true),
            'auto_evaluation' => (bool) ($item['auto_evaluation'] ?? false),
            'correct_answer' => $correctAnswer,
            'classification' => $item['classification'] ?? 'primary',
            'max_note' => (float) ($item['max_note'] ?? 0),
            'second_ratio' => (float) ($item['second_ratio'] ?? 0),
            'user_note' => $item['user_note'] ?? null,
            'note_rule' => $item['note_rule'] ?? null,
            'possible_answers' => QuestionFormOptions::isMcqComponent($component) ? $possible : null,
        ];
    }

    private function fillTranslationsIfEmpty(Question $question): void
    {
        $fr = trim((string) $question->question_fr);
        if ($fr === '') {
            return;
        }

        $needEn = trim((string) $question->question_en) === '';
        $needAr = trim((string) $question->question_ar) === '';
        if (! $needEn && ! $needAr) {
            return;
        }

        $translations = TranslationService::translateToAll($fr);
        if ($needEn) {
            $question->question_en = $translations['en'];
        }
        if ($needAr) {
            $question->question_ar = $translations['ar'];
        }
        $question->save();
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label(__('question_form.save_and_list'))
                ->submit('save')
                ->color('primary'),
        ];
    }

    protected function resolveBackUrl(): string
    {
        return QuestionResource::getUrl('index');
    }

    protected function getBackNavigationLabel(): string
    {
        return __('question_form.back_to_list');
    }
}
