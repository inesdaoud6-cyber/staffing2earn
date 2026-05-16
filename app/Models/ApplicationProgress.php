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
     * True when the candidate may still take the timed test at the current level (no submission yet).
     */
    public function canStartTimedTestSession(): bool
    {
        if ($this->level_status !== 'in_progress' || ! $this->test_id) {
            return false;
        }

        return ! $this->responses()
            ->where('level', $this->current_level)
            ->exists();
    }

    /**
     * Drop an expired deadline left from a previous visit so the candidate gets a full window
     * when they open the take-test page again without having submitted.
     */
    public function clearStaleTestSessionDeadline(): void
    {
        if (! $this->test_session_expires_at || ! $this->wholeTestSessionExpired()) {
            return;
        }

        if (! $this->canStartTimedTestSession()) {
            return;
        }

        $this->update(['test_session_expires_at' => null]);
        $this->test_session_expires_at = null;
    }

    /**
     * Wall-clock deadline for the whole test — starts when the candidate opens the take-test page
     * (first visit, or a new visit after an expired window with no submission).
     */
    public function ensureWholeTestSessionDeadline(): void
    {
        $test = $this->test;
        if (! $test || ! $test->whole_test_timer_enabled || ! $test->whole_test_timer_minutes) {
            return;
        }

        if (! $this->canStartTimedTestSession()) {
            return;
        }

        $this->clearStaleTestSessionDeadline();

        if ($this->test_session_expires_at) {
            return;
        }

        $seconds = max(1, (int) $test->whole_test_timer_minutes) * 60;
        $this->update([
            'test_session_expires_at' => now()->addSeconds($seconds),
        ]);
        $this->refresh();
    }

    public function wholeTestSessionExpired(): bool
    {
        return $this->test_session_expires_at && now()->greaterThanOrEqualTo($this->test_session_expires_at);
    }

    public function isFreeApplication(): bool
    {
        return $this->offre_id === null;
    }

    /**
     * Free application: CV accepted, waiting for admin to pick a test.
     */
    public function isAwaitingTestAssignment(): bool
    {
        return $this->isFreeApplication()
            && $this->status === 'in_progress'
            && $this->test_id === null
            && $this->level_status === 'approved';
    }
}

