<?php

namespace App\Http\Controllers;

use App\Enums\DutyGroup;
use App\Models\DutyOverride;
use App\Models\User;
use App\Support\DutyRotation;
use Carbon\CarbonInterface;
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
     * Only field personnel can be assigned to platoons and duty shifts.
     *
     * @var array<int, string>
     */
    private const ASSIGNABLE_ROLES = [User::ROLE_BFP_PERSONNEL];

    /**
     * Ranks sorted by seniority (Senior Fire Officer 4 down to Fire Officer 1).
     * Unknown ranks sort last.
     *
     * @var array<string, int>
     */
    private const RANK_SENIORITY = [
        'Senior Fire Officer 4' => 1,
        'Senior Fire Officer 3' => 2,
        'Senior Fire Officer 2' => 3,
        'Senior Fire Officer 1' => 4,
        'Fire Officer 3' => 5,
        'Fire Officer 2' => 6,
        'Fire Officer 1' => 7,
    ];

    public function index(Request $request): Response
    {
        $dateParam = $request->string('date')->trim()->value();
        $date = $dateParam !== '' ? Carbon::parse($dateParam) : now();

        $weekStart = DutyRotation::weekStartFor($date);
        $weekEnd = $weekStart->endOfWeek(CarbonInterface::SUNDAY)->startOfDay();
        $activeGroup = DutyRotation::activeGroupFor($date);
        $isCurrent = DutyRotation::weekStartFor(now())->toDateString() === $weekStart->toDateString();

        $overrides = DutyOverride::query()
            ->with(['user.personnelDetails', 'creator'])
            ->coveringWeek($weekStart)
            ->get();

        $overrideRows = $overrides->map(fn (DutyOverride $override) => [
            'override_id' => $override->override_id,
            'user_id' => $override->user_id,
            'name' => $override->user?->name,
            'duty_group' => $override->duty_group->value,
            'from_group' => $override->user?->personnelDetails?->duty_group?->value,
            'starts_on' => $override->starts_on->toDateString(),
            'ends_on' => $override->ends_on->toDateString(),
            'reason' => $override->reason,
            'created_by_name' => $override->creator?->name,
        ])->values()->all();

        $users = User::query()
            ->whereIn('role', self::ASSIGNABLE_ROLES)
            ->where('status', User::STATUS_ACTIVE)
            ->with('personnelDetails')
            ->get();

        $overridesByUser = $overrides->keyBy('user_id');

        $roster = [
            'regular' => [],
            'A' => [],
            'B' => [],
            'detached' => [],
            'unassigned' => [],
        ];

        foreach ($users as $user) {
            $override = $overridesByUser->get($user->id);
            $baseGroup = $user->personnelDetails?->duty_group;
            $effectiveGroup = $override?->duty_group ?? $baseGroup;
            $isOnDuty = ($effectiveGroup === DutyGroup::Regular)
                || ($effectiveGroup !== null && $effectiveGroup !== DutyGroup::Detached && $effectiveGroup === $activeGroup);

            $member = [
                'id' => $user->id,
                'name' => $user->name,
                'rank' => $user->personnelDetails?->rank,
                'base_group' => $baseGroup?->value,
                'effective_group' => $effectiveGroup?->value,
                'is_overridden' => $override !== null,
                'override_id' => $override?->override_id,
                'is_on_duty' => $isOnDuty,
                '_rank' => $user->personnelDetails?->rank,
                '_last_name' => $user->last_name,
            ];

            $bucketKey = match ($effectiveGroup) {
                DutyGroup::Regular => 'regular',
                DutyGroup::A => 'A',
                DutyGroup::B => 'B',
                DutyGroup::Detached => 'detached',
                null => 'unassigned',
            };

            $roster[$bucketKey][] = $member;
        }

        foreach ($roster as $key => $bucket) {
            usort($bucket, function (array $a, array $b) {
                $rankA = self::RANK_SENIORITY[$a['_rank'] ?? ''] ?? PHP_INT_MAX;
                $rankB = self::RANK_SENIORITY[$b['_rank'] ?? ''] ?? PHP_INT_MAX;

                if ($rankA !== $rankB) {
                    return $rankA <=> $rankB;
                }

                return strcasecmp((string) ($a['_last_name'] ?? ''), (string) ($b['_last_name'] ?? ''));
            });

            $roster[$key] = array_values(array_map(function (array $m) {
                unset($m['_rank'], $m['_last_name']);

                return $m;
            }, $bucket));
        }

        $groups = [
            ['value' => DutyGroup::A->value, 'label' => DutyGroup::A->label()],
            ['value' => DutyGroup::B->value, 'label' => DutyGroup::B->label()],
            ['value' => DutyGroup::Regular->value, 'label' => DutyGroup::Regular->label()],
            ['value' => DutyGroup::Detached->value, 'label' => DutyGroup::Detached->label()],
        ];

        $personnel = $users->sortBy(fn (User $u) => $u->first_name)->map(fn (User $user) => [
            'id' => $user->id,
            'name' => $user->name,
            'rank' => $user->personnelDetails?->rank,
        ])->values()->all();

        return Inertia::render('duty-schedule/index', [
            'week' => [
                'start' => $weekStart->toDateString(),
                'end' => $weekEnd->toDateString(),
                'activeGroup' => $activeGroup->value,
                'isCurrent' => $isCurrent,
            ],
            'roster' => $roster,
            'overrides' => $overrideRows,
            'upcoming' => DutyRotation::upcomingWeeks($weekStart, 8),
            'groups' => $groups,
            'personnel' => $personnel,
            'filters' => [
                'date' => $date->toDateString(),
            ],
        ]);
    }

    public function updateGroup(Request $request, User $user): RedirectResponse
    {
        abort_unless(in_array($user->role, self::ASSIGNABLE_ROLES, true), 404);

        $data = $request->validate([
            'duty_group' => ['nullable', Rule::enum(DutyGroup::class)],
        ]);

        $user->personnelDetails()->updateOrCreate(
            ['user_id' => $user->id],
            ['duty_group' => $data['duty_group']],
        );

        $groupEnum = DutyGroup::tryFrom((string) $data['duty_group']);
        $label = $groupEnum ? $groupEnum->label() : 'Unassigned';

        return back()->with('toast', [
            'type' => 'success',
            'message' => "{$user->name} moved to {$label}.",
        ]);
    }

    public function storeOverride(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q->whereIn('role', self::ASSIGNABLE_ROLES)),
            ],
            'duty_group' => ['required', Rule::enum(DutyGroup::class)],
            'starts_on' => [
                'required',
                'date',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (Carbon::parse($value)->dayOfWeek !== CarbonInterface::MONDAY) {
                        $fail('The starts on date must be a Monday.');
                    }
                },
            ],
            'ends_on' => [
                'required',
                'date',
                'after_or_equal:starts_on',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (Carbon::parse($value)->dayOfWeek !== CarbonInterface::SUNDAY) {
                        $fail('The ends on date must be a Sunday.');
                    }
                },
            ],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::with('personnelDetails')->findOrFail($data['user_id']);
        $baseGroup = $user->personnelDetails?->duty_group;

        if ($baseGroup !== null && $baseGroup->value === $data['duty_group']) {
            throw ValidationException::withMessages([
                'duty_group' => "The override group cannot match the user's base duty group.",
            ]);
        }

        $hasOverlap = DutyOverride::query()
            ->where('user_id', $user->id)
            ->where(function ($q) use ($data) {
                $q->where('starts_on', '<=', $data['ends_on'])
                    ->where('ends_on', '>=', $data['starts_on']);
            })
            ->exists();

        if ($hasOverlap) {
            throw ValidationException::withMessages([
                'starts_on' => 'This personnel member already has an override overlapping that date range.',
            ]);
        }

        DB::transaction(function () use ($data, $request) {
            DutyOverride::create([
                'user_id' => $data['user_id'],
                'duty_group' => $data['duty_group'],
                'starts_on' => $data['starts_on'],
                'ends_on' => $data['ends_on'],
                'reason' => $data['reason'] ?? null,
                'created_by' => $request->user()->id,
            ]);
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Override for {$user->name} was added.",
        ]);
    }

    public function swap(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => [
                'required',
                'integer',
                'different:swap_with_id',
                Rule::exists('users', 'id')->where(fn ($q) => $q->whereIn('role', self::ASSIGNABLE_ROLES)),
            ],
            'swap_with_id' => [
                'required',
                'integer',
                'different:user_id',
                Rule::exists('users', 'id')->where(fn ($q) => $q->whereIn('role', self::ASSIGNABLE_ROLES)),
            ],
            'starts_on' => [
                'required',
                'date',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (Carbon::parse($value)->dayOfWeek !== CarbonInterface::MONDAY) {
                        $fail('The starts on date must be a Monday.');
                    }
                },
            ],
            'ends_on' => [
                'required',
                'date',
                'after_or_equal:starts_on',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (Carbon::parse($value)->dayOfWeek !== CarbonInterface::SUNDAY) {
                        $fail('The ends on date must be a Sunday.');
                    }
                },
            ],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $userA = User::with('personnelDetails')->findOrFail($data['user_id']);
        $userB = User::with('personnelDetails')->findOrFail($data['swap_with_id']);

        $groupA = $userA->personnelDetails?->duty_group;
        $groupB = $userB->personnelDetails?->duty_group;

        $validGroups = [DutyGroup::A, DutyGroup::B];

        if (! in_array($groupA, $validGroups, true) || ! in_array($groupB, $validGroups, true)) {
            throw ValidationException::withMessages([
                'swap_with_id' => 'Both personnel must have a base group of either Duty A or Duty B to swap.',
            ]);
        }

        if ($groupA === $groupB) {
            throw ValidationException::withMessages([
                'swap_with_id' => 'Personnel in the same duty group cannot swap with each other.',
            ]);
        }

        $hasOverlapA = DutyOverride::query()
            ->where('user_id', $userA->id)
            ->where(function ($q) use ($data) {
                $q->where('starts_on', '<=', $data['ends_on'])
                    ->where('ends_on', '>=', $data['starts_on']);
            })
            ->exists();

        if ($hasOverlapA) {
            throw ValidationException::withMessages([
                'starts_on' => "{$userA->name} already has an override overlapping that date range.",
            ]);
        }

        $hasOverlapB = DutyOverride::query()
            ->where('user_id', $userB->id)
            ->where(function ($q) use ($data) {
                $q->where('starts_on', '<=', $data['ends_on'])
                    ->where('ends_on', '>=', $data['starts_on']);
            })
            ->exists();

        if ($hasOverlapB) {
            throw ValidationException::withMessages([
                'starts_on' => "{$userB->name} already has an override overlapping that date range.",
            ]);
        }

        DB::transaction(function () use ($userA, $userB, $groupA, $groupB, $data, $request) {
            DutyOverride::create([
                'user_id' => $userA->id,
                'duty_group' => $groupB->value,
                'starts_on' => $data['starts_on'],
                'ends_on' => $data['ends_on'],
                'reason' => $data['reason'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            DutyOverride::create([
                'user_id' => $userB->id,
                'duty_group' => $groupA->value,
                'starts_on' => $data['starts_on'],
                'ends_on' => $data['ends_on'],
                'reason' => $data['reason'] ?? null,
                'created_by' => $request->user()->id,
            ]);
        });

        $range = Carbon::parse($data['starts_on'])->format('M j').' – '.Carbon::parse($data['ends_on'])->format('M j');

        return back()->with('toast', [
            'type' => 'success',
            'message' => "{$userA->name} and {$userB->name} swapped for {$range}.",
        ]);
    }

    public function destroyOverride(DutyOverride $override): RedirectResponse
    {
        $name = $override->user?->name ?? 'Personnel';
        $override->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Override for {$name} was removed.",
        ]);
    }
}
