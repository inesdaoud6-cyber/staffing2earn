<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationProgress extends Model
{
    protected $fillable = [
        'candidate_id',
        'offre_id',
        'test_id',
        'status',
        'current_level',
        'level_status',
        'level_started_at',
        'main_score',
        'secondary_score',
        'apply_enabled',
        'score_published',
        'is_archived',
    ];

    protected $casts = [
        'apply_enabled'    => 'boolean',
        'score_published'  => 'boolean',
        'is_archived'      => 'boolean',
        'main_score'       => 'decimal:2',
        'secondary_score'  => 'decimal:2',
        'level_started_at' => 'datetime',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    public function offre(): BelongsTo
    {
        return $this->belongsTo(Offre::class);
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class, 'application_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    public function scopeForCandidate($query, int $candidateId)
    {
        return $query->where('candidate_id', $candidateId);
    }

    public function isTimeLimitExceeded(): bool
    {
        if (! $this->level_started_at || ! $this->test) {
            return false;
        }

        $limit = $this->test->time_limit_per_level ?? null;

        return $limit && now()->diffInSeconds($this->level_started_at) > $limit;
    }

    public function startLevel(int $level): void
    {
        $this->update([
            'current_level'    => $level,
            'level_status'     => 'in_progress',
            'level_started_at' => now(),
        ]);
    }
}
