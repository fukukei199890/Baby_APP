<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FeedingRecord extends Model
{
    protected $casts = [
        'fed_at' => 'date',
        'is_first_time' => 'boolean',
    ];

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function post(): HasOne
    {
        return $this->hasOne(Post::class);
    }
}
