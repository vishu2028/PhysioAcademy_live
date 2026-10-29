<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopicRequest extends Model
{
    protected $fillable = [
        'user_id',
        'topic_name',
        'subject_id',
        'academic_year_id',
        'semester_id',
        'topic_id',
        'description',
        'priority',
        'status',
        'admin_note',
        'topic_link',
        'submission_token',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';

    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function getSafeTopicLinkAttribute(): ?string
    {
        if (! is_string($this->topic_link) || $this->topic_link === '') {
            return null;
        }

        $scheme = strtolower((string) parse_url($this->topic_link, PHP_URL_SCHEME));

        return filter_var($this->topic_link, FILTER_VALIDATE_URL)
            && in_array($scheme, ['http', 'https'], true)
            ? $this->topic_link
            : null;
    }
}
