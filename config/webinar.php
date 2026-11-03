<?php

return [
    'group_policy_version' => '1.1',
    'group_policy' => [
        'Pricing & Minimums' => [
            ['label' => 'Base Rate:', 'text' => 'The minimum cost for any session is ₹100, which covers up to 10 Students. You can attend solo or bring up to 9 friends for this flat rate.'],
            ['label' => 'Additional Students:', 'text' => 'For groups larger than 10, the cost is an additional ₹10 per Student.'],
        ],
        'Cancellations & Refunds' => [
            ['label' => 'The Revenue Floor:', 'text' => "The ₹100 base fee is non-refundable, as it secures the Instructor's time slot."],
            ['label' => 'Dropout Refunds:', 'text' => 'For groups larger than 10, the Organizer can request a ₹10 refund for individual dropouts.'],
            ['label' => 'Strict 9:00 PM Deadline:', 'text' => 'Dropout notifications and refund requests must be submitted by the Organizer before 9:00 PM the day before the scheduled session. No refunds will be processed for requests made after this cutoff.'],
            ['label' => 'Schedule Mismatches:', 'text' => 'If the Instructor and Organizer absolutely cannot agree on a schedule during the WhatsApp negotiation, the booking will be converted into a session credit that can be applied to a future date or topic.'],
        ],
        'Tardiness Policy' => [
            ['label' => 'Host Delay:', 'text' => 'If the Instructor joins late, the session will be extended to ensure you receive the full promised time. If extending is not possible, a reschedule or refund will be offered.'],
            ['label' => 'Student Delay:', 'text' => 'If Students or the Organizer join late, the session will still end strictly at the originally scheduled time. Time lost due to student tardiness will not be compensated.'],
        ],
        'Technical Failures' => [
            ['label' => 'Host Disconnect:', 'text' => 'If the Instructor loses internet connection for more than 10 minutes, the session will be halted and a free makeup session will be scheduled. Minor disruptions (under 5 minutes) will be compensated by extending the session\'s end time.'],
            ['label' => 'Student Disconnect (Webinar):', 'text' => 'If an individual Student\'s connection drops during a group class, the session will continue for the rest of the group. No partial refunds are issued for individual technical issues.'],
            ['label' => 'Student Disconnect (One-on-One):', 'text' => 'The Instructor will keep the meeting open for 15 minutes. If the Student cannot reconnect, the session is counted as completed.'],
        ],
        'Study Materials & Notes' => [
            ['label' => 'Built-In Materials:', 'text' => 'We teach directly from the Physiosource website using a Canva interactive whiteboard. No separate PDFs or PPT presentations will be circulated.'],
            ['label' => 'Always Accessible:', 'text' => 'All structured notes, flowcharts, and learning materials covered in the session remain permanently accessible on the Physiosource website for your review.'],
        ],
        'Privacy Guarantee' => [
            ['label' => null, 'text' => 'Sessions are a safe space to learn. Physiosource occasionally screen-records sessions to create educational marketing content (such as YouTube Shorts).'],
            ['label' => 'Total Anonymity:', 'text' => 'We exclusively record the Instructor\'s screen and voice. Student names, faces, and voices are strictly anonymized and will never be shared publicly. You can ask questions freely without worrying about your privacy.'],
        ],
    ],
];
