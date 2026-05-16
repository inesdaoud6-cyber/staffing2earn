<?php

namespace App\Filament\Resources\ApplicationProgressResource\Pages;

use App\Filament\Concerns\HasBackHeaderAction;
use App\Filament\Resources\ApplicationProgressResource;
use App\Models\ApplicationProgress;
use App\Models\CandidateNotification;
use App\Models\QuestionResponse;
use App\Models\Response;
use App\Models\Test;
use App\Services\FreeApplicationWorkflow;
use App\Services\TestScoringService;
use App\Support\ApplicationProgressStepMapper;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ReviewApplicationLevel extends Page implements HasForms
{
    use HasBackHeaderAction;
    use InteractsWithForms;
    use InteractsWithRecord;

    protected static string $resource = ApplicationProgressResource::class;

    protected static string $view = 'filament.resources.application-progress-resource.pages.review-application-level';

    protected static bool $shouldRegisterNavigation = false;

    public int $reviewLevel = 1;

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public function mount(int|string $record, string|int $level): void
    {
        $this->record = $this->resolveRecord($record);
        $requested = max(1, min(20, (int) $level));
        $this->reviewLevel = ApplicationProgressStepMapper::normalizeReviewPageStep(
            $this->record,
            $requested
        );

        abort_unless(static::getResource()::canEdit($this->record), 403);

        if ($this->showsTestSection() && $this->hasResponseForReviewLevel()) {
            $this->ensureReviewQuestionResponses();
        }

        $this->form->fill($this->showsTestSection() ? $this->buildTestReviewFormData() : []);
    }

    public function getTitle(): string|Htmlable
    {
        $name = $this->record->candidate?->user?->name
            ?? $this->record->candidate?->full_name
            ?? __('Application');

        if ($this->showsCvSection()) {
            return __('admin.application_review_page_title_cv', ['name' => $name]);
        }

        return __('admin.application_review_page_title_test', [
            'name' => $name,
            'level' => (string) ApplicationProgressStepMapper::testNumberFromOfferStep($this->reviewLevel),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.application_cv_section'))
                    ->description(__('admin.application_cv_section_hint'))
                    ->schema([
                        Forms\Components\Placeholder::make('cv_link')
                            ->label(__('admin.application_cv'))
                            ->content(fn (): HtmlString => $this->getCvPlaceholder()),
                    ])
                    ->columns(1)
                    ->visible(fn (): bool => $this->showsCvSection()),
                Forms\Components\Section::make(fn (): string => $this->hasResponseForReviewLevel()
                    ? __('admin.application_test_answers_section')
                    : __('admin.application_test_preview_section'))
                    ->description(fn (): string => $this->hasResponseForReviewLevel()
                        ? __('admin.application_test_answers_level_hint', [
                            'level' => (string) ApplicationProgressStepMapper::testNumberFromOfferStep($this->reviewLevel),
                        ])
                        : __('admin.application_test_preview_level_hint', [
                            'level' => (string) ApplicationProgressStepMapper::testNumberFromOfferStep($this->reviewLevel),
                        ]))
                    ->schema([
                        Forms\Components\Placeholder::make('no_test_for_step')
                            ->label('')
                            ->content(fn (): HtmlString => new HtmlString(
                                '<p class="text-sm text-gray-600 dark:text-gray-400">'
                                .e(__('admin.application_test_not_configured_for_step'))
                                .'</p>'
                            ))
                            ->visible(fn (): bool => $this->resolveTestForReviewStep() === null)
                            ->columnSpanFull(),
                        Forms\Components\Placeholder::make('test_preview_notice')
                            ->label('')
                            ->content(fn (): HtmlString => new HtmlString(
                                '<p class="text-sm text-amber-700 dark:text-amber-300">'
                                .e(__('admin.application_test_preview_notice'))
                                .'</p>'
                            ))
                            ->visible(fn (): bool => ! $this->hasResponseForReviewLevel()
                                && $this->resolveTestForReviewStep() !== null)
                            ->columnSpanFull(),
                        Forms\Components\Placeholder::make('test_score_summary')
                            ->label(__('admin.test_score'))
                            ->content(fn (): HtmlString => $this->getTestScoreSummaryHtml())
                            ->columnSpanFull()
                            ->visible(fn (): bool => $this->hasResponseForReviewLevel()),
                        Forms\Components\Repeater::make('level_review_items')
                            ->label('')
                            ->schema([
                                Forms\Components\Hidden::make('question_response_id'),
                                Forms\Components\Hidden::make('needs_manual'),
                                Forms\Components\TextInput::make('question_label')
                                    ->label(__('admin.application_review_question_label'))
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('answer_preview')
                                    ->label(__('admin.application_review_answer_label'))
                                    ->rows(2)
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('auto_score')
                                    ->label(__('admin.application_review_auto_score'))
                                    ->suffix(__('admin.application_review_score_points_suffix'))
                                    ->disabled()
                                    ->dehydrated(true),
                                Forms\Components\Hidden::make('question_max_points')
                                    ->dehydrated(true),
                                Forms\Components\TextInput::make('manual_score')
                                    ->label(__('admin.application_review_manual_score'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->placeholder(__('admin.application_review_manual_placeholder'))
                                    ->helperText(fn (Forms\Get $get): string => $get('needs_manual')
                                        ? __('admin.manual_score_open_hint', [
                                            'max' => $get('question_max_points') ?? '—',
                                        ])
                                        : __('admin.manual_score_override_hint', [
                                            'max' => $get('question_max_points') ?? '—',
                                        ])),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->visible(fn (): bool => $this->showsTestSection()),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function getFormStatePath(): ?string
    {
        return 'data';
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveResponseForReviewLevel(): ?Response
    {
        return ApplicationProgressStepMapper::resolveResponseForOfferStep(
            $this->record,
            $this->reviewLevel
        );
    }

    private function responseLevelForScoring(): int
    {
        return ApplicationProgressStepMapper::responseLevelFromOfferStep($this->reviewLevel);
    }

    private function resolveTestForReviewStep(): ?Test
    {
        return ApplicationProgressStepMapper::resolveTestForOfferStep(
            $this->record,
            $this->reviewLevel
        );
    }

    private function getTestScoreSummaryHtml(): HtmlString
    {
        $response = $this->resolveResponseForReviewLevel();
        $test = $this->resolveTestForReviewStep();

        if (! $response || $test === null) {
            return new HtmlString('<p class="text-sm text-gray-500">—</p>');
        }

        $score = $response->test_score !== null
            ? number_format((float) $response->test_score, 2).'%'
            : '—';

        $eligibility = (float) ($test->eligibility_threshold ?? 0);
        $talent = (float) ($test->talent_threshold ?? 0);

        $eligibilityLine = $response->eligibility_passed
            ? __('admin.test_eligibility_passed', ['threshold' => $eligibility])
            : __('admin.test_eligibility_failed', ['threshold' => $eligibility]);

        $talentLine = $response->talent_passed
            ? __('admin.test_talent_passed', ['threshold' => $talent])
            : __('admin.test_talent_not_reached', ['threshold' => $talent]);

        $applicationScore = $this->record->main_score !== null
            ? number_format((float) $this->record->main_score, 2).'%'
            : '—';

        $pendingManual = app(TestScoringService::class)->responseHasPendingManualReview($response)
            ? __('admin.test_pending_manual_review')
            : __('admin.test_auto_score_complete');

        return new HtmlString(
            '<div class="space-y-1 text-sm">'
            .'<p><strong>'.e(__('admin.test_score')).':</strong> '.e($score).'</p>'
            .'<p><strong>'.e(__('admin.application_score')).':</strong> '.e($applicationScore).'</p>'
            .'<p>'.e($eligibilityLine).'</p>'
            .'<p>'.e($talentLine).'</p>'
            .'<p class="text-gray-600 dark:text-gray-400">'.e($pendingManual).'</p>'
            .'</div>'
        );
    }

    private function ensureReviewQuestionResponses(): void
    {
        $response = $this->resolveResponseForReviewLevel();
        $test = $this->resolveTestForReviewStep();

        if (! $response || ! $test) {
            return;
        }

        app(TestScoringService::class)->syncScorableQuestionResponses(
            $response,
            $test,
            $this->responseLevelForScoring()
        );
    }

    private function buildTestReviewFormData(): array
    {
        $test = $this->resolveTestForReviewStep();

        if (! $test) {
            return ['level_review_items' => []];
        }

        $response = $this->resolveResponseForReviewLevel();

        if ($response) {
            $response->loadMissing(['questionResponses.question', 'questionResponses.answer']);
        }

        $scoring = app(TestScoringService::class);
        $questions = $scoring->questionsForTestLevel($test, $this->responseLevelForScoring());

        $byQuestionId = $response
            ? $response->questionResponses->keyBy('question_id')
            : collect();

        $locale = app()->getLocale();
        $field = match ($locale) {
            'ar' => 'question_ar',
            'fr' => 'question_fr',
            default => 'question_en',
        };

        $items = [];

        foreach ($questions as $q) {
            $qr = $byQuestionId->get($q->id);
            $label = (string) ($q->{$field} ?? $q->question_en ?? $q->question_fr ?? '');
            $needsManual = $qr && ! $scoring->isAutoScorableComponent($q->component);

            $maxPoints = $scoring->maxPointsForQuestion($q);
            $manualPercent = null;

            if ($qr) {
                if ($qr->manual_score > 0) {
                    $manualPercent = (float) $qr->manual_score;
                } elseif ((float) ($qr->obtained_score ?? 0) > 0 && $needsManual) {
                    $manualPercent = $scoring->manualPercentFromPoints($q, (float) $qr->obtained_score);
                }
            }

            $items[] = [
                'question_response_id' => $qr?->id,
                'needs_manual' => $needsManual,
                'question_label' => $label !== '' ? $label : ('#'.$q->id),
                'answer_preview' => $qr
                    ? $this->formatAnswerPreview($qr)
                    : __('admin.application_review_no_answer'),
                'auto_score' => $qr
                    ? number_format((float) $qr->auto_score, 2, '.', '')
                    : '',
                'question_max_points' => number_format($maxPoints, 2, '.', ''),
                'manual_score' => $manualPercent,
            ];
        }

        return ['level_review_items' => $items];
    }

    public function saveScores(): void
    {
        if (! $this->hasResponseForReviewLevel()) {
            return;
        }

        $items = $this->form->getState()['level_review_items'] ?? [];

        foreach ($items as $row) {
            $id = $row['question_response_id'] ?? null;
            if (! $id) {
                continue;
            }

            $qr = QuestionResponse::query()->with('question')->find($id);
            if (! $qr) {
                continue;
            }

            $question = $qr->question;
            $scoring = app(TestScoringService::class);
            $manualRaw = $row['manual_score'] ?? null;
            $useManual = $manualRaw !== null && $manualRaw !== '';
            $manualPercent = $useManual ? min(100, max(0, (float) $manualRaw)) : 0.0;
            $auto = (float) $qr->auto_score;
            $obtained = $useManual && $question
                ? $scoring->pointsFromManualPercent($question, $manualPercent)
                : $auto;

            $qr->update([
                'manual_score' => $useManual ? $manualPercent : 0.0,
                'obtained_score' => round($obtained, 2),
            ]);
        }

        ApplicationProgressResource::recalculateScoresForResponse(
            $this->record->fresh(),
            $this->responseLevelForScoring()
        );

        $this->form->fill($this->buildTestReviewFormData());

        Notification::make()
            ->title(__('admin.application_review_scores_saved'))
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        if ($this->showsCvSection()) {
            $actions = array_merge($actions, [
                Action::make('viewCv')
                    ->label(__('admin.application_view_cv'))
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->url(fn () => $this->record->cvPublicUrl() ?? '#')
                    ->openUrlInNewTab()
                    ->visible(fn () => (bool) $this->record->resolveCvStoragePath()),
                Action::make('acceptCv')
                    ->label(fn (): string => $this->record->isFreeApplication()
                        ? __('admin.application_accept_cv_free')
                        : __('admin.application_accept_cv'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.application_accept_cv_heading'))
                    ->modalDescription(fn (): string => $this->record->isFreeApplication()
                        ? __('admin.application_accept_cv_description_free')
                        : __('admin.application_accept_cv_description'))
                    ->visible(fn () => $this->record->status === 'pending')
                    ->action(function () {
                        if ($this->record->isFreeApplication()) {
                            FreeApplicationWorkflow::acceptCv($this->record->fresh());
                            $this->record->refresh();

                            return;
                        }

                        $testId = $this->record->offre?->firstTestIdAfterCv() ?? $this->record->test_id;
                        if (! $testId) {
                            Notification::make()
                                ->title(__('admin.application_accept_needs_test'))
                                ->danger()
                                ->send();

                            return;
                        }

                        $this->record->update([
                            'test_id' => $testId,
                            'status' => 'in_progress',
                            'apply_enabled' => true,
                            'level_status' => 'in_progress',
                            'test_session_expires_at' => null,
                        ]);

                        $this->record->load('test');

                        CandidateNotification::create([
                            'user_id' => $this->record->candidate->user_id,
                            'type' => 'info',
                            'title' => __('admin.candidate_notif_cv_accepted_title'),
                            'message' => $this->record->offre
                                ? __('admin.candidate_notif_cv_accepted_body_with_offer', [
                                    'offer' => $this->record->offre->title,
                                    'test' => $this->record->test?->name ?? '',
                                ])
                                : __('admin.candidate_notif_cv_accepted_body_open', [
                                    'test' => $this->record->test?->name ?? '',
                                ]),
                            'offre_id' => $this->record->offre_id,
                            'application_progress_id' => $this->record->id,
                        ]);

                        Notification::make()
                            ->title(__('admin.application_toast_cv_accepted'))
                            ->success()
                            ->send();

                        $this->record->refresh();
                    }),
                Action::make('assignTest')
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
                    ->action(function (array $data): void {
                        FreeApplicationWorkflow::assignTest(
                            $this->record->fresh(),
                            (int) $data['test_id']
                        );
                        $this->record->refresh();
                    })
                    ->visible(fn (): bool => $this->canAssignTestOnThisPage()),
                Action::make('rejectCv')
                    ->label(__('admin.application_reject_cv'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.application_reject_cv_heading'))
                    ->modalDescription(__('admin.application_reject_cv_description'))
                    ->visible(fn () => $this->record->status === 'pending')
                    ->action(function () {
                        $this->record->update(['status' => 'rejected']);

                        CandidateNotification::create([
                            'user_id' => $this->record->candidate->user_id,
                            'type' => 'rejected',
                            'title' => __('admin.candidate_notif_rejected_title'),
                            'message' => $this->record->offre
                                ? __('admin.candidate_notif_rejected_body_with_offer', ['offer' => $this->record->offre->title])
                                : __('admin.candidate_notif_rejected_body_open'),
                            'offre_id' => $this->record->offre_id,
                            'application_progress_id' => $this->record->id,
                        ]);

                        Notification::make()
                            ->title(__('admin.application_toast_rejected'))
                            ->danger()
                            ->send();

                        $this->record->refresh();
                    }),
            ]);
        }

        if ($this->showsTestSection()) {
            $actions = array_merge($actions, [
            Action::make('saveScores')
                ->label(__('admin.application_review_save_scores'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->visible(fn (): bool => $this->hasResponseForReviewLevel())
                ->action(fn () => $this->saveScores()),
            Action::make('publishScore')
                ->label(__('admin.application_action_publish_score'))
                ->icon('heroicon-o-chart-bar')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->canPublishScoreForThisLevel())
                ->action(function () {
                    ApplicationProgressResource::publishScoreForRecord(
                        $this->record->fresh(),
                        $this->responseLevelForScoring()
                    );
                    $this->record->refresh();
                }),
            Action::make('validateLevel')
                ->label(__('admin.application_action_validate_level'))
                ->icon('heroicon-o-arrow-up-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading(__('admin.application_action_validate_level_heading'))
                ->modalDescription(__('admin.application_action_validate_level_description'))
                ->visible(fn (): bool => $this->canValidateThisLevel() && ! $this->record->isFreeApplication())
                ->action(function () {
                    ApplicationProgressResource::advanceToNextLevel($this->record->fresh());
                    $this->record->refresh();
                }),
            Action::make('assignTestAfterReview')
                ->label(__('admin.free_application_assign_another_test'))
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
                ->action(function (array $data): void {
                    FreeApplicationWorkflow::assignTest(
                        $this->record->fresh(),
                        (int) $data['test_id']
                    );
                    $this->record->refresh();
                })
                ->visible(fn (): bool => $this->canAssignTestAfterReview()),
            Action::make('validateProfile')
                ->label(__('admin.free_application_validate_profile'))
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading(__('admin.free_application_validate_profile_heading'))
                ->modalDescription(__('admin.free_application_validate_profile_description'))
                ->visible(fn (): bool => $this->canValidateFreeProfile())
                ->action(function (): void {
                    FreeApplicationWorkflow::validateProfile($this->record->fresh());
                    $this->record->refresh();
                }),
            ]);
        }

        return $actions;
    }

    protected function resolveBackUrl(): string
    {
        return $this->resolveApplicationHubBackUrl();
    }

    protected function resolveApplicationHubBackUrl(): string
    {
        $record = $this->record;

        if ($record->isFreeApplication()) {
            return ApplicationProgressResource::getUrl('free');
        }

        if ($record->offre_id) {
            return ApplicationProgressResource::getUrl('by_offer', ['offre' => $record->offre_id]);
        }

        return ApplicationProgressResource::getUrl('index');
    }

    private function formatAnswerPreview(QuestionResponse $qr): string
    {
        $text = trim((string) ($qr->text_answer ?? ''));
        if ($text !== '') {
            return $text;
        }

        $qr->loadMissing('answer');
        if ($qr->answer) {
            $choice = trim((string) ($qr->answer->text ?? ''));

            return $choice !== '' ? $choice : ('#'.$qr->answer_id);
        }

        if ($qr->obtained_score !== null && $qr->obtained_score !== '') {
            return (string) $qr->obtained_score;
        }

        return '—';
    }

    private function showsCvSection(): bool
    {
        return ApplicationProgressStepMapper::isCvOfferStep($this->reviewLevel);
    }

    private function showsTestSection(): bool
    {
        return ! ApplicationProgressStepMapper::isCvOfferStep($this->reviewLevel);
    }

    private function canAssignTestOnThisPage(): bool
    {
        if (! $this->record->isFreeApplication()) {
            return false;
        }

        return $this->record->isAwaitingTestAssignment()
            && ApplicationProgressStepMapper::isCvOfferStep($this->reviewLevel);
    }

    private function canAssignTestAfterReview(): bool
    {
        if (! $this->record->isFreeApplication()) {
            return false;
        }

        return $this->record->status === 'in_progress'
            && $this->record->level_status === 'awaiting_approval'
            && $this->hasResponseForReviewLevel()
            && (int) $this->record->current_level === $this->responseLevelForScoring();
    }

    private function canValidateFreeProfile(): bool
    {
        return $this->canAssignTestAfterReview();
    }

    private function hasResponseForReviewLevel(): bool
    {
        return $this->resolveResponseForReviewLevel() !== null;
    }

    private function canPublishScoreForThisLevel(): bool
    {
        if (! $this->hasResponseForReviewLevel()) {
            return false;
        }
        if ((int) $this->record->current_level !== $this->responseLevelForScoring()) {
            return false;
        }
        if ($this->record->level_status !== 'awaiting_approval') {
            return false;
        }

        return ! $this->record->score_published;
    }

    private function canValidateThisLevel(): bool
    {
        if ($this->record->status !== 'in_progress') {
            return false;
        }
        if ((int) $this->record->current_level !== $this->responseLevelForScoring()) {
            return false;
        }
        if ($this->record->level_status !== 'awaiting_approval') {
            return false;
        }

        return true;
    }

    public function getCvPlaceholder(): HtmlString
    {
        $record = $this->record;
        $url = $record->cvPublicUrl();
        if (! $url) {
            return new HtmlString(
                '<p class="text-sm text-gray-500 dark:text-gray-400">'.e(__('admin.application_no_cv')).'</p>'
            );
        }

        return new HtmlString(
            '<a href="'.e($url).'" target="_blank" rel="noopener noreferrer" '
            .'class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-btn-color-primary fi-btn-variant outlined fi-size-md gap-1.5 px-3 py-2 text-sm inline-flex">'
            .e(__('admin.application_view_cv_open'))
            .'</a>'
        );
    }
}
