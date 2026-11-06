<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class PreferredSlotService
{
    /**
     * @param  array<int, array{date?: string, time?: string}>  $slots
     * @return array<int, array{date: string, time: string, label: string}>
     */
    public static function validate(array $slots): array
    {
        if (count($slots) !== 3) {
            throw ValidationException::withMessages([
                'preferred_slots' => 'Please provide exactly 3 preferred date and time slots.',
            ]);
        }

        $normalized = [];
        $seen = [];

        foreach (array_values($slots) as $index => $slot) {
            $number = $index + 1;
            $date = trim((string) ($slot['date'] ?? ''));
            $time = trim((string) ($slot['time'] ?? ''));

            try {
                $dateTime = Carbon::createFromFormat(
                    '!Y-m-d H:i',
                    "{$date} {$time}",
                    config('app.timezone')
                );
            } catch (\Throwable) {
                throw ValidationException::withMessages([
                    "preferred_slots.{$index}" => "Slot {$number} must include a valid date and time.",
                ]);
            }

            if (! $dateTime || $dateTime->format('Y-m-d') !== $date || $dateTime->format('H:i') !== $time) {
                throw ValidationException::withMessages([
                    "preferred_slots.{$index}" => "Slot {$number} must include a valid date and time.",
                ]);
            }

            $key = $dateTime->format('Y-m-d H:i');
            if (isset($seen[$key])) {
                throw ValidationException::withMessages([
                    "preferred_slots.{$index}" => 'Preferred date and time slots must be different.',
                ]);
            }

            if (! $dateTime->isFuture()) {
                throw ValidationException::withMessages([
                    "preferred_slots.{$index}" => "Slot {$number} must be in the future.",
                ]);
            }

            $seen[$key] = true;
            $normalized[] = [
                'date' => $dateTime->format('Y-m-d'),
                'time' => $dateTime->format('H:i'),
                'label' => $dateTime->format('d M Y, h:i A'),
            ];
        }

        return $normalized;
    }

    /**
     * Validate any number of independently optional slots.
     *
     * @param  array<int, array{date?: string|null, time?: string|null}>  $slots
     * @return array<int, array{date: string, time: string, label: string}>
     */
    public static function validateOptional(array $slots): array
    {
        $provided = [];
        foreach (array_values($slots) as $index => $slot) {
            $date = trim((string) ($slot['date'] ?? ''));
            $time = trim((string) ($slot['time'] ?? ''));
            if ($date === '' || $time === '') {
                continue;
            }
            $provided[] = ['date' => $date, 'time' => $time, 'index' => $index];
        }

        $normalized = [];
        $seen = [];
        foreach ($provided as $slot) {
            try {
                $dateTime = Carbon::createFromFormat(
                    '!Y-m-d H:i',
                    "{$slot['date']} {$slot['time']}",
                    config('app.timezone')
                );
            } catch (\Throwable) {
                throw ValidationException::withMessages([
                    "preferred_slots.{$slot['index']}" => 'The preferred slot must include a valid date and time.',
                ]);
            }

            if (! $dateTime
                || $dateTime->format('Y-m-d') !== $slot['date']
                || $dateTime->format('H:i') !== $slot['time']) {
                throw ValidationException::withMessages([
                    "preferred_slots.{$slot['index']}" => 'The preferred slot must include a valid date and time.',
                ]);
            }

            $key = $dateTime->format('Y-m-d H:i');
            if (isset($seen[$key])) {
                throw ValidationException::withMessages([
                    "preferred_slots.{$slot['index']}" => 'Preferred date and time slots must be different.',
                ]);
            }
            if (! $dateTime->isFuture()) {
                throw ValidationException::withMessages([
                    "preferred_slots.{$slot['index']}" => 'The preferred slot must be in the future.',
                ]);
            }

            $seen[$key] = true;
            $normalized[] = [
                'date' => $dateTime->format('Y-m-d'),
                'time' => $dateTime->format('H:i'),
                'label' => $dateTime->format('d M Y, h:i A'),
            ];
        }

        return $normalized;
    }

    /**
     * @param  array<int, array{label?: string, date?: string, time?: string}>|null  $slots
     */
    public static function summary(?array $slots): string
    {
        return collect($slots ?? [])
            ->map(fn (array $slot): string => (string) ($slot['label'] ?? trim(($slot['date'] ?? '').' '.($slot['time'] ?? ''))))
            ->filter()
            ->implode(', ');
    }
}
