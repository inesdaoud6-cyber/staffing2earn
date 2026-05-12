<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'birth_date',
        'address',
        'cv_path',
        'primary_score',
        'secondary_score',
        'status',
        'score_visibility',
    ];

    protected $casts = [
        'score_visibility' => 'boolean',
        'birth_date'       => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(ApplicationProgress::class);
    }

    public function applicationProgresses(): HasMany
    {
        return $this->hasMany(ApplicationProgress::class, 'candidate_id');
    }

    public function getEmailAttribute(): string
    {
        return $this->attributes['email'] ?? $this->user?->email ?? '';
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name) ?: $this->user?->name ?? 'Unknown';
    }

    public function getTotalApplicationsAttribute(): int
    {
        return $this->applicationProgresses()->count();
    }
}