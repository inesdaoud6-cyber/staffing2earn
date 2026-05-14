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
        'whole_test_timer_enabled',
        'whole_test_timer_minutes',
    ];

    protected $casts = [
        'eligibility_threshold'       => 'decimal:2',
        'talent_threshold'            => 'decimal:2',
        'whole_test_timer_enabled'    => 'boolean',
        'whole_test_timer_minutes'    => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Test $test): void {
            if (! $test->whole_test_timer_enabled) {
                $test->whole_test_timer_minutes = null;
            }
        });
    }

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
}
