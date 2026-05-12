<?php

namespace App\Filament\Candidate\Pages;

use App\Models\ApplicationProgress;
use App\Models\Candidate;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class MyProfile extends Page
{
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.candidate.pages.my-profile';
    protected static ?string $title = 'Mon Profil';
    protected static ?string $slug = 'my-profile';

    public $user;
    public $candidate;
    public int $totalApplications = 0;
    public int $validatedApplications = 0;
    public float $averageScore = 0;
    public bool $isAdminViewing = false;
    public $primaryScore = null;
    public $secondaryScore = null;

    public function mount(): void
    {
        $this->user      = auth()->user();
        $this->candidate = Candidate::where('user_id', $this->user->id)->first();

        $applications = $this->candidate
            ? ApplicationProgress::where('candidate_id', $this->candidate->id)->get()
            : collect();

        $this->totalApplications     = $applications->count();
        $this->validatedApplications = $applications->where('status', 'validated')->count();
        $this->averageScore          = round($applications->avg('main_score') ?? 0, 2);
        $this->isAdminViewing        = $this->user->can('view-candidate-scores');

        if ($this->isAdminViewing && $this->candidate) {
            $this->primaryScore   = $this->candidate->primary_score;
            $this->secondaryScore = $this->candidate->secondary_score;
        }
    }

    protected function getHeaderActions(): array
    {
        if (! auth()->user()->can('edit-candidate-status')) {
            return [];
        }

        return [
            Action::make('changeStatus')
                ->label('Changer le statut')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->form([
                    Select::make('status')
                        ->label('Nouveau statut')
                        ->options([
                            'pending'     => __('Pending'),
                            'in_progress' => __('In Progress'),
                            'validated'   => __('Validated'),
                            'rejected'    => __('Rejected'),
                        ])
                        ->required(),
                ])
                ->action(function (array $data): void {
                    if ($this->candidate) {
                        $this->candidate->update(['status' => $data['status']]);
                        $this->candidate = $this->candidate->fresh();

                        Notification::make()
                            ->title('Statut mis à jour')
                            ->success()
                            ->send();
                    }
                }),
        ];
    }
}