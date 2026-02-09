<?php

namespace Modules\TripManagement\Lib;

final class TripStatusTransition
{
    /**
     * Columns used in trip_request_statuses/trip_status tables.
     * These are commonly used as "to" values in the repository code.
     */
    public static function columns(): array
    {
        return [
            'pending',
            'accepted',
            'ongoing',
            'completed',
            'cancelled',
            'returning',
            'returned',
        ];
    }

    public static function isValidColumn(?string $to): bool
    {
        if ($to === null) return false;
        $to = strtolower(trim($to));
        return in_array($to, self::columns(), true);
    }

    /**
     * Keep permissive while you’re building, but still sane.
     * If something unknown comes in, allow it instead of 500’ing.
     */
    public static function canTransition(?string $from, ?string $to): bool
    {
        $from = strtolower(trim((string) $from));
        $to   = strtolower(trim((string) $to));

        if ($to === '') return false;
        if ($from === '' || $from === $to) return true;

        $map = [
            'pending'   => ['accepted', 'cancelled'],
            'accepted'  => ['ongoing', 'cancelled'],
            'ongoing'   => ['completed', 'cancelled', 'returning'],
            'returning' => ['returned', 'cancelled'],
            'returned'  => [],
            'completed' => [],
            'cancelled' => [],
        ];

        // If repo sends a status we didn’t map yet, don’t crash the flow.
        if (!array_key_exists($from, $map)) return true;

        return in_array($to, $map[$from], true);
    }
}
