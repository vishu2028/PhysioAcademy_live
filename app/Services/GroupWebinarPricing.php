<?php

namespace App\Services;

class GroupWebinarPricing
{
    public static function totalFor(int $participantCount): int
    {
        return 100 + (max(1, $participantCount) > 10 ? (max(1, $participantCount) - 10) * 10 : 0);
    }

    /**
     * Split the group total in paise so individual invitations sum exactly
     * to the policy price; the organizer covers any rounding remainder.
     *
     * @return list<float>
     */
    public static function sharesFor(int $participantCount): array
    {
        $count = max(1, $participantCount);
        $totalPaise = self::totalFor($count) * 100;
        $baseShare = intdiv($totalPaise, $count);
        $remainder = $totalPaise % $count;

        return array_map(
            static fn (int $index): float => ($baseShare + ($index === 0 ? $remainder : 0)) / 100,
            range(0, $count - 1)
        );
    }
}
