<?php

namespace App\Filament\Candidate\Pages;

use App\Models\ApplicationProgress;
use App\Models\Candidate;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Validator;
use Livewire\WithFileUploads;

class MyProfile extends Page
{
    use WithFileUploads;

    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.candidate.pages.my-profile';
    protected static ?string $slug = 'my-profile';

    public function getTitle(): string
    {
        return __('My Profile');
    }

    public $user;
    public $candidate;
    public int $totalApplications = 0;
    public int $validatedApplications = 0;
    public float $averageScore = 0;
    public bool $isAdminViewing = false;
    public $primaryScore = null;
    public $secondaryScore = null;

    // Inline edit state
    public bool $editingPersonal = false;
    public bool $editingAccount  = false;
    public bool $showCv          = false;

    // Editable buffers (so Cancel can discard without re-querying)
    public ?string $firstName  = null;
    public ?string $lastName   = null;
    public ?string $phone      = null;
    public ?string $birthDate  = null;
    public ?string $address    = null;
    public ?string $userName   = null;
    public ?string $userEmail  = null;

    // CV upload (Livewire temporary file)
    public $newCv;

    public function mount(): void
    {
        $this->loadFromDb();
        $this->resetEditBuffers();
    }

    private function loadFromDb(): void
    {
        $this->user      = auth()->user();
        $this->candidate = Candidate::where('user_id', $this->user->id)->first();

        // First visit (or older orphan account from a broken registration): bootstrap
        // the candidate row using whatever we know from the User. Without this, every
        // form field would be blank even when the user already typed their name during
        // registration, and any update would crash because of NOT NULL columns.
        if (! $this->candidate) {
            [$first, $last]  = $this->splitName($this->user->name ?? '');
            $this->candidate = Candidate::create([
                'user_id'    => $this->user->id,
                'first_name' => $first ?: null,
                'last_name'  => $last ?: null,
                'email'      => $this->user->email,
            ]);
        } elseif (! $this->candidate->first_name && $this->user->name) {
            // Existing row but the name was never propagated — backfill from User.
            [$first, $last] = $this->splitName($this->user->name);
            $this->candidate->update([
                'first_name' => $first ?: null,
                'last_name'  => $last ?: null,
            ]);
            $this->candidate->refresh();
        }

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

    private function resetEditBuffers(): void
    {
        [$fallbackFirst, $fallbackLast] = $this->splitName($this->user?->name ?? '');

        $this->firstName = $this->candidate?->first_name ?: $fallbackFirst;
        $this->lastName  = $this->candidate?->last_name  ?: $fallbackLast;
        $this->phone     = $this->candidate?->phone;
        $this->birthDate = $this->candidate?->birth_date?->format('Y-m-d');
        $this->address   = $this->candidate?->address;
        $this->userName  = $this->user?->name;
        $this->userEmail = $this->user?->email;
    }

    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2);

        return [$parts[0] ?? '', $parts[1] ?? ''];
    }

    // ---------- Personal Information ----------

    public function editPersonal(): void
    {
        if ($this->isAdminViewing) {
            return;
        }
        $this->resetEditBuffers();
        $this->editingPersonal = true;
    }

    public function cancelPersonal(): void
    {
        $this->resetEditBuffers();
        $this->editingPersonal = false;
    }

    public function savePersonal(): void
    {
        if ($this->isAdminViewing) {
            return;
        }

        $data = Validator::make([
            'firstName' => $this->firstName,
            'lastName'  => $this->lastName,
            'phone'     => $this->phone,
            'birthDate' => $this->birthDate,
            'address'   => $this->address,
        ], [
            'firstName' => ['nullable', 'string', 'max:255'],
            'lastName'  => ['nullable', 'string', 'max:255'],
            'phone'     => ['nullable', 'string', 'max:50'],
            'birthDate' => ['nullable', 'date'],
            'address'   => ['nullable', 'string', 'max:500'],
        ])->validate();

        $candidate = $this->candidate
            ?: Candidate::firstOrNew(['user_id' => $this->user->id]);

        $candidate->fill([
            'first_name' => $data['firstName'] ?: null,
            'last_name'  => $data['lastName']  ?: null,
            'phone'      => $data['phone']     ?: null,
            'birth_date' => $data['birthDate'] ?: null,
            'address'    => $data['address']   ?: null,
        ]);
        $candidate->user_id = $this->user->id;
        $candidate->save();

        // Keep user.name in sync only when we have at least one of first/last.
        $newName = trim(($data['firstName'] ?? '') . ' ' . ($data['lastName'] ?? ''));
        if ($newName !== '') {
            $this->user->update(['name' => $newName]);
        }

        $this->editingPersonal = false;
        $this->loadFromDb();
        $this->resetEditBuffers();

        Notification::make()->title(__('Profile updated.'))->success()->send();
    }

    // ---------- Account ----------

    public function editAccount(): void
    {
        if ($this->isAdminViewing) {
            return;
        }
        $this->resetEditBuffers();
        $this->editingAccount = true;
    }

    public function cancelAccount(): void
    {
        $this->resetEditBuffers();
        $this->editingAccount = false;
    }

    public function saveAccount(): void
    {
        if ($this->isAdminViewing) {
            return;
        }

        $data = Validator::make([
            'userName'  => $this->userName,
            'userEmail' => $this->userEmail,
        ], [
            'userName'  => ['required', 'string', 'max:255'],
            'userEmail' => ['required', 'email', 'max:255', 'unique:users,email,' . $this->user->id],
        ])->validate();

        $this->user->update([
            'name'  => $data['userName'],
            'email' => $data['userEmail'],
        ]);

        $this->editingAccount = false;
        $this->loadFromDb();
        $this->resetEditBuffers();

        Notification::make()->title(__('Account updated.'))->success()->send();
    }

    // ---------- CV ----------

    public function toggleCv(): void
    {
        $this->showCv = ! $this->showCv;
    }

    public function uploadCv(): void
    {
        if ($this->isAdminViewing) {
            return;
        }

        $this->validate([
            'newCv' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $path = $this->newCv->store('cvs', 'public');

        // loadFromDb() guarantees $this->candidate exists.
        $this->candidate->update(['cv_path' => $path]);

        $this->newCv = null;
        $this->loadFromDb();

        Notification::make()->title(__('CV uploaded.'))->success()->send();
    }

    public function deleteCv(): void
    {
        if ($this->isAdminViewing || ! $this->candidate?->cv_path) {
            return;
        }

        $this->candidate->update(['cv_path' => null]);
        $this->showCv = false;
        $this->loadFromDb();

        Notification::make()->title(__('CV removed.'))->success()->send();
    }

    // ---------- Admin status header action ----------

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