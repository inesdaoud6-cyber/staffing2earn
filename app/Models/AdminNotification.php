<?php

namespace App\Models;

use App\Filament\Resources\ApplicationProgressResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminNotification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'is_read',
        'application_progress_id',
        'offre_id',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function applicationProgress(): BelongsTo
    {
        return $this->belongsTo(ApplicationProgress::class, 'application_progress_id');
    }

    public function offre(): BelongsTo
    {
        return $this->belongsTo(Offre::class);
    }

    public function getUrlAttribute(): string
    {
        if ($this->application_progress_id) {
            $application = $this->relationLoaded('applicationProgress')
                ? $this->applicationProgress
                : ApplicationProgress::query()->find($this->application_progress_id);

            if ($application) {
                return ApplicationProgressResource::reviewUrlFor($application);
            }
        }

        return route('filament.admin.pages.admin-notifications');
    }
}
