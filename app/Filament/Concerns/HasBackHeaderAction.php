<?php

namespace App\Filament\Concerns;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

trait HasBackHeaderAction
{
    public function getHeading(): string | Htmlable
    {
        $heading = $this->getPageHeadingWithoutBack();

        if (! $this->shouldShowBackNavigation()) {
            return $heading;
        }

        return $this->wrapHeadingWithBackNavigation(
            $heading,
            $this->resolveBackUrl(),
            $this->getBackNavigationLabel(),
        );
    }

    protected function getPageHeadingWithoutBack(): string | Htmlable
    {
        return $this->heading ?? $this->getTitle();
    }

    protected function shouldShowBackNavigation(): bool
    {
        return true;
    }

    protected function getBackNavigationLabel(): string
    {
        return __('common.back');
    }

    protected function wrapHeadingWithBackNavigation(
        string | Htmlable $heading,
        string $url,
        string $label,
    ): Htmlable {
        return new HtmlString(
            view('filament.partials.heading-with-back', [
                'url' => $url,
                'label' => $label,
                'heading' => $heading,
            ])->render()
        );
    }

    protected function resolveBackUrl(): string
    {
        if (method_exists($this, 'resolveCandidateBackUrl')) {
            return $this->resolveCandidateBackUrl();
        }

        if (method_exists(static::class, 'getResource')) {
            return static::getResource()::getUrl('index');
        }

        return filament()->getUrl();
    }

    /**
     * @param  list<mixed>  $actions
     * @return list<mixed>
     */
    protected function prependBackHeaderAction(array $actions, ?string $url = null, ?string $label = null): array
    {
        return $actions;
    }
}
