<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Block extends Model
{
    protected $fillable = [
        'name',
        'title',
        'order',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }
}