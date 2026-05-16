<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    protected $table = 'question_groups';

    protected $fillable = ['block_id', 'name', 'description', 'order'];

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}