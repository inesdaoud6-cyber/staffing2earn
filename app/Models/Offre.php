<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Offre extends Model
{
    protected $fillable = [
        'title',
        'description',
        'domain',
        'location',
        'contract_type',
        'deadline',
        'is_published',
        'test_id',
        'levels_count',
        'level_test_ids',
    ];

    protected $casts = [
        'is_published'   => 'boolean',
        'deadline'       => 'date',
        'levels_count'   => 'integer',
        'level_test_ids' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (Offre $offre): void {
            $ids = array_values($offre->level_test_ids ?? []);
            $need = max(0, (int) $offre->levels_count - 1);
            $ids = array_slice($ids, 0, $need);
            $offre->level_test_ids = $ids;
            $offre->test_id = $ids[0] ?? null;
        });
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class, 'test_id');
    }

    public function applicationProgresses(): HasMany
    {
        return $this->hasMany(ApplicationProgress::class);
    }

    /**
     * Test attached to a given level (1 = CV only, no test id).
     */
    public function testIdForLevel(int $level): ?int
    {
        if ($level <= 1) {
            return null;
        }

        $ids = array_values($this->level_test_ids ?? []);
        $idx = $level - 2;

        return isset($ids[$idx]) ? (int) $ids[$idx] : null;
    }

    /**
     * First test after CV (same as legacy `test_id` once synced).
     */
    public function firstTestIdAfterCv(): ?int
    {
        return $this->testIdForLevel(2);
    }
}
