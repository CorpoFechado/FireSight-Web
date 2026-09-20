<?php

namespace App\Http\Controllers;

use App\Models\DutySchedule;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class DutyScheduleController extends Controller
{
    /**
     * Only field personnel can be assigned duty shifts — admins monitor
     * the schedule but aren't the ones responding to incidents, so they
     * never appear as a row or a selectable option in the form.
     *
     * @var array<int, string>
     */
    private const ASSIGNABLE_ROLES = [User::ROLE_BFP_PERSONNEL];

    /**
     * The only two shift windows the UI offers. Kept here (not just in
     * the frontend) so a request that bypasses the UI — a stale client,
     * a direct API call — can't sneak in an arbitrary time range.
     *
     * @var array<int, string>
     */
    private const VALID_SHIFT_TIMES = ['06:00', '18:00'];

    public function index(Request $request): Response
    {
        $dateParam = $request->string('date')->trim()->value();
        $personnelId = $request->integer('personnel_id') ?: null;
        $search = $request->string('search')->trim()->value();

        $date = $dateParam !== '' ? Carbon::parse($dateParam) : now();
        $weekStart = $date->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $date->copy()->endOfWeek(Carbon::SUNDAY);

        $shifts = DutySchedule::query()
            ->with('user')
            ->whereBetween('duty_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->when($personnelId !== null, fn ($q) => $q->where('user_id', $personnelId))
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('user', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('duty_date')
            ->orderBy('time_start')
            ->get()
            ->map(fn (DutySchedule $schedule) => $this->toRow($schedule));

        // Personnel-only: this list feeds both the roster grid rows and
        // the "Personnel" dropdown in the add/edit modal, so filtering
        // here is enough to keep admins out of both places at once.
        $personnel = User::query()
            ->whereIn('role', self::ASSIGNABLE_ROLES)
            ->where('status', User::STATUS_ACTIVE)
            ->with('personnelDetails')
            ->orderBy('first_name')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'rank' => $user->personnelDetails?->rank,
            ]);

        $onDutyNow = DutySchedule::onDutyAt(now())
            ->with('user')
            ->get()
            ->map(fn (DutySchedule $schedule) => [
                'id' => $schedule->user_id,
                'name' => $schedule->user?->name,
                'shift_end_time' => $schedule->time_end,
            ]);

        return Inertia::render('duty-schedule/index', [
            'shifts' => $shifts,
            'personnel' => $personnel,
            'onDutyNow' => $onDutyNow,
            'weekStart' => $weekStart->toDateString(),
            'weekEnd' => $weekEnd->toDateString(),
            'filters' => [
                'date' => $date->toDateString(),
                'personnel_id' => $personnelId,
                'search' => $search,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        try {
            DB::transaction(function () use ($data, $request) {
                DutySchedule::create([
                    'user_id' => $data['user_id'],
                    'duty_date' => $data['duty_date'],
                    'time_start' => $data['time_start'],
                    'time_end' => $data['time_end'],
                    'created_by' => $request->user()->id,
                ]);
            });
        } catch (QueryException $e) {
            if ($this->isUniqueConstraintViolation($e)) {
                throw ValidationException::withMessages([
                    'time_start' => 'This person already has a shift starting at that time.',
                ]);
            }

            throw $e;
        }

        $name = User::find($data['user_id'])?->name ?? 'Personnel';

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Shift for {$name} on {$data['duty_date']} was added.",
        ]);
    }

    public function update(Request $request, DutySchedule $schedule): RedirectResponse
    {
        $data = $this->validated($request);

        try {
            DB::transaction(function () use ($schedule, $data) {
                $schedule->update([
                    'user_id' => $data['user_id'],
                    'duty_date' => $data['duty_date'],
                    'time_start' => $data['time_start'],
                    'time_end' => $data['time_end'],
                ]);
            });
        } catch (QueryException $e) {
            if ($this->isUniqueConstraintViolation($e)) {
                throw ValidationException::withMessages([
                    'time_start' => 'This person already has a shift starting at that time.',
                ]);
            }

            throw $e;
        }

        $name = User::find($data['user_id'])?->name ?? 'Personnel';

        return back()->with('toast', [
            'type' => 'success',
            'message' => "{$name}'s shift on {$data['duty_date']} was updated.",
        ]);
    }

    public function destroy(DutySchedule $schedule): RedirectResponse
    {
        $name = $schedule->user?->name ?? 'Personnel';
        $date = $schedule->duty_date->toDateString();

        $schedule->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => "{$name}'s shift on {$date} was removed.",
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'user_id' => [
                'required',
                'integer',
                // Only field personnel can be assigned — this is the
                // server-side half of the "no admin accounts" rule;
                // the frontend dropdown already only lists personnel,
                // but this stops a direct POST from assigning an admin.
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereIn('role', self::ASSIGNABLE_ROLES);
                }),
            ],
            'duty_date' => ['required', 'date', 'after_or_equal:today'],
            'time_start' => ['required', Rule::in(self::VALID_SHIFT_TIMES)],
            'time_end' => [
                'required',
                Rule::in(self::VALID_SHIFT_TIMES),
                // Only the two real shift pairs are valid — this is NOT
                // a "time_end must be after time_start" check, because
                // that's false for the Night shift (18:00 -> 06:00
                // crosses midnight). Instead we check the pair matches
                // one of the two allowed windows exactly.
                function (string $attribute, mixed $value, \Closure $fail) use ($request) {
                    $start = $request->input('time_start');
                    $validPairs = [
                        ['06:00', '18:00'], // Morning
                        ['18:00', '06:00'], // Night (overnight)
                    ];

                    $isValidPair = collect($validPairs)
                        ->contains(fn ($pair) => $pair[0] === $start && $pair[1] === $value);

                    if (! $isValidPair) {
                        $fail('Choose a valid shift: Morning (6:00 AM – 6:00 PM) or Night (6:00 PM – 6:00 AM).');
                    }
                },
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function toRow(DutySchedule $schedule): array
    {
        return [
            'schedule_id' => $schedule->schedule_id,
            'user_id' => $schedule->user_id,
            'name' => $schedule->user?->name,
            'duty_date' => $schedule->duty_date->toDateString(),
            'time_start' => $schedule->time_start,
            'time_end' => $schedule->time_end,
        ];
    }

    /**
     * Detect a unique constraint violation across MySQL (1062) and SQLite (19).
     */
    private function isUniqueConstraintViolation(QueryException $e): bool
    {
        $code = (int) $e->errorInfo[1];

        return in_array($code, [1062, 19], true);
    }
}