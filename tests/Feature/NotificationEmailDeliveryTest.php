<?php

namespace Tests\Feature;

use App\Mail\UserNotificationMail;
use App\Models\Doubt;
use App\Models\DoubtSessionBooking;
use App\Models\DoubtSessionFollowUp;
use App\Models\NotificationEmailDelivery;
use App\Models\TopicRequest;
use App\Models\User;
use App\Models\Webinar;
use App\Models\WebinarRegistration;
use App\Notifications\Channels\NotificationEmailChannel;
use App\Notifications\DoubtAnswered;
use App\Notifications\DoubtSessionFollowUpAnswered;
use App\Notifications\DoubtSessionStatusUpdated;
use App\Notifications\TopicRequestStatusUpdated;
use App\Notifications\WebinarRegistrationUpdated;
use App\Notifications\WebinarUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class NotificationEmailDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_existing_user_notification_category_is_emailed_without_queueing(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'student@example.test']);
        $booking = new DoubtSessionBooking([
            'booking_reference' => 'DS-123',
            'scheduled_at' => now(),
            'meeting_link' => 'https://meet.example.test/session',
        ]);
        $followUp = new DoubtSessionFollowUp([
            'id' => 17,
            'answer' => 'Try the exercise for one week.',
        ]);
        $followUp->setRelation('booking', $booking);
        $webinar = new Webinar([
            'title' => 'Shoulder rehabilitation',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
            'timezone' => 'Asia/Kolkata',
            'meeting_link' => 'https://meet.example.test/webinar',
        ]);
        $registration = new WebinarRegistration([
            'webinar_id' => 4,
            'registration_reference' => 'WR-123',
            'registration_status' => WebinarRegistration::STATUS_CONFIRMED,
        ]);
        $registration->setRelation('webinar', $webinar);

        $notifications = [
            new DoubtAnswered(new Doubt([
                'topic' => 'shoulder pain',
                'answer' => 'Rest and apply ice for the first 48 hours.',
            ])),
            new DoubtSessionFollowUpAnswered($followUp),
            new DoubtSessionStatusUpdated($booking, 'pending_schedule', 'confirmed'),
            new TopicRequestStatusUpdated(new TopicRequest([
                'topic_name' => 'Gait assessment',
                'status' => TopicRequest::STATUS_RESOLVED,
                'admin_note' => 'The topic is available in the learning library.',
            ])),
            new WebinarUpdated($webinar, 'The webinar schedule has changed.'),
            new WebinarRegistrationUpdated($registration),
        ];

        DB::commit();
        foreach ($notifications as $notification) {
            $user->notify($notification);
        }

        Mail::assertSent(UserNotificationMail::class, 6);
        Mail::assertSent(UserNotificationMail::class, fn (UserNotificationMail $mail): bool => $mail->hasTo($user->email));
        Mail::assertSent(
            UserNotificationMail::class,
            fn (UserNotificationMail $mail): bool => $mail->title === 'Webinar update'
                && $mail->details['Timezone'] === 'Asia/Kolkata'
                && $mail->actionUrl === route('webinars.my')
        );
        Mail::assertSent(
            UserNotificationMail::class,
            fn (UserNotificationMail $mail): bool => ($mail->details['Admin response'] ?? null)
                === 'The topic is available in the learning library.'
        );
        $this->assertDatabaseCount('notifications', 6);
        $this->assertDatabaseCount('notification_email_deliveries', 6);
        $this->assertDatabaseCount('jobs', 0);
        $this->assertSame(
            6,
            NotificationEmailDelivery::query()->whereNotNull('sent_at')->count()
        );
    }

    public function test_database_notifications_without_the_email_trait_are_also_emailed(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'new-user@example.test']);
        $notification = new class extends Notification
        {
            public function via(object $notifiable): array
            {
                return ['database'];
            }

            public function toDatabase(object $notifiable): array
            {
                return [
                    'title' => 'New notification type',
                    'message' => 'This notification does not use the email trait.',
                ];
            }
        };

        DB::commit();
        $user->notify($notification);

        Mail::assertSent(
            UserNotificationMail::class,
            fn (UserNotificationMail $mail): bool => $mail->hasTo($user->email)
                && $mail->title === 'New notification type'
                && $mail->notificationMessage === 'This notification does not use the email trait.'
        );
        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('notification_email_deliveries', [
            'user_id' => $user->id,
            'email' => $user->email,
            'notification_type' => $notification::class,
        ]);
    }

    public function test_same_notification_is_deduplicated_but_identical_new_notifications_are_sent(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $webinar = new Webinar(['title' => 'Neurophysiology']);
        $notification = new WebinarUpdated($webinar, 'The webinar schedule has changed.');
        $notification->id = (string) Str::uuid();

        DB::commit();
        $user->notify($notification);
        app(NotificationEmailChannel::class)->send($user, $notification);
        $user->notify(new WebinarUpdated($webinar, 'The webinar schedule has changed.'));

        Mail::assertSentTimes(UserNotificationMail::class, 2);
        $this->assertDatabaseCount('notifications', 2);
        $this->assertDatabaseCount('notification_email_deliveries', 2);
    }

    public function test_email_failure_is_recorded_without_interrupting_in_app_notification(): void
    {
        Mail::shouldReceive('mailer')
            ->zeroOrMoreTimes()
            ->andThrow(new RuntimeException('Mail transport unavailable'));
        $user = User::factory()->create();
        DB::commit();

        $user->notify(new WebinarUpdated(
            new Webinar(['title' => 'Neurophysiology']),
            'The webinar was cancelled.'
        ));

        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('notification_email_deliveries', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
        $this->assertNotNull(NotificationEmailDelivery::query()->firstOrFail()->failed_at);
    }

    public function test_webinar_cancellation_email_matches_the_notification_status(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $registration = new WebinarRegistration([
            'registration_status' => WebinarRegistration::STATUS_CANCELLED,
        ]);
        $registration->setRelation('webinar', new Webinar(['title' => 'Neurophysiology']));

        DB::commit();
        $user->notify(new WebinarRegistrationUpdated($registration));

        Mail::assertSent(
            UserNotificationMail::class,
            fn (UserNotificationMail $mail): bool => $mail->title === 'Webinar registration cancelled'
                && str_contains($mail->notificationMessage, 'has been cancelled')
        );
    }
}
