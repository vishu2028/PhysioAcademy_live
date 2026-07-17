<?php

namespace App\Models;

use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoubtSessionBooking extends Model
{
    use Loggable;

    /*
     * Payment statuses.
     */
    public const PAYMENT_NOT_REQUIRED = 'not_required';
    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_FAILED = 'failed';
    public const PAYMENT_REFUNDED = 'refunded';

    /*
     * Booking statuses.
     */
    public const STATUS_PENDING_PAYMENT = 'pending_payment';
    public const STATUS_PENDING_SCHEDULE = 'pending_schedule';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'booking_reference',
        'user_id',
        'student_name',
        'student_email',
        'phone',
        'academic_year_id',
        'subject_id',
        'topic',
        'doubt',
        'duration_minutes',
        'amount',
        'currency',
        'is_free',
        'payment_status',
        'booking_status',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'payment_error',
        'paid_at',
        'scheduled_at',
        'meeting_link',
        'admin_notes',
        'confirmed_by',
    ];

    protected $hidden = [
        'razorpay_signature',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'amount' => 'decimal:2',
            'is_free' => 'boolean',
            'paid_at' => 'datetime',
            'scheduled_at' => 'datetime',
        ];
    }

    /**
     * Student who created the booking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Selected academic year.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Selected subject.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Admin who confirmed the session schedule.
     */
    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Check whether this booking requires payment.
     */
    public function requiresPayment(): bool
    {
        return ! $this->is_free && (float) $this->amount > 0;
    }

    /**
     * Check whether payment has been completed.
     */
    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    /**
     * Check whether the booking is waiting for admin scheduling.
     */
    public function isPendingSchedule(): bool
    {
        return $this->booking_status === self::STATUS_PENDING_SCHEDULE;
    }
}
