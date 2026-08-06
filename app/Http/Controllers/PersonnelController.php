<?php

namespace App\Http\Controllers;

use App\Models\BfpPersonnelDetails;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PersonnelController extends Controller
{
    /** @var array<int, string> */
    private const ROLES = [User::ROLE_BFP_ADMIN, User::ROLE_BFP_PERSONNEL];

    /** @var array<int, string> */
    private const STATUSES = [User::STATUS_ACTIVE, User::STATUS_INACTIVE];

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();
        $role = $request->string('role')->lower()->value();
        $status = $request->string('status')->lower()->value();

        $personnel = User::query()
            ->whereIn('role', self::ROLES)
            ->with('personnelDetails')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('personnelDetails', fn ($q) => $q->where('employee_number', 'like', "%{$search}%"));
                });
            })
            ->when(in_array($role, self::ROLES, true), fn ($q) => $q->where('role', $role))
            ->when(in_array($status, self::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->orderBy('first_name')
            ->get()
            ->map(fn (User $user) => $this->toRow($user));

        return Inertia::render('personnel/index', [
            'personnel' => $personnel,
            'filters' => [
                'search' => $search,
                'role' => $role ?: 'all',
                'status' => $status ?: 'all',
            ],
            'totalCount' => User::whereIn('role', self::ROLES)->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'rank' => ['required', 'string', 'max:100'],
            'contact_number' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(self::ROLES)],
        ]);

        [$firstName, $lastName] = $this->splitName($data['full_name']);
        $tempPassword = Str::password(10, symbols: false);

        $user = DB::transaction(function () use ($data, $firstName, $lastName, $tempPassword) {
            $user = User::create([
                'role' => $data['role'],
                'status' => User::STATUS_ACTIVE,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $data['email'],
                'contact_number' => $data['contact_number'],
                'password' => Hash::make($tempPassword),
                // Admin-provisioned accounts skip the email verification
                // link — the temp password below is how they first get in.
                'email_verified_at' => now(),
            ]);

            BfpPersonnelDetails::create([
                'user_id' => $user->id,
                'rank' => $data['rank'],
                'station_assigned' => 'BFP Lian Fire Station',
                'employee_number' => $this->nextEmployeeNumber(),
            ]);

            return $user;
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => "{$user->name} was added. Temporary password: {$tempPassword} — share it with them.",
            'duration' => 15000,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->assertPersonnel($user);

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'rank' => ['required', 'string', 'max:100'],
            'contact_number' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(self::ROLES)],
            'status' => ['required', Rule::in(self::STATUSES)],
        ]);

        if ($user->id === $request->user()->id && $data['status'] === User::STATUS_INACTIVE) {
            throw ValidationException::withMessages([
                'status' => "You can't deactivate your own account.",
            ]);
        }

        [$firstName, $lastName] = $this->splitName($data['full_name']);

        DB::transaction(function () use ($user, $data, $firstName, $lastName) {
            $user->update([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $data['email'],
                'contact_number' => $data['contact_number'],
                'role' => $data['role'],
                'status' => $data['status'],
            ]);

            $user->personnelDetails()->update(['rank' => $data['rank']]);
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => "{$user->name}'s account was updated.",
        ]);
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->assertPersonnel($user);

        if ($user->id === $request->user()->id) {
            throw ValidationException::withMessages([
                'id' => "You can't delete your own account.",
            ]);
        }

        $name = $user->name;
        $user->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => "{$name} was removed from Personnel Accounts.",
        ]);
    }

    private function assertPersonnel(User $user): void
    {
        abort_unless(in_array($user->role, self::ROLES, true), 404);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(string $full): array
    {
        $parts = preg_split('/\s+/', trim($full), 2) ?: [];

        return [$parts[0] ?? '', $parts[1] ?? ''];
    }

    /**
     * Next `EMP-0XX` number, based on the highest existing numeric suffix.
     * Assumes the `EMP-###` format used everywhere else in the app.
     */
    private function nextEmployeeNumber(): string
    {
        $max = BfpPersonnelDetails::query()
            ->selectRaw('MAX(CAST(SUBSTRING(employee_number, 5) AS UNSIGNED)) as max_num')
            ->value('max_num');

        return sprintf('EMP-%03d', ((int) $max) + 1);
    }

    /**
     * @return array<string, mixed>
     */
    private function toRow(User $user): array
    {
        return [
            'id' => $user->id,
            'employee_number' => $user->personnelDetails?->employee_number,
            'name' => $user->name,
            'rank' => $user->personnelDetails?->rank,
            'role' => $user->role,
            'status' => $user->status,
            'contact_number' => $user->contact_number,
            'email' => $user->email,
        ];
    }
}
