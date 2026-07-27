<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoubtSessionBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_reference' => $this->booking_reference,

            'topic' => $this->topic,
            'doubt' => $this->doubt,

            'duration_minutes' => (int) $this->duration_minutes,

            'price' => [
                'amount' => number_format(
                    (float) $this->amount,
                    2,
                    '.',
                    ''
                ),
                'amount_subunits' => (int) round(
                    ((float) $this->amount) * 100
                ),
                'currency' => $this->currency,
                'is_free' => (bool) $this->is_free,
            ],

            'payment' => [
                'status' => $this->payment_status,
                'is_paid' => $this->isPaid(),
                'required' => $this->requiresPayment(),
                'paid_at' => $this->paid_at?->toISOString(),
            ],

            'booking_status' => $this->booking_status,

            'schedule' => [
                'scheduled_at' => $this->scheduled_at
                    ?->toISOString(),

                'meeting_link' => $this->meeting_link,
            ],

            'academic_year' => $this->academicYear
                ? [
                    'id' => $this->academicYear->id,
                    'name' => $this->academicYear->name,
                    'slug' => $this->academicYear->slug,
                ]
                : null,

            'subject' => $this->subject
                ? [
                    'id' => $this->subject->id,
                    'name' => $this->subject->name,
                    'slug' => $this->subject->slug,
                ]
                : null,

            'created_at' => $this->created_at
                ?->toISOString(),

            'updated_at' => $this->updated_at
                ?->toISOString(),
        ];
    }
}
