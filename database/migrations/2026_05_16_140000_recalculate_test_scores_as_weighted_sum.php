<?php

use App\Models\ApplicationProgress;
use App\Models\Response;
use App\Services\TestScoringService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $scoring = app(TestScoringService::class);

        Response::query()
            ->whereNotNull('test_score')
            ->with(['application.test'])
            ->orderBy('id')
            ->chunkById(100, function ($responses) use ($scoring): void {
                foreach ($responses as $response) {
                    $application = $response->application;

                    if (! $application?->test) {
                        continue;
                    }

                    $scoring->evaluateResponse($response, $application);
                }
            });

        ApplicationProgress::query()
            ->whereNotNull('main_score')
            ->orderBy('id')
            ->chunkById(100, function ($applications) use ($scoring): void {
                foreach ($applications as $application) {
                    $scoring->evaluateApplication($application);
                }
            });
    }

    public function down(): void
    {
        // Scores cannot be restored to the previous averaging formula.
    }
};
