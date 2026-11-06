<?php

return [
    'group_policy_version' => '1.2',
    'group_policy' => [
        'Role of the Student Representative' => [
            ['label' => null, 'text' => 'You are booking a shared session on behalf of your batchmates. You must collect their specific doubts beforehand and enter them into this form so the educator can prepare the flowcharts and materials.'],
        ],
        'Pricing Structure' => [
            ['label' => null, 'text' => 'The session fee is based on the current hourly rate displayed on the booking page. This base rate covers a group of up to 10 students. If your group exceeds 10 members, an applicable additional fee per extra student will be automatically calculated at checkout. (Note: If the session is currently marked as "Free", no charges will apply).'],
        ],
        'Payment Collection' => [
            ['label' => null, 'text' => 'For paid sessions, the platform checkout does not split the bill. You are responsible for collecting individual shares from your batchmates privately and paying the combined total upfront to secure the booking.'],
        ],
        'Scheduling & Confirmation' => [
            ['label' => null, 'text' => 'Submitting your 3 preferred time slots helps us organize the schedule. The final session date and time are not locked until officially confirmed by the admin via WhatsApp.'],
        ],
        'Invitations & Access' => [
            ['label' => null, 'text' => 'You must provide accurate email addresses for all attendees. Once confirmed, each participant will receive their own direct invitation link to join the session.'],
        ],
        'Refund Policy (For Paid Sessions)' => [
            ['label' => null, 'text' => 'Refunds for individual dropouts are strictly limited to groups larger than 10. To process a dropout refund, the Student Representative must submit the cancellation request by 9:00 PM the day before the scheduled session. There are no partial refunds for groups of 10 or fewer.'],
        ],
    ],
    'group_guidelines' => [
        'Definitions' => [
            ['label' => 'Instructor:', 'text' => 'The Physiosource expert leading and teaching the session.'],
            ['label' => 'Student:', 'text' => 'Any participant attending the class.'],
            ['label' => 'Student Organizer:', 'text' => 'The designated group leader responsible for booking the class, paying the upfront fee, collecting doubts from the group, and handling all WhatsApp communication with the Instructor.'],
        ],
        'How It Works (Booking & Scheduling)' => [
            ['label' => 'Step 1: Assign a Student Organizer', 'text' => 'Gather your peers and choose one student to act as the Student Organizer.'],
            ['label' => 'Step 2: Book & Pay', 'text' => 'The Organizer selects the topic, enters the total headcount, and provides the exact email addresses of all attending Students on the Physiosource website. Payment for the entire group is collected upfront from the Organizer during this step.'],
            ['label' => 'Step 3: Schedule via WhatsApp', 'text' => 'Once booked, our Instructor will reach out to the Organizer on WhatsApp to confirm a mutual date and time.'],
            ['label' => 'Step 4: Collect & Share Doubts', 'text' => 'The Student Organizer should ask their group for any specific questions beforehand, compile them, and send them to the Instructor via WhatsApp by 9:00 PM the day before the session. This allows the Instructor to prepare targeted explanations. Impromptu questions are still welcome during the live class; if they require extra preparation, they will be deferred to a future session.'],
            ['label' => 'Step 5: Join the Class', 'text' => 'Attendees will receive a secure meeting link. Links are non-transferable, and only the registered email addresses provided by the Organizer will be admitted to the room.'],
        ],
        'Important' => [
            ['label' => null, 'text' => 'Do not make the description or doubt-entry field mandatory just because the guidelines ask the Organizer to collect doubts.'],
            ['label' => null, 'text' => 'Students may share their doubts later through WhatsApp.'],
            ['label' => null, 'text' => 'Keep the guidelines informational and do not introduce additional form validation.'],
        ],
    ],
];
