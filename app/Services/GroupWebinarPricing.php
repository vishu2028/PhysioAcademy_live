<?php

namespace App\Services;

class GroupWebinarPricing
{
    public static function baseRate(): float
    {
        return max(0, (float) get_setting('webinar_group_price_per_person', 100));
    }

    public static function isFree(): bool
    {
        return (string) get_setting('webinar_group_is_free', '0') === '1';
    }

    public static function totalFor(int $participantCount): int|float
    {
        if (self::isFree()) {
            return 0;
        }

        $count = max(1, $participantCount);
        $baseRate = self::baseRate();
        $additionalRate = max(0, (float) get_setting('webinar_group_additional_price_per_person', 10));

        $total = $baseRate + ($count > 10 ? ($count - 10) * $additionalRate : 0);

        return floor($total) === $total ? (int) $total : $total;
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
        $totalPaise = (int) round(self::totalFor($count) * 100);
        $baseShare = intdiv($totalPaise, $count);
        $remainder = $totalPaise % $count;

        return array_map(
            static fn (int $index): float => ($baseShare + ($index === 0 ? $remainder : 0)) / 100,
            range(0, $count - 1)
        );
    }
}
