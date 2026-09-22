<?php

use App\Support\DateRange;
use Illuminate\Support\Carbon;

// ─── resolve() ───────────────────────────────────────────────────────────────

test('this_month resolves to start/end of current month', function () {
    $now = Carbon::create(2026, 6, 15, 12, 0, 0);

    [$start, $end] = DateRange::resolve('this_month', '', '', $now);

    expect($start->toDateString())->toBe('2026-06-01');
    expect($end->toDateString())->toBe('2026-06-30');
});

test('last_3_months covers the three most recent calendar months', function () {
    $now = Carbon::create(2026, 9, 20, 12, 0, 0);

    [$start, $end] = DateRange::resolve('last_3_months', '', '', $now);

    // subMonths(2)->startOfMonth from Sep = start of July
    expect($start->toDateString())->toBe('2026-07-01');
    // endOfMonth of current month = end of Sep
    expect($end->toDateString())->toBe('2026-09-30');
});

test('this_year resolves to start of year to end of today', function () {
    $now = Carbon::create(2026, 6, 15, 23, 59, 59);

    [$start, $end] = DateRange::resolve('this_year', '', '', $now);

    expect($start->toDateString())->toBe('2026-01-01');
    expect($end->toDateString())->toBe('2026-06-15');
});

test('custom with valid dates uses start-of-day / end-of-day bounds', function () {
    $now = Carbon::create(2026, 9, 20);

    [$start, $end] = DateRange::resolve('custom', '2026-03-01', '2026-03-31', $now);

    expect($start->toDateString())->toBe('2026-03-01');
    expect($start->format('H:i:s'))->toBe('00:00:00');

    expect($end->toDateString())->toBe('2026-03-31');
    expect($end->format('H:i:s'))->toBe('23:59:59');
});

test('custom with empty date_from falls back to start of year', function () {
    $now = Carbon::create(2026, 9, 20);

    [$start] = DateRange::resolve('custom', '', '2026-03-31', $now);

    expect($start->toDateString())->toBe('2026-01-01');
});

test('custom with empty date_to falls back to end of today', function () {
    $now = Carbon::create(2026, 9, 20);

    [, $end] = DateRange::resolve('custom', '2026-01-01', '', $now);

    expect($end->toDateString())->toBe('2026-09-20');
});

test('unknown period falls back to this_year', function () {
    $now = Carbon::create(2026, 6, 15);

    [$start, $end] = DateRange::resolve('garbage', '', '', $now);

    expect($start->toDateString())->toBe('2026-01-01');
    expect($end->toDateString())->toBe('2026-06-15');
});

test('invalid custom dates do not throw and fall back gracefully', function () {
    $now = Carbon::create(2026, 6, 15);

    [$start, $end] = DateRange::resolve('custom', 'not-a-date', 'also-bad', $now);

    // Falls back to start-of-year and end-of-today
    expect($start->toDateString())->toBe('2026-01-01');
    expect($end->toDateString())->toBe('2026-06-15');
});

// ─── label() ─────────────────────────────────────────────────────────────────

test('this_month label shows month and year', function () {
    $now = Carbon::create(2026, 6, 15);
    [$start, $end] = DateRange::resolve('this_month', '', '', $now);

    expect(DateRange::label('this_month', $start, $end, $now))->toBe('June 2026');
});

test('last_3_months label shows abbreviated range', function () {
    $now = Carbon::create(2026, 9, 20);
    [$start, $end] = DateRange::resolve('last_3_months', '', '', $now);

    expect(DateRange::label('last_3_months', $start, $end, $now))->toBe('Jul 2026 – Sep 2026');
});

test('this_year label shows abbreviated span for multi-month year', function () {
    $now = Carbon::create(2026, 6, 15);
    [$start, $end] = DateRange::resolve('this_year', '', '', $now);

    expect(DateRange::label('this_year', $start, $end, $now))->toBe('Jan – Jun 2026');
});

test('this_year label in January shows full month name', function () {
    $now = Carbon::create(2026, 1, 31);
    [$start, $end] = DateRange::resolve('this_year', '', '', $now);

    expect(DateRange::label('this_year', $start, $end, $now))->toBe('January 2026');
});
