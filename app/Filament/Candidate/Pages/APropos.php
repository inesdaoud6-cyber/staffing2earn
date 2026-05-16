<?php

namespace App\Filament\Candidate\Pages;

use App\Models\ApplicationProgress;
use App\Models\Candidate;
use App\Models\Temoignage;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class APropos extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-information-circle';
    protected static string $view = 'filament.candidate.pages.a-propos';
    protected static ?string $slug = 'a-propos';
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = 'candidate.footer';

    public function getTitle(): string
    {
        return __('About Staffing2Earn');
    }

    public static function getNavigationLabel(): string
    {
        return __('nav.about');
    }

    public $temoignages;
    public bool $hasApplied = false;
    public ?Temoignage $myTemoignage = null;
    public string $contenu = '';
    public int $note = 5;

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->temoignages = Temoignage::where('is_approved', true)
            ->with('user')
            ->latest()
            ->take(10)
            ->get();

        $candidate = Candidate::where('user_id', auth()->id())->first();

        $this->hasApplied = $candidate
            ? ApplicationProgress::where('candidate_id', $candidate->id)->exists()
            : false;

        $this->myTemoignage = Temoignage::where('user_id', auth()->id())->first();

        if ($this->myTemoignage) {
            $this->contenu = $this->myTemoignage->contenu;
            $this->note    = $this->myTemoignage->note;
        }
    }

    public function submitTemoignage(): void
    {
        $this->validate([
            'contenu' => 'required|min:20|max:500',
            'note'    => 'required|integer|min:1|max:5',
        ]);

        Temoignage::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'contenu'     => $this->contenu,
                'note'        => $this->note,
                'is_approved' => false,
            ]
        );

        $this->myTemoignage = Temoignage::where('user_id', auth()->id())->first();

        Notification::make()
            ->title(__('temoignage.saved'))
            ->success()
            ->send();
    }
}