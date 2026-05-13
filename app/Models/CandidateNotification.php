<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'is_read',
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

    public function offre(): BelongsTo
    {
        return $this->belongsTo(Offre::class);
    }

    /**
     * Where clicking this notification should take the candidate. Falls back
     * to the notifications page so a bogus type can never produce a dead link.
     */
    public function getUrlAttribute(): string
    {
        $applicationsRoute  = route('filament.candidate.pages.applications');
        $offresRoute        = route('filament.candidate.pages.choix-candidature');
        $notificationsRoute = route('filament.candidate.pages.notifications');

        return match ($this->type) {
            'offre' => $this->offre_id
                ? $offresRoute . '#offre-' . $this->offre_id
                : $offresRoute,
            'application', 'validated', 'rejected', 'result' => $applicationsRoute,
            default => $notificationsRoute,
        };
    }
}
