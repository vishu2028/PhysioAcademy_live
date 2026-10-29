<?php
namespace App\Notifications;
use App\Models\WebinarRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
class WebinarRegistrationUpdated extends Notification
{
    use Queueable;
    public function __construct(public readonly WebinarRegistration $registration) {}
    public function via(object $notifiable): array { return ['database']; }
    public function toDatabase(object $notifiable): array { return ['type'=>'webinar_registration','title'=>'Webinar registration confirmed','message'=>'Your registration for '.$this->registration->webinar->title.' is confirmed.','webinar_id'=>$this->registration->webinar_id,'registration_id'=>$this->registration->id]; }
    public function toArray(object $notifiable): array { return $this->toDatabase($notifiable); }
}
