<?php

namespace App\Http\Controllers;

use App\Models\AuthToken;
use App\Models\Barangay;
use App\Models\CommunityReport;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ResidentController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();
        $barangayId = $request->integer('barangay') ?: null;
        $status = $request->string('status')->lower()->value();
        $status = in_array($status, ['active', 'suspended'], true) ? $status : 'all';

        $residents = User::query()
            ->where('role', User::ROLE_RESIDENT)
            ->with('homeAddress.barangay')
            ->withCount('communityReports')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->when($barangayId !== null, fn ($query) => $query->whereHas(
                'homeAddress',
                fn ($query) => $query->where('barangay_id', $barangayId),
            ))
            ->when($status === 'active', fn ($query) => $query->where('status', User::STATUS_ACTIVE))
            ->when($status === 'suspended', fn ($query) => $query->where('status', User::STATUS_INACTIVE))
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (User $user) => $this->toRow($user));

        return Inertia::render('residents/index', [
            'residents' => $residents,
            'filters' => [
                'search' => $search,
                'barangay' => $barangayId ? (string) $barangayId : 'all',
                'status' => $status,
            ],
            'barangays' => Barangay::query()
                ->orderBy('barangay_name')
                ->get(['barangay_id', 'barangay_name']),
            'totalCount' => User::where('role', User::ROLE_RESIDENT)->count(),
        ]);
    }

    public function show(User $user): JsonResponse
    {
        $this->assertResident($user);
        $user->load('homeAddress.barangay');

        $reports = $user->communityReports()
            ->with(['barangay', 'incidentRecord.barangay'])
            ->latest('created_at')
            ->get()
            ->map(fn (CommunityReport $report) => [
                'id' => $report->report_id,
                'reference' => $this->reportReference($report),
                'incident_type' => $report->incidentRecord?->incident_type
                    ? AnalyticsController::TYPE_LABELS[$report->incidentRecord->incident_type]
                        ?? Str::headline($report->incidentRecord->incident_type)
                    : 'Not yet assessed',
                'barangay' => $report->barangay?->barangay_name
                    ?? $report->incidentRecord?->barangay?->barangay_name,
                'status' => $report->status,
                'created_at' => $report->created_at?->toISOString(),
                'url' => route('incidents.show', $report),
            ]);

        $address = $user->homeAddress;

        return response()->json([
            'resident' => [
                'id' => $user->getKey(),
                'first_name' => $user->first_name,
                'middle_name' => $user->middle_name,
                'last_name' => $user->last_name,
                'suffix' => $user->suffix,
                'email' => $user->email,
                'username' => $user->username,
                'contact_number' => $user->contact_number,
                'house_no_street' => $address?->house_no_street,
                'barangay_id' => $address?->barangay_id,
                'barangay' => $address?->barangay?->barangay_name,
                'municipality' => $address?->municipality,
                'province' => $address?->province,
                'is_verified' => $user->is_verified,
                'joined_at' => $user->created_at?->toISOString(),
                'password_changed_at' => $user->password_changed_at?->toISOString(),
                'details_updated_at' => $user->details_updated_at?->toISOString(),
                'suspended_at' => $user->suspended_at?->toISOString(),
                'suspension_reason' => $user->suspension_reason,
                'is_google_linked' => $user->google_id !== null,
                'status' => $user->isActive() ? 'active' : 'suspended',
            ],
            'reports' => $reports,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->assertResident($user);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'suffix' => ['nullable', 'string', 'max:10'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'contact_number' => ['required', 'string', 'max:20'],
            'username' => ['nullable', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user)],
            'house_no_street' => ['nullable', 'string', 'max:150'],
            'barangay_id' => ['required', 'integer', 'exists:barangay,barangay_id'],
        ]);

        DB::transaction(function () use ($user, $data): void {
            $user->update([
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'],
                'suffix' => $data['suffix'] ?? null,
                'email' => $data['email'],
                'contact_number' => $data['contact_number'],
                'username' => $data['username'] ?? null,
                'details_updated_at' => now(),
            ]);

            $user->homeAddress()->updateOrCreate([], [
                'house_no_street' => $data['house_no_street'] ?? null,
                'barangay_id' => $data['barangay_id'],
                'municipality' => 'Lian',
                'province' => 'Batangas',
            ]);
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => "{$user->name}'s resident details were updated.",
        ]);
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $this->assertResident($user);
        $temporaryPassword = Str::password(10, symbols: false);

        DB::transaction(function () use ($user, $temporaryPassword): void {
            $user->forceFill([
                'password' => $temporaryPassword,
                'password_changed_at' => now(),
            ])->save();

            AuthToken::where('user_id', $user->getKey())->delete();
        });

        return back()
            ->with('residentTemporaryPassword', $temporaryPassword)
            ->with('toast', [
                'type' => 'success',
                'message' => "{$user->name}'s password was reset. Temporary password: {$temporaryPassword} — share it securely.",
                'duration' => 15000,
            ]);
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        $this->assertResident($user);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($user, $data): void {
            $user->update([
                'status' => User::STATUS_INACTIVE,
                'suspended_at' => now(),
                'suspension_reason' => $data['reason'] ?? null,
            ]);

            AuthToken::where('user_id', $user->getKey())->delete();
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => "{$user->name}'s resident account was suspended.",
        ]);
    }

    public function reactivate(User $user): RedirectResponse
    {
        $this->assertResident($user);

        $user->update([
            'status' => User::STATUS_ACTIVE,
            'suspended_at' => null,
            'suspension_reason' => null,
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => "{$user->name}'s resident account was reactivated.",
        ]);
    }

    private function assertResident(User $user): void
    {
        abort_unless($user->isResident(), 404);
    }

    private function reportReference(CommunityReport $report): string
    {
        return sprintf('INC-%s-%04d', $report->created_at?->format('Y') ?? now()->format('Y'), $report->report_id);
    }

    /**
     * @return array<string, mixed>
     */
    private function toRow(User $user): array
    {
        return [
            'id' => $user->getKey(),
            'name' => $user->name,
            'email' => $user->email,
            'contact_number' => $user->contact_number,
            'barangay' => $user->homeAddress?->barangay?->barangay_name,
            'status' => $user->isActive() ? 'active' : 'suspended',
            'report_count' => $user->community_reports_count,
            'created_at' => $user->created_at?->toISOString(),
            'is_google_linked' => $user->google_id !== null,
            'password_changed_at' => $user->password_changed_at?->toISOString(),
            'details_updated_at' => $user->details_updated_at?->toISOString(),
            'suspended_at' => $user->suspended_at?->toISOString(),
        ];
    }
}
