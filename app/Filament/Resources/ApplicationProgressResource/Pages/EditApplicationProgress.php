<?php

namespace App\Filament\Resources\ApplicationProgressResource\Pages;

use App\Filament\Resources\ApplicationProgressResource;
use App\Models\CandidateNotification;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditApplicationProgress extends EditRecord
{
    protected static string $resource = ApplicationProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewCv')
                ->label(__('admin.application_view_cv'))
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->url(fn () => $this->record->cvPublicUrl() ?? '#')
                ->openUrlInNewTab()
                ->visible(fn () => (bool) $this->record->resolveCvStoragePath()),
            Action::make('acceptCv')
                ->label(__('admin.application_accept_cv'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading(__('admin.application_accept_cv_heading'))
                ->modalDescription(__('admin.application_accept_cv_description'))
                ->visible(fn () => $this->record->status === 'pending')
                ->action(function () {
                    $testId = $this->form->getState()['test_id'] ?? $this->record->test_id;
                    if (! $testId) {
                        Notification::make()
                            ->title(__('admin.application_accept_needs_test'))
                            ->danger()
                            ->send();

                        return;
                    }

                    $this->record->update([
                        'test_id'       => $testId,
                        'status'        => 'in_progress',
                        'apply_enabled' => true,
                    ]);

                    CandidateNotification::create([
                        'user_id'  => $this->record->candidate->user_id,
                        'type'     => 'info',
                        'title'    => __('admin.candidate_notif_cv_accepted_title'),
                        'message'  => $this->record->offre
                            ? __('admin.candidate_notif_cv_accepted_body_with_offer', [
                                'offer' => $this->record->offre->title,
                                'test'  => $this->record->test?->name ?? '',
                            ])
                            : __('admin.candidate_notif_cv_accepted_body_open', [
                                'test' => $this->record->test?->name ?? '',
                            ]),
                        'offre_id' => $this->record->offre_id,
                    ]);

                    Notification::make()
                        ->title(__('admin.application_toast_cv_accepted'))
                        ->success()
                        ->send();

                    $this->record->refresh();
                    $this->refreshFormData(['test_id', 'status', 'apply_enabled']);
                }),
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
                        'user_id'  => $this->record->candidate->user_id,
                        'type'     => 'rejected',
                        'title'    => __('admin.candidate_notif_rejected_title'),
                        'message'  => $this->record->offre
                            ? __('admin.candidate_notif_rejected_body_with_offer', ['offer' => $this->record->offre->title])
                            : __('admin.candidate_notif_rejected_body_open'),
                        'offre_id' => $this->record->offre_id,
                    ]);

                    Notification::make()
                        ->title(__('admin.application_toast_rejected'))
                        ->danger()
                        ->send();

                    $this->record->refresh();
                    $this->refreshFormData(['status']);
                }),
            DeleteAction::make(),
        ];
    }
}
