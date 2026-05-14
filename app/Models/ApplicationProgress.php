<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationProgress extends Model
{
    protected $table = 'application_progress';

    protected $fillable = [
        'candidate_id',
        'offre_id',
        'test_id',
        'cv_path',
        'status',
        'current_level',
        'level_status',
        'main_score',
        'secondary_score',
        'apply_enabled',
        'score_published',
        'is_archived',
        'test_session_expires_at',
    ];

    protected $casts = [
        'apply_enabled'           => 'boolean',
        'score_published'         => 'boolean',
        'is_archived'             => 'boolean',
        'main_score'              => 'decimal:2',
        'secondary_score'         => 'decimal:2',
        'test_session_expires_at' => 'datetime',
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

    /**
     * CV stored on the application row, or fallback to the candidate profile CV.
     */
    public function resolveCvStoragePath(): ?string
    {
        return $this->cv_path ?: $this->candidate?->cv_path;
    }

    public function cvPublicUrl(): ?string
    {
        $path = $this->resolveCvStoragePath();

        return $path ? asset('storage/' . $path) : null;
    }

    /**
     * Wall-clock deadline for the whole test session (all levels), set once when the candidate first opens the test.
     */
    public function ensureWholeTestSessionDeadline(): void
    {
        $test = $this->test;
        if (! $test || ! $test->whole_test_timer_enabled || ! $test->whole_test_timer_minutes) {
            return;
        }
        if ($this->test_session_expires_at) {
            return;
        }
        $seconds = max(1, (int) $test->whole_test_timer_minutes) * 60;
        $this->update([
            'test_session_expires_at' => now()->addSeconds($seconds),
        ]);
    }

    public function wholeTestSessionExpired(): bool
    {
        return $this->test_session_expires_at && now()->greaterThanOrEqualTo($this->test_session_expires_at);
    }
}
