<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DoubtSessionFollowUp extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ANSWERED = 'answered';

    public const ROLE_USER = 'user';

    public const ROLE_ADMIN = 'admin';

    protected $fillable = [
        'doubt_session_booking_id',
        'user_id',
        'parent_id',
        'thread_id',
        'author_role',
        'submission_key',
        'is_hidden',
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
            'is_hidden' => 'boolean',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(DoubtSessionBooking::class, 'doubt_session_booking_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('created_at')
            ->orderBy('id')
            ->with('user');
    }

    public function threadMessages(): HasMany
    {
        return $this->hasMany(self::class, 'thread_id')
            ->whereNotNull('parent_id')
            ->orderBy('created_at')
            ->orderBy('id')
            ->with('user');
    }

    public function answeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'answered_by');
    }
}
