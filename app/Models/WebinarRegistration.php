<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class WebinarRegistration extends Model
{
    public const PAYMENT_NOT_REQUIRED='not_required', PAYMENT_PENDING='pending', PAYMENT_PAID='paid', PAYMENT_FAILED='failed';
    public const STATUS_PENDING='pending', STATUS_CONFIRMED='confirmed', STATUS_CANCELLED='cancelled';
    protected $fillable = ['webinar_id','user_id','student_name','student_email','registration_reference','amount','currency','is_free','payment_status','registration_status','razorpay_order_id','razorpay_payment_id','razorpay_signature','payment_error','paid_at','joined_at','attendance_status'];
    protected $hidden = ['razorpay_signature'];
    protected function casts(): array { return ['amount'=>'decimal:2','is_free'=>'boolean','paid_at'=>'datetime','joined_at'=>'datetime']; }
    public function webinar(): BelongsTo { return $this->belongsTo(Webinar::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function isPaid(): bool { return $this->payment_status === self::PAYMENT_PAID; }
}
