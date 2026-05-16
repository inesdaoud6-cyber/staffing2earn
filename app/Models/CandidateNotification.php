<?php

namespace App\Models;

use App\Support\CandidateNotificationLinks;
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
        'application_progress_id',
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

    public function applicationProgress(): BelongsTo
    {
        return $this->belongsTo(ApplicationProgress::class, 'application_progress_id');
    }

    public function resolveApplicationProgressId(): ?int
    {
        return CandidateNotificationLinks::resolveApplicationProgressId($this);
    }

    /**
     * Where clicking this notification should take the candidate.
     */
    public function getUrlAttribute(): string
    {
        return CandidateNotificationLinks::resolve($this);
    }
}
