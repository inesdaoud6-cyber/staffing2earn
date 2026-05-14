<?php

namespace App\Filament\Resources\TestResource\Pages;

use App\Filament\Resources\TestResource;
use App\Models\Group;
use App\Models\Question;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class ManageTestQuestions extends EditRecord
{
    protected static string $resource = TestResource::class;

    protected static ?string $title = null;

    public function getTitle(): string
    {
        return __('test.manage-questions');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('test.questions-du-test'))->schema([
                Select::make('filter_group_id')
                    ->label(__('test.filtrer-groupe'))
                    ->options(Group::pluck('name', 'id'))
                    ->placeholder(__('test.tous-groupes'))
                    ->live()
                    ->dehydrated(false),
                CheckboxList::make('questions')
                    ->label(__('test.selectionner-questions'))
                    ->relationship('questions', 'question_fr')
                    ->options(function (Get $get) {
                        $groupId = $get('filter_group_id');
                        $query = Question::query();
                        if ($groupId) {
                            $query->where('group_id', $groupId);
                        }

                        return $query->get()->mapWithKeys(function ($question) {
                            $group = $question->group?->name ?? '—';
                            $label = "[{$group}] ".Str::limit($question->question_fr, 80);

                            return [$question->id => $label];
                        });
                    })
                    ->bulkToggleable()
                    ->columns(1)
                    ->gridDirection('row')
                    ->helperText(__('test.helper-filtre')),
            ]),
        ]);
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title(__('test.questions-mises-a-jour'))
            ->success();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back_to_view')
                ->label(__('test.retour-details'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left')
                ->url($this->getRedirectUrl()),
        ];
    }
}
