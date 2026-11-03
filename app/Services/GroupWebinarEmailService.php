<?php

namespace App\Services;

use App\Jobs\SendGroupWebinarEmail;
use App\Models\GroupWebinarEmailDelivery;
use App\Models\GroupWebinarParticipant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class GroupWebinarEmailService
{
    public function send(
        GroupWebinarParticipant $participant,
        string $type,
        string $subject,
        string $title,
        string $message,
        ?string $actionUrl = null
    ): void {
        $key = hash('sha256', implode('|', [
            $participant->id,
            $type,
            $participant->email,
            $subject,
            $message,
            $actionUrl ?? '',
        ]));

        $delivery = GroupWebinarEmailDelivery::firstOrCreate(
            ['delivery_key' => $key],
            [
                'participant_id' => $participant->id,
                'notification_type' => $type,
                'recipient' => $participant->email,
                'subject' => $subject,
                'title' => $title,
                'message' => $message,
                'action_url' => $actionUrl,
            ]
        );

        $shouldDispatch = DB::transaction(function () use ($delivery): bool {
            $locked = GroupWebinarEmailDelivery::query()->lockForUpdate()->findOrFail($delivery->id);
            if ($locked->sent_at || ($locked->queued_at && ! $locked->failed_at)) {
                return false;
            }

            $locked->forceFill([
                'queued_at' => now(),
                'failed_at' => null,
                'failure_message' => null,
            ])->save();

            return true;
        });

        if (! $shouldDispatch) {
            return;
        }

        try {
            SendGroupWebinarEmail::dispatch($delivery->id);
        } catch (Throwable $exception) {
            $delivery->forceFill([
                'queued_at' => null,
                'failed_at' => now(),
                'failure_message' => 'Email could not be queued ('.$exception::class.').',
            ])->save();
            throw $exception;
        }
    }

    public function sendInvitation(GroupWebinarParticipant $participant, string $token): void
    {
        $request = $participant->request;
        $paymentLine = $request->is_free
            ? 'This webinar is free; no payment is required.'
            : 'The group total is ₹'.number_format((float) $request->total_amount, 2).'. Your allocated amount is ₹'.number_format((float) $participant->amount_due, 2).'. Each participant pays separately.';
        $message = implode("\n\n", [
            'You have been invited to a group webinar.',
            'Topic: '.$request->topic,
            'Organizer: '.$request->organizer_name.' ('.$request->organizer_email.')',
            'Reference: '.$request->reference,
            'Participant: '.$participant->email,
            'Payment status: '.ucwords(str_replace('_', ' ', $participant->payment_status)).'.',
            $paymentLine,
            'Open your secure invitation link to review the request and complete your own payment if required.',
            'The link expires on '.$participant->invitation_expires_at?->format('d M Y, h:i A').'.',
        ]);

        $this->send(
            $participant,
            'invitation',
            'Your group webinar invitation',
            'Group webinar invitation',
            $message,
            route('group-webinars.invitation', ['token' => $token])
        );
    }

    public function sendPaymentConfirmation(GroupWebinarParticipant $participant): void
    {
        $this->notifyInApp(
            $participant,
            'payment_confirmation',
            'Group webinar payment confirmed',
            'Your payment of ₹'.number_format((float) $participant->amount_due, 2).' for "'.$participant->request->topic.'" has been verified.'
        );

        $this->send(
            $participant,
            'payment_confirmation',
            'Group webinar payment confirmed',
            'Payment confirmed',
            'Your payment of ₹'.number_format((float) $participant->amount_due, 2).' for "'.$participant->request->topic.'" ('.$participant->request->reference.') has been verified.'
        );
    }

    public function sendGroupBookingConfirmation(GroupWebinarParticipant $participant, string $token): void
    {
        $request = $participant->request;
        $details = [
            'You are included in a Group Webinar booking.',
            'Topic: '.$request->topic,
            'Organizer: '.$request->organizer_name.' ('.$request->organizer_email.')',
            'Reference: '.$request->reference,
            'Participants: '.$request->participant_count,
            'The organizer has paid the group total of ₹'.number_format((float) $request->total_amount, 2).'. No separate participant payment is required.',
        ];

        if ($request->scheduled_at) {
            $details[] = 'Scheduled: '.$request->scheduled_at->format('d M Y, h:i A');
        }
        if ($request->admin_response) {
            $details[] = 'Message from the admin: '.$request->admin_response;
        }

        $this->send(
            $participant,
            'group_booking_confirmation',
            'Group webinar booking confirmed',
            'You are included in a Group Webinar booking',
            implode("\n\n", $details),
            route('group-webinars.invitation', ['token' => $token])
        );
    }

    public function sendGroupOrganizerPaymentLink(GroupWebinarParticipant $participant, string $token): void
    {
        $request = $participant->request;

        $this->send(
            $participant,
            'group_payment_link',
            'Complete your Group Webinar booking',
            'Group Webinar payment required',
            implode("\n\n", [
                'You are the Organizer for a Group Webinar booking.',
                'Topic: '.$request->topic,
                'Reference: '.$request->reference,
                'Participants: '.$request->participant_count,
                'Group total: ₹'.number_format((float) $request->total_amount, 2).'. The Organizer completes one payment for everyone included.',
            ]),
            route('group-webinars.invitation', ['token' => $token])
        );
    }

    public function sendRequestUpdate(GroupWebinarParticipant $participant, string $message, string $eventKey): void
    {
        $request = $participant->request;
        $this->notifyInApp($participant, $eventKey, 'Group webinar update', $message);
        $details = [
            $message,
            'Topic: '.$request->topic,
            'Reference: '.$request->reference,
        ];
        if ($request->scheduled_at) {
            $details[] = 'Scheduled: '.$request->scheduled_at->format('d M Y, h:i A');
        }
        if (
            $eventKey !== 'cancellation'
            && $request->meeting_link
            && in_array($participant->payment_status, [
                GroupWebinarParticipant::PAYMENT_PAID,
                GroupWebinarParticipant::PAYMENT_NOT_REQUIRED,
            ], true)
        ) {
            $details[] = 'Meeting link: '.$request->meeting_link;
        }
        if ($request->admin_response) {
            $details[] = 'Message from the admin: '.$request->admin_response;
        }

        $this->send(
            $participant,
            $eventKey,
            'Group webinar update',
            'Group webinar update',
            implode("\n\n", $details)
        );
    }

    public function sendAdjustmentUpdate(GroupWebinarParticipant $participant, string $message, string $eventKey): void
    {
        $this->send(
            $participant,
            'adjustment_'.$eventKey,
            'Group webinar request update',
            'Group webinar request update',
            implode("\n\n", [
                $message,
                'Topic: '.$participant->request->topic,
                'Reference: '.$participant->request->reference,
            ]),
            route('webinars.my').'#group-booking-'.$participant->request->id
        );
    }

    public function notifyInApp(
        GroupWebinarParticipant $participant,
        string $eventKey,
        string $title,
        string $message
    ): void {
        try {
            $participant->loadMissing('request', 'user');
            $user = $participant->user;
            if (! $user) {
                return;
            }

            $notificationKey = hash('sha256', implode('|', [
                $participant->id,
                $eventKey,
                $title,
                $message,
            ]));

            if ($user->notifications()->where('data->notification_key', $notificationKey)->exists()) {
                return;
            }

            $user->notifications()->create([
                'id' => (string) Str::uuid(),
                'type' => self::class,
                'data' => [
                    'type' => 'group_webinar',
                    'title' => $title,
                    'message' => $message,
                    'group_webinar_request_id' => $participant->request->id,
                    'notification_key' => $notificationKey,
                ],
            ]);
        } catch (Throwable $exception) {
            Log::error('Unable to store an in-app group webinar notification.', [
                'participant_id' => $participant->id,
                'exception' => $exception::class,
            ]);
        }
    }
}
