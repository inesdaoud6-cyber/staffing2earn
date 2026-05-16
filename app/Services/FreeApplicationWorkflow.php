<?php

namespace App\Services;

use App\Models\ApplicationProgress;
use App\Models\CandidateNotification;
use App\Models\Question;
use App\Models\Test;
use Filament\Notifications\Notification;

class FreeApplicationWorkflow
{
    public static function acceptCv(ApplicationProgress $record): void
    {
        abort_unless($record->isFreeApplication(), 403);
        abort_unless($record->status === 'pending', 422);

        $record->update([
            'status' => 'in_progress',
            'level_status' => 'approved',
            'apply_enabled' => false,
            'test_id' => null,
            'test_session_expires_at' => null,
        ]);

        $record->loadMissing('candidate');

        CandidateNotification::create([
            'user_id' => $record->candidate->user_id,
            'type' => 'info',
            'title' => __('admin.candidate_notif_free_cv_accepted_title'),
            'message' => __('admin.candidate_notif_free_cv_accepted_body'),
            'offre_id' => null,
            'application_progress_id' => $record->id,
        ]);

        Notification::make()
            ->title(__('admin.application_toast_free_cv_accepted'))
            ->success()
            ->send();
    }

    public static function assignTest(ApplicationProgress $record, int $testId): void
    {
        abort_unless($record->isFreeApplication(), 403);
        abort_unless($record->status === 'in_progress', 422);

        $test = Test::query()->findOrFail($testId);

        $maxResponseLevel = (int) ($record->responses()->max('level') ?? 0);
        $nextLevel = $maxResponseLevel > 0
            ? $maxResponseLevel + 1
            : (int) (Question::query()
                ->whereHas('tests', fn ($q) => $q->where('tests.id', $testId))
                ->min('level') ?? 1);

        $record->update([
            'test_id' => $testId,
            'current_level' => $nextLevel,
            'level_status' => 'in_progress',
            'apply_enabled' => true,
            'score_published' => false,
            'test_session_expires_at' => null,
        ]);

        $record->loadMissing('candidate', 'test');

        CandidateNotification::create([
            'user_id' => $record->candidate->user_id,
            'type' => 'info',
            'title' => __('admin.candidate_notif_free_test_assigned_title'),
            'message' => __('admin.candidate_notif_free_test_assigned_body', [
                'test' => $test->name,
            ]),
            'offre_id' => null,
            'application_progress_id' => $record->id,
        ]);

        Notification::make()
            ->title(__('admin.application_toast_free_test_assigned', ['test' => $test->name]))
            ->success()
            ->send();
    }

    public static function validateProfile(ApplicationProgress $record): void
    {
        abort_unless($record->isFreeApplication(), 403);
        abort_unless($record->status === 'in_progress', 422);

        $record->update([
            'status' => 'validated',
            'level_status' => 'approved',
            'apply_enabled' => false,
        ]);

        $record->loadMissing('candidate');

        CandidateNotification::create([
            'user_id' => $record->candidate->user_id,
            'type' => 'validated',
            'title' => __('admin.candidate_notif_free_potential_title'),
            'message' => __('admin.candidate_notif_free_potential_body'),
            'offre_id' => null,
            'application_progress_id' => $record->id,
        ]);

        Notification::make()
            ->title(__('admin.application_toast_free_profile_validated'))
            ->success()
            ->send();
    }
}
