<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\DutyScheduleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DutySchedule extends Model
{
    /** @use HasFactory<DutyScheduleFactory> */
    use HasFactory;

    protected $table = 'duty_schedule';

    protected $primaryKey = 'schedule_id';

    protected $fillable = [
        'user_id',
        'duty_date',
        'time_start',
        'time_end',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duty_date' => 'date',
        ];
    }

    /**
     * The BFP personnel member assigned to this shift.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The admin who created this shift entry.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to shifts that cover a given point in time.
     *
     * `whereDate()` is used for the date column because SQLite stores Laravel
     * date-casted values as full datetime strings ("Y-m-d H:i:s"), so a plain
     * equality check against "Y-m-d" would never match. Laravel's `whereDate()`
     * wraps the column in the appropriate DATE() function for each driver.
     * Time columns are stored as plain "H:i:s" strings on both SQLite and MySQL,
     * so ordinary string comparisons work correctly across both drivers.
     *
     * Shifts can be same-day (Morning: 06:00:00–18:00:00, time_start <
     * time_end) or overnight (Night: 18:00:00–06:00:00, time_start >
     * time_end because it crosses midnight). Those two shapes need
     * different logic, so `time_start <=> time_end` is used to tell
     * them apart, and an overnight shift is checked across BOTH the
     * day it starts and the day it ends.
     *
     * @param  Builder<DutySchedule>  $query
     * @return Builder<DutySchedule>
     */
    public function scopeOnDutyAt(Builder $query, CarbonInterface $moment): Builder
    {
        $today = $moment->toDateString();
        $yesterday = $moment->copy()->subDay()->toDateString();
        $time = $moment->format('H:i:s'); // e.g. "14:30:00"

        return $query->where(function (Builder $q) use ($today, $yesterday, $time) {
            // Same-day shift (e.g. Morning 06:00:00–18:00:00): a plain
            // "now falls between start and end" check on today's rows.
            $q->where(function (Builder $sameDay) use ($today, $time) {
                $sameDay->whereDate('duty_date', $today)
                    ->whereColumn('time_start', '<=', 'time_end')
                    ->where('time_start', '<=', $time)
                    ->where('time_end', '>', $time);
            })
                // Overnight shift that started today (e.g. Night
                // 18:00:00 onward): time_start > time_end is what marks
                // a row as crossing midnight. "Now" just needs to be at
                // or after the start time — the shift hasn't reached
                // tomorrow's end time yet by definition.
                ->orWhere(function (Builder $overnightStartedToday) use ($today, $time) {
                    $overnightStartedToday->whereDate('duty_date', $today)
                        ->whereColumn('time_start', '>', 'time_end')
                        ->where('time_start', '<=', $time);
                })
                // The other half of an overnight shift: it started
                // yesterday (duty_date = yesterday) and "now" is still
                // before its end time today, e.g. it's 3 AM and
                // yesterday's Night shift doesn't end until 6 AM.
                ->orWhere(function (Builder $overnightStartedYesterday) use ($yesterday, $time) {
                    $overnightStartedYesterday->whereDate('duty_date', $yesterday)
                        ->whereColumn('time_start', '>', 'time_end')
                        ->where('time_end', '>', $time);
                });
        });
    }
}
