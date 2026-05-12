<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Test extends Model
{
    protected $fillable = [
        'name',
        'description',
        'eligibility_threshold',
        'talent_threshold',
        'time_limit_per_level',
    ];

    protected $casts = [
        'eligibility_threshold' => 'decimal:2',
        'talent_threshold'      => 'decimal:2',
        'time_limit_per_level'  => 'integer',
    ];

    public function offres(): HasMany
    {
        return $this->hasMany(Offre::class, 'test_id');
    }

    public function blocks(): BelongsToMany
    {
        return $this->belongsToMany(Block::class, 'test_block');
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'question_test');
    }

    public function applicationProgresses(): HasMany
    {
        return $this->hasMany(ApplicationProgress::class);
    }

    public function getTimeLimitFormattedAttribute(): ?string
    {
        if (! $this->time_limit_per_level) {
            return null;
        }

        $minutes = intdiv($this->time_limit_per_level, 60);
        $seconds = $this->time_limit_per_level % 60;

        return $seconds > 0
            ? "{$minutes}m {$seconds}s"
            : "{$minutes}m";
    }
}
