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
        $message = 'Your registration for '.$this->registration->webinar->title.' is confirmed.';
        if ($this->registration->coupon_code) {
            $message .= ' Coupon '.$this->registration->coupon_code.' saved ₹'.number_format((float) $this->registration->discount_amount, 2).'; final amount ₹'.number_format((float) $this->registration->amount, 2).'.';
        }

        return [
            'type' => 'webinar_registration',
            'title' => 'Webinar registration confirmed',
            'message' => $message,
            'webinar_id' => $this->registration->webinar_id,
            'registration_id' => $this->registration->id,
        ];
    }
    public function toArray(object $notifiable): array { return $this->toDatabase($notifiable); }
}
