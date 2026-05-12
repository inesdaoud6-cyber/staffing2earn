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
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'deadline'     => 'date',
    ];

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

   
    public function applicationProgresses(): HasMany
    {
        return $this->hasMany(ApplicationProgress::class);
    }
}
