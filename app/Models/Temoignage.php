<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Temoignage extends Model
{
    protected $table = 'temoignages';

    protected $fillable = [
        'user_id',
        'contenu',
        'note',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'note'        => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
