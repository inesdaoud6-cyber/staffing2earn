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
    ];

    protected $casts = [
        'eligibility_threshold' => 'decimal:2',
        'talent_threshold'      => 'decimal:2',
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
}
