<?php

namespace App\Services;

use App\Jobs\SendGroupWebinarEmail;
use App\Models\GroupWebinarEmailDelivery;
use App\Models\GroupWebinarParticipant;
use Illuminate\Support\Facades\DB;
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
            : 'Your individual amount is ₹'.number_format((float) $participant->amount_due, 2).'. Each participant pays separately.';
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
        $this->send(
            $participant,
            'payment_confirmation',
            'Group webinar payment confirmed',
            'Payment confirmed',
            'Your individual payment of ₹'.number_format((float) $participant->amount_due, 2).' for "'.$participant->request->topic.'" ('.$participant->request->reference.') has been verified.'
        );
    }

    public function sendRequestUpdate(GroupWebinarParticipant $participant, string $message, string $eventKey): void
    {
        $request = $participant->request;
        $details = [
            $message,
            'Topic: '.$request->topic,
            'Reference: '.$request->reference,
        ];
        if ($request->scheduled_at) {
            $details[] = 'Scheduled: '.$request->scheduled_at->format('d M Y, h:i A');
        }
        if ($eventKey !== 'cancellation' && $request->meeting_link) {
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
}
