<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    protected $casts = [
        'posted_at' => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'child_id',
        'feeding_record_id',
        'photo_path',
        'caption',
        'posted_at',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function feedingRecord(): BelongsTo
    {
        return $this->belongsTo(FeedingRecord::class);
    }
}
