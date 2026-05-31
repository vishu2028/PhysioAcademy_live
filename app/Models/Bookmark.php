<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Bookmark extends Model
{
    protected $fillable = ['user_id', 'bookmarkable_id', 'bookmarkable_type'];

    /**
     * Get the parent bookmarkable model (Topic or LearningMaterial).
     */
    public function bookmarkable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who bookmarked the item.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
