<?php
namespace App\Notifications;
use App\Models\WebinarRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Traits\EmailNotification;
class WebinarRegistrationUpdated extends Notification
{
    use Queueable;
    use EmailNotification;
    public function __construct(public readonly WebinarRegistration $registration) {}
    public function toDatabase(object $notifiable): array
    {
        $status = $this->registration->registration_status;
        $title = match ($status) {
            WebinarRegistration::STATUS_CANCELLED => 'Webinar registration cancelled',
            WebinarRegistration::STATUS_PENDING => 'Webinar registration updated',
            default => 'Webinar registration confirmed',
        };
        $message = match ($status) {
            WebinarRegistration::STATUS_CANCELLED => 'Your registration for '.$this->registration->webinar->title.' has been cancelled.',
            WebinarRegistration::STATUS_PENDING => 'Your registration for '.$this->registration->webinar->title.' is pending.',
            default => 'Your registration for '.$this->registration->webinar->title.' is confirmed.',
        };
        if ($status === WebinarRegistration::STATUS_CONFIRMED && $this->registration->coupon_code) {
            $message .= ' Coupon '.$this->registration->coupon_code.' saved ₹'.number_format((float) $this->registration->discount_amount, 2).'; final amount ₹'.number_format((float) $this->registration->amount, 2).'.';
        }

        return [
            'type' => 'webinar_registration',
            'title' => $title,
            'message' => $message,
            'webinar_id' => $this->registration->webinar_id,
            'registration_id' => $this->registration->id,
        ];
    }
    public function toArray(object $notifiable): array { return $this->toDatabase($notifiable); }

    public function emailDetails(object $notifiable): array
    {
        return array_filter([
            'Webinar' => $this->registration->webinar->title,
            'Registration reference' => $this->registration->registration_reference,
            'Starts' => $this->registration->webinar->starts_at?->format('M j, Y g:i A'),
            'Timezone' => $this->registration->webinar->timezone,
            'Meeting link' => $this->registration->webinar->meeting_link,
        ], fn ($value): bool => filled($value));
    }
}
