<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Period-to-date-range resolution shared by AnalyticsController and
 * FireMapController so both always apply the same date arithmetic.
 */
class DateRange
{
    /**
     * Resolve a [start, end] Carbon pair from a period preset or custom dates.
     *
     * Unknown periods fall back to 'this_year'. Invalid or unparseable custom
     * date strings fall back to start-of-year / end-of-day respectively.
     *
     * @return array{Carbon, Carbon}
     */
    public static function resolve(
        string $period,
        string $dateFrom,
        string $dateTo,
        CarbonInterface $now,
    ): array {
        return match ($period) {
            'this_month' => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
            ],
            'last_3_months' => [
                $now->copy()->subMonths(2)->startOfMonth(),
                $now->copy()->endOfMonth(),
            ],
            'custom' => [
                $dateFrom !== '' ? self::safeParseStart($dateFrom, $now) : $now->copy()->startOfYear(),
                $dateTo !== '' ? self::safeParseEnd($dateTo, $now) : $now->copy()->endOfDay(),
            ],
            default => [ // this_year
                $now->copy()->startOfYear(),
                $now->copy()->endOfDay(),
            ],
        };
    }

    /**
     * Human-readable label for the resolved range, matching exactly the
     * format Analytics uses on its period filter bar.
     */
    public static function label(
        string $period,
        CarbonInterface $start,
        CarbonInterface $end,
        CarbonInterface $now,
    ): string {
        return match ($period) {
            'this_month' => $now->format('F Y'),
            'last_3_months' => $start->format('M Y').' – '.$end->format('M Y'),
            'custom' => $start->format('M j').' – '.$end->format('M j, Y'),
            default => $now->month === 1
                ? $now->format('F Y')
                : $start->format('M').' – '.$end->format('M Y'),
        };
    }

    /**
     * Parse a date string to start-of-day, falling back to start-of-year on
     * any parse error so invalid custom dates never throw.
     */
    private static function safeParseStart(string $date, CarbonInterface $now): Carbon
    {
        try {
            return Carbon::parse($date)->startOfDay();
        } catch (\Throwable) {
            return Carbon::createFromTimestamp($now->timestamp)->startOfYear();
        }
    }

    /**
     * Parse a date string to end-of-day, falling back to end-of-day on any
     * parse error.
     */
    private static function safeParseEnd(string $date, CarbonInterface $now): Carbon
    {
        try {
            return Carbon::parse($date)->endOfDay();
        } catch (\Throwable) {
            return Carbon::createFromTimestamp($now->timestamp)->endOfDay();
        }
    }
}
