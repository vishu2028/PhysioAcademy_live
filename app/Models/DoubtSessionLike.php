<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoubtSessionLike extends Model
{
    protected $fillable = [
        'doubt_session_booking_id',
        'user_id',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(DoubtSessionBooking::class, 'doubt_session_booking_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
