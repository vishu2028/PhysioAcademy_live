<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoubtSessionFollowUp extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ANSWERED = 'answered';

    protected $fillable = [
        'doubt_session_booking_id',
        'question',
        'answer',
        'status',
        'answered_by',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'answered_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(DoubtSessionBooking::class, 'doubt_session_booking_id');
    }

    public function answeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'answered_by');
    }
}
