<?php

namespace App\Support;

use App\Enums\DutyGroup;
use App\Models\DutyOverride;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class DutyRotation
{
    /**
     * Compute the week start for a given moment, taking into account
     * the configured Monday changeover time.
     */
    public static function weekStartFor(CarbonInterface $moment): CarbonImmutable
    {
        $immutable = CarbonImmutable::instance($moment);
        $changeover = (string) config('duty.rotation.changeover_time', '00:00');
        [$hours, $minutes] = array_pad(array_map('intval', explode(':', $changeover)), 2, 0);

        return $immutable
            ->subHours($hours)
            ->subMinutes($minutes)
            ->startOfWeek(CarbonInterface::MONDAY)
            ->startOfDay();
    }

    /**
     * Calculate which platoon (A or B) is active for the given moment based
     * on the configured anchor week start and anchor group.
     */
    public static function activeGroupFor(CarbonInterface $moment): DutyGroup
    {
        $anchorDateStr = (string) config('duty.rotation.anchor_week_start', '2026-10-12');
        $anchorWeekStart = CarbonImmutable::parse($anchorDateStr)->startOfDay();
        $targetWeekStart = self::weekStartFor($moment);

        $days = $anchorWeekStart->diffInDays($targetWeekStart, false);
        $weeks = (int) floor($days / 7);

        $anchorGroupVal = (string) config('duty.rotation.anchor_group', 'A');
        $anchorGroup = DutyGroup::from($anchorGroupVal);
        $otherGroup = $anchorGroup === DutyGroup::A ? DutyGroup::B : DutyGroup::A;

        return ($weeks % 2 === 0) ? $anchorGroup : $otherGroup;
    }

    /**
     * Determine the effective duty group for a user during a given week.
     * If an override covering that week exists, it takes precedence.
     *
     * @param  Collection<int, DutyOverride>|null  $overrides
     */
    public static function effectiveGroupFor(User $user, CarbonInterface $weekStart, ?Collection $overrides = null): ?DutyGroup
    {
        $weekDate = $weekStart->toDateString();

        if ($overrides !== null) {
            /** @var DutyOverride|null $override */
            $override = $overrides
                ->where('user_id', $user->getKey())
                ->first(function (DutyOverride $item) use ($weekDate) {
                    $startsOn = $item->starts_on instanceof CarbonInterface
                        ? $item->starts_on->toDateString()
                        : (string) $item->starts_on;
                    $endsOn = $item->ends_on instanceof CarbonInterface
                        ? $item->ends_on->toDateString()
                        : (string) $item->ends_on;

                    return $startsOn <= $weekDate && $endsOn >= $weekDate;
                });
        } else {
            $override = DutyOverride::query()
                ->where('user_id', $user->getKey())
                ->coveringWeek($weekStart)
                ->first();
        }

        if ($override !== null) {
            return $override->duty_group;
        }

        return $user->personnelDetails?->duty_group;
    }

    /**
     * Determine whether the given user is currently on duty at the given moment.
     * Regular members are always on duty; Detached or unassigned members are never on duty;
     * Platoon A/B members are on duty when their group is the active rotation group.
     */
    public static function isOnDuty(User $user, CarbonInterface $moment): bool
    {
        $weekStart = self::weekStartFor($moment);
        $effectiveGroup = self::effectiveGroupFor($user, $weekStart);

        if ($effectiveGroup === DutyGroup::Regular) {
            return true;
        }

        if ($effectiveGroup === DutyGroup::Detached || $effectiveGroup === null) {
            return false;
        }

        return $effectiveGroup === self::activeGroupFor($moment);
    }

    /**
     * Generate rotation schedule for the next N weeks.
     *
     * @return array<int, array{week_start: string, week_end: string, group: string}>
     */
    public static function upcomingWeeks(CarbonInterface $from, int $count = 8): array
    {
        $baseWeekStart = self::weekStartFor($from);
        $weeks = [];

        for ($i = 0; $i < $count; $i++) {
            $start = $baseWeekStart->addWeeks($i);
            $end = $start->endOfWeek(CarbonInterface::SUNDAY)->startOfDay();

            $weeks[] = [
                'week_start' => $start->toDateString(),
                'week_end' => $end->toDateString(),
                'group' => self::activeGroupFor($start)->value,
            ];
        }

        return $weeks;
    }
}
