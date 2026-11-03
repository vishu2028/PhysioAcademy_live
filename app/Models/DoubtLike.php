<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoubtLike extends Model
{
    protected $fillable = [
        'doubt_id',
        'user_id',
    ];

    public function doubt(): BelongsTo
    {
        return $this->belongsTo(Doubt::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
