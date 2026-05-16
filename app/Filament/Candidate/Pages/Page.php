<?php

namespace App\Filament\Candidate\Pages;

use App\Filament\Concerns\HasBackHeaderAction;
use Filament\Pages\Page as BasePage;

abstract class Page extends BasePage
{
    use HasBackHeaderAction;

    protected function shouldShowBackNavigation(): bool
    {
        return ! static::$shouldRegisterNavigation;
    }

    protected function resolveCandidateBackUrl(): string
    {
        return route('filament.candidate.pages.dashboard');
    }
}
